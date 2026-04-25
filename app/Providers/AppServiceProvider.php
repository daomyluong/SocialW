<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);
        View::composer('partials.suggestions', function ($view) {
            if (!Auth::check()) {
                return;
            }

            $currentUserId = (int) Auth::id();

            $suggestedUsers = User::where('id', '!=', $currentUserId)
                ->inRandomOrder()
                ->limit(5)
                ->get();

            $followingIds = DB::table('followers')
                ->where('follower_user_id', $currentUserId)
                ->pluck('following_user_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $view->with('suggestedUsers', $suggestedUsers);
            $view->with('followingIds', $followingIds);
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
    }

    public const HOME = '/';
}