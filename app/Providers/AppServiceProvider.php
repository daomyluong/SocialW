<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrap();
        \Illuminate\Support\Facades\View::composer('partials.suggestions', function ($view) {
            if (Auth::check()) {
                $suggestedUsers = \App\Models\User::where('id', '!=', Auth::id())
                    ->inRandomOrder()
                    ->limit(5)
                    ->get();

                $view->with('suggestedUsers', $suggestedUsers);
            }
        });
    }

    public const HOME = '/';
}
