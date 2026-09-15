<?php

namespace Tests\Feature\Account;

use App\Enums\Auth\UserStatus;
use App\Models\Auth\NotificationPreference;
use App\Models\Auth\UserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountPreferencesFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
    }

    public function test_preferences_require_authentication(): void
    {
        $this->getJson('/api/v1/account/preferences')->assertUnauthorized();
    }

    public function test_authenticated_user_can_read_and_update_preferences(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/account/preferences')->assertOk();
        $this->actingAs($user, 'sanctum')->putJson('/api/v1/account/preferences/notifications', [
            'push_enabled' => false, 'email_enabled' => true, 'sms_enabled' => false, 'marketing_enabled' => false, 'restock_enabled' => true, 'price_drop_enabled' => true,
        ])->assertOk()->assertJsonPath('data.marketing_enabled', false);

        $this->actingAs($user, 'sanctum')->putJson('/api/v1/account/preferences/size-profile', [
            'top' => 'L', 'bottom' => '34', 'shoe' => '42',
        ])->assertOk()->assertJsonPath('data.top', 'L');

        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id, 'push_enabled' => false, 'marketing_enabled' => false, 'restock_enabled' => true, 'price_drop_enabled' => true]);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
        $this->assertSame(['top' => 'L', 'bottom' => '34', 'shoe' => '42'], UserProfile::where('user_id', $user->id)->first()->size_profile);
    }
}
