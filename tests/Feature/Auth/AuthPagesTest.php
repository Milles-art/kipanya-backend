<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_password_auth_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
        $this->get('/reset-password?token=example-token')->assertOk();
    }

    public function test_the_login_page_links_to_the_reset_flow(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('password.forgot'), false);
    }
}
