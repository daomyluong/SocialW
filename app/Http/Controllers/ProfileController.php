<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang cá nhân (Show)
     */
    public function show($id = null)
    {
        if ($id) {
            $user = User::findOrFail($id);
        } else {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect()->route('login');
        }

        $isOwnProfile = Auth::check() && Auth::id() === $user->id;

        $postCount = Schema::hasTable('posts')
            ? DB::table('posts')
                ->where('author_user_id', $user->id)
                ->where('is_deleted', false)
                ->count()
            : 0;

        $followingCount = $user->following_count ?? $user->following()->count();
        $followerCount = $user->follower_count ?? $user->followers()->count();

        return view('profile.show', compact('user', 'isOwnProfile', 'postCount', 'followingCount', 'followerCount'));
    }

    /**
     * Hiển thị form chỉnh sửa (Edit)
     */
    public function edit()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Xử lý cập nhật thông tin (Update)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Kiểm tra dữ liệu (Validation) bao gồm cả avatar và bio
        $request->validate([
            'display_name' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 1. Xử lý upload ảnh đại diện (nếu có chọn file mới)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $fileName);
            if (Schema::hasColumn('users', 'avatar_url')) {
                $user->avatar_url = 'uploads/avatars/' . $fileName;
            }
        }

        // 2. Cập nhật các thông tin văn bản từ form
        $user->display_name = $request->display_name;
        $user->bio = $request->bio; // Thêm dòng này để lưu tiểu sử vào DB

        // 3. Lưu vào Database
        $user->save();

        // Chuyển hướng về trang profile kèm thông báo thành công
        return redirect()->route('profile.show', $user->id)->with('success', 'Cập nhật thành công!');
    }

    /**
     * Theo dõi hoặc bỏ theo dõi người dùng (Follow)
     */
    public function follow(Request $request, $id)
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        if ($currentUser->id === $targetUser->id) {
            return back()->with('error', 'Không thể theo dõi chính mình!');
        }

        // Logic follow - tạm thời chỉ tăng follower_count (cần tạo bảng followers sau)
        if (!$currentUser->following()->where('following_user_id', $targetUser->id)->exists()) {
            // Follow
            $targetUser->increment('follower_count');
            $currentUser->increment('following_count');
        } else {
            // Unfollow
            $targetUser->decrement('follower_count');
            $currentUser->decrement('following_count');
        }

        return back()->with('success', 'Cập nhật theo dõi thành công!');
    }
}
