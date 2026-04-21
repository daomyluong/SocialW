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


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/welcome', function () {
    return view('welcome');
});

// TV 1 (ĐÀO) - HẠ TẦNG, TRANG CHỦ & TÌM KIẾM
Route::redirect('/home', '/');

// TV 2 (LOAN) - NGƯỜI DÙNG (AUTH & PROFILE)
Route::get('/profile', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route('profile.show', Auth::id());
})->name('profile');
<<<<<<< HEAD
require __DIR__.'/auth.php';

=======

Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/{id}/follow', [ProfileController::class, 'follow'])->name('profile.follow');
});

Route::get('/profile/{id}', [ProfileController::class, 'show'])
    ->whereNumber('id')
    ->name('profile.show');
// TV 3 (THANH) - BÀI VIẾT (POSTS)
Route::get('/post/{id}', [AdminController5::class, 'showPost'])->name('post.show');
    // Đường dẫn để hiện form đăng bài
    Route::get('/posts/create', [PostController3::class, 'create'])->name('posts3.create');
    
    // Đường dẫn để xử lý lưu dữ liệu
    Route::post('/posts/store', [PostController3::class, 'store'])->name('posts3.store');
    // Đường dẫn xem bài viết của riêng tôi
Route::get('/my-posts', [PostController3::class, 'myPosts'])->name('posts3.myPosts');


// Route để XEM chi tiết bài viết (Phương thức GET)
Route::get('/posts/{id}', [PostController3::class, 'show'])->name('posts3.show');

    // Route xóa bài viết
Route::delete('/posts/{id}', [PostController3::class, 'destroy'])->name('posts3.destroy');
    // Route Sửa - 1: Mở trang sửa (Cần file edit3.blade.php sau này)
Route::get('/posts/{id}/edit', [PostController3::class, 'edit'])->name('posts3.edit');
    // Route Sửa - 2: Lưu dữ liệu (Không cần file giao diện)
Route::put('/posts/{id}', [PostController3::class, 'update'])->name('posts3.update');

Route::get('/', [PostController3::class, 'index'])->name('home');
   // Route xem danh sách thông báo
Route::get('/notifications', [PostController3::class, 'notifications'])->name('notifications.index');
    // Route để xử lý đăng Story mới
Route::post('/stories3', [App\Http\Controllers\StoryController3::class, 'store'])->name('stories3.store');
// Route xử lý việc nhấn nút lưu 
Route::post('/bookmarks/toggle/{postId}', [BookmarkController3::class, 'toggleBookmark'])->name('bookmarks.toggle');

// Route để vào trang xem danh sách đã lưu
Route::get('/bookmarks', [BookmarkController3::class, 'index'])->name('bookmarks.index');
Route::get('/bookmarks/folders', [BookmarkController3::class, 'getFolders']);
// TV 4 (QUỲNH) - TƯƠNG TÁC (SOCIAL)

// TV 5 (LINH) - QUẢN TRỊ (ADMIN)
require __DIR__.'/auth.php';

    // Like
    Route::post('/posts/{post}/like', [InteractionController4::class, 'like'])->name('posts.like');

    // Comment
    Route::post('/posts/{post}/comments', [InteractionController4::class, 'comment'])->name('comments.store');
    Route::delete('/comments/{comment}', [InteractionController4::class, 'destroyComment'])->name('comments.destroy');

    // Share
    Route::post('/posts/{post}/share', [InteractionController4::class, 'share'])->name('posts.share');
    // Follow
    Route::post('/users/{user}/follow', [InteractionController4::class, 'toggleFollow'])->name('users.follow');
    Route::get('/suggestions', [HomeController4::class, 'allSuggestions'])->name('users.suggestions');
    
    // xem thêm bình luận
    Route::get('/posts/{post}/load-more-comments', [InteractionController4::class, 'show'])->name('comments.show');


// TV 5 (LINH) - QUẢN TRỊ (ADMIN)

// Route tạm thời để test giao diện
Route::get('/admin_test', [AdminController5::class, 'testLayout']);
// Gom nhóm các trang admin lại cho chuyên nghiệp
Route::prefix('admin')->group(function () {
    
    // 1. TRANG DASHBOARD THẬT 
    Route::get('/dashboard', [AdminController5::class, 'dashboard'])->name('admin.dashboard');

    // 2. QUẢN LÝ NGƯỜI DÙNG 
    Route::get('/users', [AdminController5::class, 'manageUsers'])->name('admin.users.index');
    Route::post('/users', [AdminController5::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{id}/update-role', [AdminController5::class, 'updateUserRole'])->name('admin.users.update_role');
    // Route xử lý Khóa/Mở khóa tài khoản (Dùng POST để bảo mật)
    Route::post('/users/{id}/toggle-status', [AdminController5::class, 'toggleUserStatus'])->name('admin.users.toggle_status');
    // Route xử lý Xóa mềm tài khoản
    Route::post('/users/{id}/delete', [AdminController5::class, 'deleteUser'])->name('admin.users.delete');
    
    // 3. QUẢN LÝ BÀI VIẾT 
    Route::get('/posts', [AdminController5::class, 'managePosts'])->name('admin.posts.index');
    Route::post('/posts/{id}/moderate', [AdminController5::class, 'moderatePost'])->name('admin.posts.moderate');
    Route::post('/posts/{id}/delete', [AdminController5::class, 'deletePost'])->name('admin.posts.delete');

    // 4. QUẢN LÝ BÌNH LUẬN (COMMENTS)
    Route::get('/comments', [AdminController5::class, 'manageComments'])->name('admin.comments.index');
    Route::post('/comments/{id}/delete', [AdminController5::class, 'deleteComment'])->name('admin.comments.delete');
    Route::post('/comments/quick-ban/{userId}', [AdminController5::class, 'quickBanUser'])->name('admin.comments.quick_ban');

    // 5. QUẢN LÝ BÁO CÁO (REPORTS)
    Route::get('/reports', [AdminController5::class, 'manageReports'])->name('admin.reports.index');
    // Route xử lý thao tác (Phán quyết) của Admin
    Route::post('/reports/process', [AdminController5::class, 'processReport'])->name('admin.reports.process');
});

>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
