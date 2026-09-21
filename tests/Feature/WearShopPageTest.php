<?php

namespace Tests\Feature;

use Tests\TestCase;

final class WearShopPageTest extends TestCase
{
    public function test_shop_page_contains_customer_filter_controls_without_unsupported_delivery_claim(): void
    {
        $response = $this->get('/shop');

        $response->assertOk()
            ->assertSee('Shop All Products')
            ->assertSee('data-catalog-search', false)
            ->assertSee('data-catalog-sort', false)
            ->assertSee('data-catalog-filter-toggle', false)
            ->assertSee('data-catalog-mobile-apply', false)
            ->assertDontSee('Free delivery on orders over 100,000 TZS', false);
    }
}
