<?php

namespace App\Http\Controllers;

// Lưu ý: Đảm bảo use đúng Model User và Post của nhóm bạn
use App\Models\User; 
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController4 extends Controller
{
    public function index()
    {
        $currentUserId = Auth::id();
        if (! $currentUserId) {
            return redirect()->route('login');
        }

        // 2. LẤY DỮ LIỆU BÀI VIẾT (Để hiển thị ở phần "Dành cho bạn")
        // Lấy bài viết kèm media và sắp xếp mới nhất
        $posts = Post::with('media')->orderBy('created_at', 'desc')->get();

        // 3. LẤY DỮ LIỆU GỢI Ý (Logic của bạn đã viết)
        $followingIds = DB::table('followers')
            ->where('follower_user_id', $currentUserId)
            ->pluck('following_user_id')
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
        $currentUserId = Auth::id();
        if (! $currentUserId) {
            return redirect()->route('login');
        }

        $followingIds = DB::table('followers')
            ->where('follower_user_id', $currentUserId)
            ->pluck('following_user_id')
            ->toArray();

        // Lấy tất cả trừ bản thân và những người đã follow
        $allSuggestions = User::where('id', '!=', $currentUserId)
            ->whereNotIn('id', $followingIds)
            ->get();

        return view('suggestions', compact('allSuggestions'));
    }
}