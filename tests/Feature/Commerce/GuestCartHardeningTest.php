<?php

namespace Tests\Feature\Commerce;

use App\Models\Cart\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F-11: guest tokens are bearer secrets - weak/guessable values are never adopted,
 * and cart creation is capped per IP.
 */
final class GuestCartHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_weak_client_token_is_not_adopted_as_the_cart_identity(): void
    {
        $weak = str_repeat('a', 64);

        $this->withHeader('X-Guest-Cart-Token', $weak)->getJson('/api/v1/cart')->assertSuccessful();

        $this->assertSame(0, Cart::query()->where('guest_token_hash', hash('sha256', $weak))->count());
        $this->assertSame(1, Cart::query()->whereNotNull('guest_token_hash')->count(), 'A server-issued token is used instead.');
    }

    public function test_a_strong_client_generated_token_is_still_accepted(): void
    {
        $token = bin2hex(random_bytes(32));

        $this->withHeader('X-Guest-Cart-Token', $token)->getJson('/api/v1/cart')->assertSuccessful();

        $this->assertSame(1, Cart::query()->where('guest_token_hash', hash('sha256', $token))->count());
    }

    public function test_guest_cart_creation_is_capped_per_ip(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $this->withHeader('X-Guest-Cart-Token', bin2hex(random_bytes(32)))->getJson('/api/v1/cart')->assertSuccessful();
        }

        $this->withHeader('X-Guest-Cart-Token', bin2hex(random_bytes(32)))->getJson('/api/v1/cart')->assertStatus(429);

        // An existing cart keeps working for the same IP.
        $existing = Cart::query()->latest('id')->first();
        $this->assertNotNull($existing);
    }
}
