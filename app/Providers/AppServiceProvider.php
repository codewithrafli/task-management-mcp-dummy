<?php

namespace App\Providers;

use App\Models\User;
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
        // Rate limiter for the MCP HTTP endpoint: 60 requests/minute per
        // authenticated user (falls back to IP for unauthenticated calls).
        RateLimiter::for('mcp', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Authorization: only admins may run destructive MCP tools (e.g. deleting tasks).
        Gate::define('delete-tasks', fn (User $user) => $user->is_admin);
    }
}
