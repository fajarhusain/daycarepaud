<?php

namespace App\Providers;

use App\View\Composers\TourComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale((string) config('app.locale', 'id'));
        Carbon::setLocale((string) config('app.date_locale', 'id'));

        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute((int) config('api.rate_limits.login', 10))
                ->by($request->ip());
        });

        RateLimiter::for('api-google', function (Request $request) {
            return Limit::perMinute((int) config('api.rate_limits.google', 10))
                ->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) config('api.rate_limits.api', 120))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-upload', function (Request $request) {
            return Limit::perMinute((int) config('api.rate_limits.upload', 10))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-download', function (Request $request) {
            return Limit::perMinute((int) config('api.rate_limits.download', 30))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('guest-forms', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        Gate::define('viewApiDocs', fn () => (bool) config('scramble.docs_enabled', false));

        View::composer('layouts.app', TourComposer::class);

        View::composer('layouts.topbar', function ($view) {
            $user = auth()->user();
            $path = null;
            if ($user) {
                $user->loadMissing([
                    'pengajar:id,user_id,photo,name',
                    'anaks:id,user_id,photo,name',
                ]);
                if (filled(optional($user->pengajar)->photo)) {
                    $path = $user->pengajar->photo;
                } elseif ($user->hasRole('Orang Tua') && $user->anaks->isNotEmpty()) {
                    $withPhoto = $user->anaks->first(fn ($a) => filled($a->photo));
                    $path = $withPhoto?->photo;
                }
            }
            $view->with('topbarProfilePhotoPath', $path);
        });
    }
}
