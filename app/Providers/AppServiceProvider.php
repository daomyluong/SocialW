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
            if (Auth::check()) {
                $currentUserId = Auth::id();

                // 1. Lấy danh sách ID những người mình ĐÃ theo dõi
                $followingIds = DB::table('followers')
                    ->where('follower_user_id', $currentUserId)
                    ->pluck('following_user_id')
                    ->toArray();

                // 2. Chỉ gợi ý những người CHƯA theo dõi và KHÔNG PHẢI là mình
                $suggestedUsers = User::where('id', '!=', $currentUserId)
                    ->whereNotIn('id', $followingIds) // Loại bỏ người đã follow
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();

                $view->with('suggestedUsers', $suggestedUsers);
                $view->with('followingIds', $followingIds); // Vẫn gửi cái này để nếu cần check ở đâu đó
            }
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
    }

    public const HOME = '/';
}