<?php

namespace Tests\Unit\Integrations\Sms;

use App\Integrations\Sms\LogSmsGateway;
use App\Integrations\Sms\NotifyAfricaSmsGateway;
use App\Integrations\Sms\SmsGateway;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class NotifyAfricaSmsGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.notify_africa', [
            'base_url' => 'https://sms.example.test',
            'api_key' => 'ntfy_secret_test_key',
            'sender_id' => 'KIPANYA',
            'timeout' => 10,
        ]);

        Http::preventStrayRequests();
    }

    public function test_send_posts_the_single_message_endpoint_with_bearer_auth_and_json_body(): void
    {
        Http::fake([
            'https://sms.example.test/api/v1/api/messages/send' => Http::response([
                'status' => 202,
                'message' => 'Message accepted',
                'data' => ['messageId' => 'd9b54c80-8a63-4f8b-83c8-71f2439f5429', 'status' => 'SENT'],
            ], 202),
        ]);

        app(SmsGateway::class)->send('+255712345678', 'Your Kipanya verification code is 123456.');

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://sms.example.test/api/v1/api/messages/send'
                && $request->hasHeader('Authorization', 'Bearer ntfy_secret_test_key')
                && $request->hasHeader('Content-Type', 'application/json')
                && $request['phone_number'] === '+255712345678'
                && $request['message'] === 'Your Kipanya verification code is 123456.'
                && $request['sender_id'] === 'KIPANYA';
        });
    }

    public function test_non_success_response_throws_without_leaking_the_key_or_message(): void
    {
        Http::fake([
            'https://sms.example.test/api/v1/api/messages/send' => Http::response([
                'error' => 'Unauthorised',
            ], 401),
        ]);

        try {
            app(SmsGateway::class)->send('+255712345678', 'Your Kipanya verification code is 987654.');
            $this->fail('Expected a RuntimeException for the failed SMS request.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('HTTP 401', $e->getMessage());
            $this->assertStringNotContainsString('987654', $e->getMessage());
            $this->assertStringNotContainsString('ntfy_secret_test_key', $e->getMessage());
        }
    }

    public function test_server_error_response_throws_without_leaking_the_message(): void
    {
        Http::fake([
            'https://sms.example.test/api/v1/api/messages/send' => Http::response('', 500),
        ]);

        try {
            app(SmsGateway::class)->send('+255712345678', 'Your Kipanya verification code is 555111.');
            $this->fail('Expected a RuntimeException for a server error response.');
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString('555111', $e->getMessage());
            $this->assertStringNotContainsString('ntfy_secret_test_key', $e->getMessage());
        }
    }

    public function test_notify_gateway_is_bound_when_an_api_key_is_configured(): void
    {
        $this->assertInstanceOf(NotifyAfricaSmsGateway::class, app(SmsGateway::class));
    }

    public function test_log_gateway_is_bound_when_no_api_key_is_configured(): void
    {
        config()->set('services.notify_africa.api_key', null);

        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));
    }

    public function test_empty_api_key_is_trim_before_selecting_the_real_gateway(): void
    {
        config()->set('services.notify_africa.api_key', '   ');

        $this->assertInstanceOf(LogSmsGateway::class, app(SmsGateway::class));
    }
}
