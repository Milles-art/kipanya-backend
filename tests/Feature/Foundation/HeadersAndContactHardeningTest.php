<?php

namespace Tests\Feature\Foundation;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F-15 (header hardening) and F-16 (contact form bot protection).
 */
final class HeadersAndContactHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Real Person', 'email' => 'person@example.com',
            'type' => 'support', 'message' => 'Hello, I need help with my order please.',
        ], $overrides);
    }

    public function test_responses_carry_cross_origin_opener_policy(): void
    {
        $this->get('/')->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }

    public function test_csp_img_src_follows_configuration(): void
    {
        config(['security.csp_img_src' => "'self' data: https://cdn.kipanya.example"]);

        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("img-src 'self' data: https://cdn.kipanya.example;", $csp.';');
        $this->assertStringNotContainsString('blob: https:', $csp);
    }

    public function test_default_csp_is_unchanged(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("img-src 'self' data: blob: https:", $csp);
    }

    public function test_a_normal_message_is_stored(): void
    {
        $this->postJson('/api/v1/contact', $this->payload())->assertCreated();

        $this->assertSame(1, ContactMessage::query()->count());
    }

    public function test_a_filled_honeypot_is_answered_like_success_but_not_stored(): void
    {
        $this->postJson('/api/v1/contact', $this->payload(['website' => 'http://spam.example']))
            ->assertCreated()
            ->assertJsonPath('data.id', 0);

        $this->assertSame(0, ContactMessage::query()->count());
    }

    public function test_one_sender_cannot_flood_the_inbox(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/contact', $this->payload())->assertCreated();
        }

        $this->postJson('/api/v1/contact', $this->payload())->assertStatus(429);
        $this->assertSame(3, ContactMessage::query()->count());

        // ...but a different sender is unaffected.
        $this->postJson('/api/v1/contact', $this->payload(['email' => 'other@example.com']))->assertCreated();
    }

    public function test_the_contact_page_renders_the_honeypot_field(): void
    {
        $this->get('/contact')->assertOk()->assertSee('name="website"', false);
    }
}
