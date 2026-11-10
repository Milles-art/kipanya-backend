<?php

namespace App\Services\Auth;

use App\Actions\Authorization\AssignRoleToUser;
use App\Enums\Auth\UserStatus;
use App\Models\Auth\SocialAccount;
use App\Models\User;
use App\Support\DeviceIdentity;
use App\Support\SignupRateLimiter;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as ProviderUser;

final class SocialAccountService
{
    public function resolve(string $provider, ProviderUser $identity, Request $request, ?User $linkTo = null): User
    {
        $subject = (string) $identity->getId();
        if ($subject === '' || strlen($subject) > 512) {
            throw new DomainException('invalid_identity');
        }
        $hash = hash_hmac('sha256', $provider.'|'.$subject, (string) config('app.key'));
        $raw = $identity->getRaw();
        // Google explicitly asserts email verification. A Facebook profile is
        // sufficient for identity, but is not proof of a recovery email address.
        $verifiedEmail = $provider === 'google' && ($raw['email_verified'] ?? $raw['verified_email'] ?? false) === true;
        $email = $verifiedEmail ? filter_var($identity->getEmail(), FILTER_VALIDATE_EMAIL) : null;
        $email = $email ? strtolower($email) : null;
        $known = SocialAccount::query()->where('provider', $provider)->where('subject_hash', $hash)->first();
        if ($known) {
            $user = $known->user;
            if ($linkTo && $linkTo->id !== $user->id) {
                throw new DomainException('identity_in_use');
            }
            $this->assertCustomer($user);
            return $user;
        }
        if ($linkTo) {
            $this->assertCustomer($linkTo);
        } else {
            // Never grant access to a pre-existing account merely because the
            // provider supplies the same email. Linking requires its own session.
            if ($email && User::query()->whereEmail($email)->exists()) {
                throw new DomainException('account_exists');
            }
            app(SignupRateLimiter::class)->hit($request);
        }
        return DB::transaction(function () use ($provider, $hash, $identity, $email, $request, $linkTo): User {
            $user = $linkTo;
            if ($user) {
                $user = User::query()->lockForUpdate()->findOrFail($user->id);
                $this->assertCustomer($user);
                if (SocialAccount::query()->where('user_id', $user->id)->where('provider', $provider)->exists()) {
                    throw new DomainException('already_linked');
                }
            } else {
                $name = trim((string) $identity->getName());
                $user = User::query()->create(['name' => mb_substr($name ?: 'KP Wear customer', 0, 120), 'email' => $email, 'phone' => null]);
                $user->forceFill([
                    'status' => UserStatus::Active->value,
                    'password' => null,
                    'email_verified_at' => $email ? now() : null,
                    'phone_verified_at' => null,
                    'device_hash' => DeviceIdentity::hash(DeviceIdentity::id($request)),
                    'signup_ip' => $request->ip(),
                ])->save();
                app(AssignRoleToUser::class)->execute($user, 'user');
                $user->profile()->create();
                $user->notificationPreferences()->create();
            }
            SocialAccount::query()->create(['user_id' => $user->id, 'provider' => $provider, 'subject_hash' => $hash]);
            return $user->fresh();
        });
    }

    private function assertCustomer(User $user): void
    {
        if (! $user->isActive() || $user->isAdmin()) {
            throw new DomainException('account_unavailable');
        }
    }
}
