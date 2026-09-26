<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesRenderTest extends TestCase
{
    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('WEAR THE', false);
    }

    public function test_collections_page_renders(): void
    {
        $this->get('/collections')->assertOk()->assertSee('More than clothes.', false);
    }

    public function test_about_page_renders(): void
    {
        $this->get('/about')->assertOk()->assertSee('From cartoons to clothing.', false);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back', false);
    }

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your account', false);
    }

    public function test_product_page_renders_for_any_slug(): void
    {
        $this->get('/product/does-not-exist')->assertOk()->assertSee('data-product-page', false);
    }

    public function test_collection_slug_renders_catalog(): void
    {
        $this->get('/collections/some-collection')->assertOk()->assertSee('Shop All', false);
    }

    public function test_category_slug_renders_catalog(): void
    {
        $this->get('/category/hoodies')->assertOk()->assertSee('Shop All', false);
    }

    public function test_search_renders_catalog(): void
    {
        $this->get('/search?q=hoodie')->assertOk()->assertSee('Shop All', false);
    }

    public function test_order_status_page_renders_for_any_number(): void
    {
        $this->get('/orders/KP-9999')->assertOk()->assertSee('data-order-status', false);
    }

    public function test_order_status_page_shows_the_progress_steps(): void
    {
        $html = $this->get('/orders/KP-9999')->assertOk()->getContent();

        $this->assertStringContainsString('data-track-step="1"', $html);
        $this->assertStringContainsString('data-track-step="3"', $html);
    }
}
