<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post4;
use App\Models\Comment4;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Post;
class InteractionController4 extends Controller
{
    // --- CHỨC NĂNG LIKE ---
    public function like(Post4 $post)
    {
        $userId = Auth::id();
        
        // Kiểm tra xem đã like chưa (Tra cứu trong bảng post_likes từ file social.sql)
        $like = DB::table('post_likes')
                  ->where('post_id', $post->id)
                  ->where('user_id', $userId);

        if ($like->exists()) {
            $like->delete();
            $post->decrement('like_count'); // Cập nhật thống kê như file social.sql yêu cầu
        } else {
            DB::table('post_likes')->insert([
                'post_id' => $post->id,
                'user_id' => $userId,
                'created_at' => now()
            ]);
            $post->increment('like_count');
        }

        return back();
    }

    // --- CHỨC NĂNG BÌNH LUẬN ---
    public function comment(Request $request, Post4 $post)
    {
        $request->validate(['content' => 'required']);

        Comment4::create([
            'post_id' => $post->id,
            'author_user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        $post->increment('comment_count'); // Cập nhật thống kê bài viết
        return back();
    }

    public function destroyComment(Comment4 $comment)
    {
        // Kiểm tra quyền: Chỉ chủ comment mới được xóa
        if (Auth::id() === $comment->author_user_id) {
            $comment->delete();
            Post4::find($comment->post_id)->decrement('comment_count');
        }

        return back();
    }

    // --- CHỨC NĂNG FOLLOW ---
    public function toggleFollow(User $user)
    {
        /** @var User|null $me */
        $me = Auth::user();

        // Quy tắc: Không được tự follow chính mình
        if (! $me || $me->id === $user->id) {
            return back()->with('error', 'Bạn không thể tự theo dõi chính mình.');
        }

        $isFollowing = DB::table('followers')
                         ->where('follower_id', $me->id)
                         ->where('following_id', $user->id);

        if ($isFollowing->exists()) {
            $isFollowing->delete();
            $me->decrement('following_count');
            $user->decrement('follower_count');
        } else {
            DB::table('followers')->insert([
                'follower_id' => $me->id,
                'following_id' => $user->id,
                'created_at' => now()
            ]);
            $me->increment('following_count');
            $user->increment('follower_count');
        }

        return back();
    }
    public function likePost(Post $post)
{
    $userId = 1; 
    $likeQuery = DB::table('post_likes')->where('post_id', $post->id)->where('user_id', $userId);

    if ($likeQuery->exists()) {
        $likeQuery->delete();
        $liked = false;
        $post->decrement('like_count'); 
    } else {
        DB::table('post_likes')->insert([
            'post_id' => $post->id,
            'user_id' => $userId,
            'created_at' => now()
        ]);
        $liked = true;
        $post->increment('like_count');

        // --- ĐÂY LÀ PHẦN MỚI THÊM VÀO ---
        if ($post->author_user_id != $userId) {
            \App\Models\Notification::create([
                'user_id'   => $post->author_user_id,
                'actor_user_id' => $userId,
                'post_id'   => $post->id,
                'type'      => 'like',
                'message'   => 'đã thích bài viết của bạn',
            ]);
        }
    }

    // return json
    return response()->json([
        'success' => true,
        'liked' => $liked,
        'likeCount' => DB::table('post_likes')->where('post_id', $post->id)->count()
    ]);
}
}
