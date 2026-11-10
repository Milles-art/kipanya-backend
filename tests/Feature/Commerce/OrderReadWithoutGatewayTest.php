<?php

namespace Tests\Feature\Commerce;

use App\Integrations\Payments\PaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderReadWithoutGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_history_does_not_require_payment_gateway_configuration(): void
    {
        app()->bind(PaymentGateway::class, fn () => throw new \LogicException('Reading orders must not resolve the payment gateway.'));
        $this->getJson('/api/v1/orders')->assertUnauthorized();
        $this->actingAs(User::factory()->create(), 'sanctum')->getJson('/api/v1/orders')
            ->assertOk()->assertJsonPath('data', []);
    }
}
