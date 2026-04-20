<?php

namespace App\Http\Controllers;

// Lưu ý: Đảm bảo use đúng Model User và Post của nhóm bạn
use App\Models\User; 
use App\Models\Post4; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController4 extends Controller
{
    public function index()
    {
        // 1. GIẢ LẬP ĐĂNG NHẬP (Ép hệ thống nhận diện bạn là User ID 1)
        $userId = Auth::id() ?? 1;


        $currentUserId = Auth::id() ?? 1; // Giả lập user ID 1 nếu chưa đăng nhập

        // 2. LẤY DỮ LIỆU BÀI VIẾT (Để hiển thị ở phần "Dành cho bạn")
        // Lấy bài viết kèm media và sắp xếp mới nhất
        $posts = Post4::with('media')->orderBy('created_at', 'desc')->get();

        // 3. LẤY DỮ LIỆU GỢI Ý (Logic của bạn đã viết)
        $followingIds = DB::table('followers')
            ->where('follower_id', $currentUserId)
            ->pluck('following_id')
            ->toArray();

        $suggestedUsers = User::where('id', '!=', $currentUserId)
            ->whereNotIn('id', $followingIds)
            ->inRandomOrder() 
            ->limit(5)
            ->get();

        // 4. TRẢ DỮ LIỆU RA VIEW HOME
        // compact giúp truyền cả 2 biến $posts và $suggestedUsers ra ngoài giao diện
        return view('home', compact('posts', 'suggestedUsers'));
    }
    public function allSuggestions()
    {
        $currentUserId = Auth::id() ?? 1; // Giả lập bạn là User 1

        $followingIds = DB::table('followers')
            ->where('follower_id', $currentUserId)
            ->pluck('following_id')
            ->toArray();

        // Lấy tất cả trừ bản thân và những người đã follow
        $allSuggestions = User::where('id', '!=', $currentUserId)
            ->whereNotIn('id', $followingIds)
            ->get();

        return view('suggestions', compact('allSuggestions'));
    }
}