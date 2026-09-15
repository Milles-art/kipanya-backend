<?php

namespace App\Providers;

use App\Models\Content\Cartoon;
use App\Models\Content\Category;
use App\Models\Content\Collection;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Policies\CartoonPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CollectionPolicy;
use App\Policies\WearOrderPolicy;
use App\Policies\WearProductPolicy;
use App\Integrations\Payments\FakePaymentGateway;
use App\Integrations\Payments\PaymentGateway;
use App\Integrations\Sms\LogSmsGateway;
use App\Integrations\Sms\SmsGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
        $this->app->bind(SmsGateway::class, LogSmsGateway::class);
    }

    public function boot(): void
    {
        Gate::policy(Cartoon::class, CartoonPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Collection::class, CollectionPolicy::class);
        Gate::policy(WearProduct::class, WearProductPolicy::class);
        Gate::policy(WearOrder::class, WearOrderPolicy::class);

        RateLimiter::for('api', fn (Request $request) => [
            Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()),
        ]);
    }
}
