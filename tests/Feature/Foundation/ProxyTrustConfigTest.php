<?php

namespace Tests\Feature\Foundation;

use App\Support\ProxyTrust;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * F-08: proxy trust must come from config (survives config:cache), and env() must
 * never be read outside config files.
 */
final class ProxyTrustConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        TrustProxies::flushState();
        EncryptCookies::flushState();

        parent::tearDown();
    }

    private function clientIp(string $remoteAddr, ?string $forwardedFor = null): string
    {
        Route::get('/_test/ip', fn () => response(request()->ip()));

        $server = ['REMOTE_ADDR' => $remoteAddr];
        if ($forwardedFor !== null) {
            $server['HTTP_X_FORWARDED_FOR'] = $forwardedFor;
        }

        return $this->call('GET', '/_test/ip', [], [], [], $server)->getContent();
    }

    public function test_forwarded_for_is_honoured_only_from_a_trusted_proxy(): void
    {
        config(['app.trusted_proxies' => '10.0.0.1, 10.0.0.2']);
        ProxyTrust::apply();

        $this->assertSame('203.0.113.9', $this->clientIp('10.0.0.1', '203.0.113.9'));
        // An untrusted caller cannot spoof its address (would otherwise bypass IP rate limits).
        $this->assertSame('198.51.100.7', $this->clientIp('198.51.100.7', '203.0.113.9'));
    }

    public function test_without_configuration_forwarded_for_is_ignored(): void
    {
        config(['app.trusted_proxies' => '']);
        ProxyTrust::apply();

        $this->assertSame('10.0.0.1', $this->clientIp('10.0.0.1', '203.0.113.9'));
    }

    public function test_env_is_never_read_outside_config_files(): void
    {
        $offenders = [];

        foreach (['app', 'bootstrap', 'routes', 'database', 'resources/views'] as $dir) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(base_path($dir), \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                // ProxyTrust only mentions env() in a comment.
                $code = preg_replace('#/\*.*?\*/|//[^\n]*#s', '', (string) file_get_contents($file->getPathname()));

                if (preg_match('/\benv\s*\(/', (string) $code)) {
                    $offenders[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $offenders, 'env() returns null once config is cached; read config() instead.');
    }
}
