<?php

namespace Tests\Feature\Commerce;

use Tests\TestCase;

/**
 * Wear Commerce V2 coverage is intentionally deferred until the persistent
 * cart, checkout, payment transaction, reservation, and order APIs exist.
 *
 * The previous tests targeted the removed Blade/web route layer and therefore
 * cannot be used as backend regression tests for the API-only architecture.
 */
class WearCommerceFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped(
            'Deferred until Wear Commerce V2 API is implemented.',
        );
    }
}
