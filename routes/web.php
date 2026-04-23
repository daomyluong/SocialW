<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::middleware('web')->group(function (): void {
    require __DIR__.'/web/tv1_home_search.php';
    require __DIR__.'/web/tv2_auth_profile.php';
    require __DIR__.'/web/tv3_posts.php';
    require __DIR__.'/web/tv4_social.php';
    require __DIR__.'/web/tv6_messages.php';
    require __DIR__.'/web/tv5_admin.php';
});


