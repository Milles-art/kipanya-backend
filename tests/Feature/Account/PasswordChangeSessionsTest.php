<?php

namespace Tests\Feature\Account;

use App\Actions\Auth\IssueSanctumToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PasswordChangeSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_the_password_signs_out_every_other_session_but_not_this_one(): void
    {
        // An OTP-only account has no password yet, so no current password is asked for.
        $user = User::factory()->create(['status' => 'active', 'phone_verified_at' => now()]);
        $user->forceFill(['password' => null])->save();
        $user->createToken('old-phone', ['auth']);
        $user->createToken('old-laptop', ['auth']);
        $plain = (new IssueSanctumToken)->execute($user);
        $currentId = $user->tokens()->latest('id')->value('id');

        $this->withHeaders(['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'])
            ->putJson('/api/v1/password', [
                'new_password' => 'a-new-strong-pass',
                'new_password_confirmation' => 'a-new-strong-pass',
            ])->assertOk();

        $this->assertSame(1, $user->tokens()->count());
        $this->assertSame($currentId, $user->tokens()->first()->id);
    }

    public function test_a_wrong_current_password_changes_nothing_and_keeps_all_sessions(): void
    {
        $user = User::factory()->create(['status' => 'active', 'phone_verified_at' => now(), 'password' => 'the-real-password']);
        $user->createToken('other', ['auth']);
        $plain = (new IssueSanctumToken)->execute($user);

        $this->withHeaders(['Authorization' => 'Bearer '.$plain, 'Accept' => 'application/json'])
            ->putJson('/api/v1/password', [
                'current_password' => 'wrong',
                'new_password' => 'a-new-strong-pass',
                'new_password_confirmation' => 'a-new-strong-pass',
            ])->assertStatus(422);

        $this->assertSame(2, $user->tokens()->count());
    }
}
