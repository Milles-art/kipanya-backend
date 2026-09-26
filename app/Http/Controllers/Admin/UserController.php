<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Auth\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Administration\Role;
use App\Models\User;
use App\Rules\ValidTanzanianPhoneNumber;
use App\Support\AuditLogger;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeUsers($request);

        $search = trim($request->string('search')->toString());
        $status = $request->string('status')->toString();

        $users = User::query()
            ->whereHas('roles', static fn ($query) => $query->where('slug', '!=', 'user'))
            ->with(['roles' => fn ($query) => $query->orderBy('name')])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    // phone/email are ciphertext; only exact blind-index
                    // matches are possible. Name remains substring-searchable.
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email_hash', User::emailHash($search))
                        ->orWhere('phone_hash', User::phoneHash($search));
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        $this->authorizeUsers($request);

        return view('admin.users.create', [
            'roles' => $this->staffRoles($request),
            // The shared form partial expects $user (edit passes the model);
            // hand it an empty instance so create renders instead of 500ing.
            'user' => new User,
        ]);
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeUsers($request);

        $data = $this->validated($request);
        $phone = PhoneNumber::normalize($data['phone'])->value();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $phone,
        ]);

        $user->forceFill([
            'onboarding_completed_at' => now(),
            'role' => UserRole::Admin,
            'status' => $data['status'],
        ])->save();

        $role = $this->resolveRole($request, (int) $data['role_id']);
        $user->roles()->sync([$role->id]);

        $auditLogger->log($request, 'admin.users.created', $user, [
            'role' => $role->slug,
            'status' => $user->status,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Admin user created successfully.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeUsers($request);
        $this->ensureManageable($request, $user);

        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => $this->staffRoles($request),
        ]);
    }

    public function update(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeUsers($request);
        $this->ensureManageable($request, $user);

        $data = $this->validated($request, $user);
        $phone = PhoneNumber::normalize($data['phone'])->value();
        $role = $this->resolveRole($request, (int) $data['role_id']);

        if ($request->user()->is($user)) {
            if ($data['status'] !== 'active') {
                return back()->withErrors(['status' => 'You cannot deactivate your own administrator account.'])->withInput();
            }

            $currentRole = $user->roles()->where('slug', '!=', 'user')->first();
            if ($currentRole && $role->id !== $currentRole->id) {
                return back()->withErrors(['role_id' => 'You cannot change your own administrator role.'])->withInput();
            }
        }

        return DB::transaction(function () use ($request, $user, $auditLogger, $data, $phone, $role): RedirectResponse {
            $losesSuperAdmin = $user->hasRole('super_admin')
                && ($role->slug !== 'super_admin' || $data['status'] !== 'active');

            if ($losesSuperAdmin) {
                $this->assertAnotherActiveSuperAdminExists(
                    $user,
                    'The last active super administrator cannot be demoted or deactivated.',
                );
            }

            $old = [
                'role' => $user->roles()->where('slug', '!=', 'user')->value('slug'),
                'status' => $user->status,
            ];

            $credentialsChanged = $user->phone !== $phone
                || $user->email !== ($data['email'] ?: null);

            // The phone number is the admin's login credential: changing it lets the
            // editor sign in as that admin. Only a super administrator may do that.
            if ($credentialsChanged && ! $request->user()->hasRole('super_admin')) {
                throw ValidationException::withMessages([
                    'phone' => 'Only a super administrator can change an administrator\'s phone number or email.',
                ]);
            }

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'] ?: null,
                'phone' => $phone,
            ]);

            $user->forceFill(['status' => $data['status']])->save();

            // Revoke all API tokens when the account is deactivated or its
            // identifying credentials change, so a token issued under the old
            // identity cannot outlive the change.
            if ($data['status'] !== 'active' || $credentialsChanged) {
                $user->tokens()->delete();
            }

            // A changed credential invalidates the second factor: the account must
            // enrol a fresh authenticator on its next sign-in.
            if ($credentialsChanged && $user->twoFactorEnabled()) {
                $user->forceFill([
                    'two_factor_secret' => null,
                    'two_factor_enabled_at' => null,
                ])->save();
            }

            $user->roles()->sync([$role->id]);

            $auditLogger->log($request, 'admin.users.updated', $user, [
                'before' => $old,
                'after' => ['role' => $role->slug, 'status' => $user->status],
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Admin user updated successfully.');
        });
    }

    public function toggleStatus(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorizeUsers($request);
        $this->ensureManageable($request, $user);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot change your own administrator status.']);
        }

        $next = $user->status === 'active' ? 'inactive' : 'active';

        return DB::transaction(function () use ($request, $user, $auditLogger, $next): RedirectResponse {
            if ($next === 'inactive' && $user->hasRole('super_admin')) {
                $this->assertAnotherActiveSuperAdminExists(
                    $user,
                    'The last active super administrator cannot be deactivated.',
                );
            }

            $user->forceFill(['status' => $next])->save();

            if ($next === 'inactive') {
                $user->tokens()->delete();
            }

            $auditLogger->log($request, 'admin.users.status_changed', $user, ['status' => $next]);

            return back()->with('success', "Admin user {$next} successfully.");
        });
    }

    /**
     * Ensure at least one other active super administrator remains once the
     * given target loses that role/status. The active super administrator rows
     * are locked so two concurrent demotions or deactivations cannot both pass
     * the check and leave the platform without one.
     */
    private function assertAnotherActiveSuperAdminExists(User $target, string $message): void
    {
        $activeSuperAdminIds = User::query()
            ->where('status', 'active')
            ->whereHas('roles', static fn ($query) => $query->where('slug', 'super_admin'))
            ->lockForUpdate()
            ->pluck('id');

        if ($activeSuperAdminIds->count() <= 1 && $activeSuperAdminIds->contains($target->id)) {
            throw ValidationException::withMessages(['user' => $message]);
        }
    }

    private function authorizeUsers(Request $request): void
    {
        abort_unless($request->user()?->isAdmin() && $request->user()->hasPermission('users.manage'), 403);
    }

    private function ensureAdminUser(User $user): void
    {
        abort_unless($user->isAdmin(), 403);
    }

    /**
     * The target must be an administrator, and only a super administrator may
     * touch another super administrator (edit, demote, deactivate).
     */
    private function ensureManageable(Request $request, User $target): void
    {
        $this->ensureAdminUser($target);

        abort_if(
            $target->hasRole('super_admin') && ! $request->user()?->hasRole('super_admin'),
            403,
            'Only a super administrator can manage another super administrator.',
        );
    }

    /**
     * Roles an actor may assign. `super_admin` is only assignable by an
     * existing super administrator so a holder of `users.manage` can never
     * grant it to themselves or others.
     */
    private function resolveRole(Request $request, int $roleId): Role
    {
        return $this->assignableRoleQuery($request)->whereKey($roleId)->firstOrFail();
    }

    private function staffRoles(Request $request)
    {
        return $this->assignableRoleQuery($request)->orderBy('name')->get();
    }

    private function assignableRoleQuery(Request $request)
    {
        return Role::query()
            ->where('slug', '!=', 'user')
            ->when(
                ! $request->user()?->hasRole('super_admin'),
                static fn ($query) => $query->where('slug', '!=', 'super_admin'),
            );
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['required', 'string', 'max:30', new ValidTanzanianPhoneNumber, Rule::unique('users', 'phone')->ignore($user?->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where(function ($query) use ($request): void {
                $query->where('slug', '!=', 'user');

                if (! $request->user()?->hasRole('super_admin')) {
                    $query->where('slug', '!=', 'super_admin');
                }
            })],
        ]);
    }
}
