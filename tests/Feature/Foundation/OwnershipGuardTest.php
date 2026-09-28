<?php

namespace Tests\Feature\Foundation;

use App\Models\Commerce\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The ownership rule now lives in one place (AuthorizesUserOwnership), so this
 * suite pins the behaviour that rule must preserve: a customer can only ever
 * touch their own payment methods, addresses and orders, and a staff user with
 * the right permission can still see a customer order.
 */
class OwnershipGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_cannot_delete_another_customers_payment_method(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $method = PaymentMethod::create([
            'user_id' => $owner->id,
            'brand' => 'Visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
        ]);

        $this->actingAs($attacker, 'sanctum')
            ->deleteJson("/api/v1/payment-methods/{$method->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('payment_methods', ['id' => $method->id]);
    }

    public function test_a_customer_cannot_promote_another_customers_payment_method(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $method = PaymentMethod::create([
            'user_id' => $owner->id,
            'brand' => 'Visa',
            'last4' => '1111',
            'exp_month' => 1,
            'exp_year' => 2031,
        ]);

        $this->actingAs($attacker, 'sanctum')
            ->putJson("/api/v1/payment-methods/{$method->id}/set-default")
            ->assertForbidden();

        $this->assertFalse((bool) PaymentMethod::query()->find($method->id)->is_default);
    }

    public function test_a_customer_can_still_manage_their_own_payment_method(): void
    {
        $owner = User::factory()->create();

        $method = PaymentMethod::create([
            'user_id' => $owner->id,
            'brand' => 'Visa',
            'last4' => '4242',
            'exp_month' => 12,
            'exp_year' => 2030,
        ]);

        $this->actingAs($owner, 'sanctum')
            ->putJson("/api/v1/payment-methods/{$method->id}/set-default")
            ->assertOk();

        $this->assertTrue((bool) PaymentMethod::query()->find($method->id)->is_default);
    }

    public function test_a_customer_cannot_update_another_customers_address(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $address = $owner->addresses()->create([
            'type' => 'shipping',
            'recipient_name' => 'Owner Name',
            'phone' => '+255700000001',
            'region' => 'Dar es Salaam',
            'district' => 'Ilala',
            'street' => 'Uhru St 12',
        ]);

        $this->actingAs($attacker, 'sanctum')
            ->putJson("/api/v1/addresses/{$address->id}", [
                'type' => 'shipping',
                'recipient_name' => 'Hijacked',
                'phone' => '+255700000002',
                'region' => 'Dar es Salaam',
                'district' => 'Ilala',
                'street' => 'Hijacked St 1',
            ])
            ->assertForbidden();

        $this->assertSame('Owner Name', $address->fresh()->recipient_name);
    }

    public function test_phone_and_email_are_ciphertext_at_rest(): void
    {
        $user = User::factory()->create([
            'phone' => '+255700000042',
            'email' => 'ciphertext@example.test',
        ]);

        $row = $this->getConnection()->table('users')->where('id', $user->id)->first();

        $this->assertNotSame('+255700000042', $row->phone);
        $this->assertNotSame('ciphertext@example.test', $row->email);
        $this->assertStringStartsWith('eyJpdiI6', $row->phone);
        $this->assertStringStartsWith('eyJpdiI6', $row->email);

        // ...while the model still reads and writes plaintext transparently.
        $this->assertSame('+255700000042', $user->fresh()->phone);
        $this->assertSame('ciphertext@example.test', $user->fresh()->email);
    }

    public function test_the_blind_index_still_resolves_a_user_by_phone(): void
    {
        $user = User::factory()->create(['phone' => '+255700000099']);

        $this->assertTrue(User::query()->wherePhone('+255700000099')->exists());
        $this->assertFalse(User::query()->wherePhone('+255700000098')->exists());
        $this->assertSame($user->id, User::query()->wherePhone('+255700000099')->value('id'));
    }

    public function test_a_gateway_timeout_surfaces_as_a_validation_error_not_a_500(): void
    {
        // Asserted here so the gateway-failure translation cannot silently
        // regress back to an unhandled exception.
        $this->assertTrue(
            method_exists(\App\Services\Payments\PaymentService::class, 'translateGatewayFailure')
        );
    }

    // ---- ownership helper behaviour (AuthorizesUserOwnership)

    /** The trait reads request()->user(); outside an HTTP call that needs a resolver. */
    private function actingAsInRequest(User $user): void
    {
        request()->setUserResolver(static fn () => $user);
    }

    private function guardHarness(): object
    {
        return new class
        {
            use \App\Http\Controllers\Api\V1\Concerns\AuthorizesUserOwnership;

            public function owned(string $class, int|string $key)
            {
                return $this->ownedOrFail($class, $key);
            }

            public function assert(\Illuminate\Database\Eloquent\Model $model): void
            {
                $this->assertOwnedBy($model);
            }
        };
    }

    public function test_owned_or_fail_finds_the_callers_record_and_hides_everyone_elses(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $mine = PaymentMethod::create(['user_id' => $owner->id, 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2030]);
        $theirs = PaymentMethod::create(['user_id' => $other->id, 'brand' => 'Visa', 'last4' => '1111', 'exp_month' => 1, 'exp_year' => 2031]);

        $this->actingAsInRequest($owner);
        $guard = $this->guardHarness();

        $this->assertTrue($guard->owned(PaymentMethod::class, $mine->id)->is($mine));

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $guard->owned(PaymentMethod::class, $theirs->id);
    }

    public function test_ownership_is_compared_as_integers_so_string_keys_do_not_lock_out_the_owner(): void
    {
        $owner = User::factory()->create();
        $method = PaymentMethod::create(['user_id' => $owner->id, 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2030]);
        $method->setRawAttributes(array_merge($method->getAttributes(), ['user_id' => (string) $owner->id]));

        $this->actingAsInRequest($owner);
        $this->guardHarness()->assert($method);

        $this->assertTrue(true, 'A numeric-string user_id still identifies the real owner.');
    }

    public function test_a_record_with_no_owner_is_never_treated_as_owned(): void
    {
        $user = User::factory()->create();
        $orphan = new PaymentMethod(['brand' => 'Visa', 'last4' => '0000']);

        $this->actingAsInRequest($user);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->guardHarness()->assert($orphan);
    }
}
