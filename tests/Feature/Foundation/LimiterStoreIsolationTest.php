<?php

namespace Tests\Feature\Foundation;

use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LimiterStoreIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_limiter_enforces_limits_without_writing_to_application_database(): void
    {
        $directory = storage_path('framework/testing/limiter-'.Str::uuid());
        config(['cache.default' => 'database', 'cache.limiter' => 'file', 'cache.stores.file.path' => $directory]);
        app('cache')->forgetDriver('file');
        app()->forgetInstance(RateLimiter::class);
        $limiter = app(RateLimiter::class);
        try {
            $limiter->hit('concurrent-storefront', 60);
            $limiter->hit('concurrent-storefront', 60);
            $this->assertTrue($limiter->tooManyAttempts('concurrent-storefront', 2));
            $this->assertSame(2, $limiter->attempts('concurrent-storefront'));
            $this->assertSame(0, DB::table('cache')->count());
            $limiter->clear('concurrent-storefront');
            $this->assertFalse($limiter->tooManyAttempts('concurrent-storefront', 2));
        } finally {
            $this->assertSame(storage_path('framework/testing'), dirname($directory));
            (new \Illuminate\Filesystem\Filesystem)->deleteDirectory($directory);
        }
    }
}
