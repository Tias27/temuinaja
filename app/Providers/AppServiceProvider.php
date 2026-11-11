<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::define('manage-laporan', fn ($admin) => $admin instanceof Admin);
        foreach (['catalog' => 60, 'catalog-search' => 20, 'report-submit' => 15, 'report-detail' => 60, 'report-photo' => 180] as $name => $limit) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute($limit)->by($request->ip()));
        }
        RateLimiter::for('admin-login', function (Request $request) {
            $username = $request->input('nama_pengguna');
            $account = is_string($username) ? hash('sha256', mb_strtolower(trim($username))) : 'invalid';

            return [
                Limit::perMinute(5)->by('admin-ip:'.$request->ip()),
                Limit::perMinute(10)->by('admin-account:'.$account),
            ];
        });
        RateLimiter::for('admin-settings', fn (Request $request) => [
            Limit::perMinute(10)->by('settings-ip:'.$request->ip()),
            Limit::perMinute(5)->by('settings-admin:'.$request->user('admin')?->getKey()),
        ]);
    }
}
