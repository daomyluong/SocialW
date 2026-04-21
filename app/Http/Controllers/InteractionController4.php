<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Comment4;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class InteractionController4 extends Controller
{
    // --- CHỨC NĂNG LIKE ---
    public function like(Post $post)
    {
        $userId = Auth::id();
        if (! $userId) {
            return redirect()->route('login');
        }
        
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $post->increment('like_count');

            if ((int) $post->author_user_id !== (int) $userId) {
                Notification::create([
                    'user_id' => $post->author_user_id,
                    'sender_id' => $userId,
                    'post_id' => $post->id,
                    'type' => 'like',
                    'message' => 'đã thích bài viết của bạn',
                    'is_read' => 0,
                ]);
            }
        }

        return redirect()->to(url()->previous() . '#post-' . $post->id);    
    }

    // --- CHỨC NĂNG BÌNH LUẬN ---
    public function comment(Request $request, Post $post)
    {
        $userId = Auth::id();
        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate(['content' => 'required']);

        Comment4::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'content' => $request->input('content'),
        ]);

        $post->increment('comment_count'); // Cập nhật thống kê bài viết

        if ((int) $post->author_user_id !== (int) $userId) {
            Notification::create([
                'user_id' => $post->author_user_id,
                'sender_id' => $userId,
                'post_id' => $post->id,
                'type' => 'comment',
                'message' => 'đã bình luận về bài viết của bạn',
                'is_read' => 0,
            ]);
        }

        return redirect()->to(url()->previous() . '#post-' . $post->id);
    }

    // File: App/Http/Controllers/InteractionController4.php

    public function show(Request $request, Post $post)
    {
        if ($request->ajax()) {
            // Lấy bình luận từ thứ 6 trở đi, CẦN THÊM take() ĐỂ TRÁNH LỖI MYSQL
            $comments = Comment4::where('post_id', $post->id)
                                ->with('user')
                                ->orderBy('created_at', 'desc')
                                ->skip(5)
                                ->take(100) // Đã thêm giới hạn để MySQL không báo lỗi
                                ->get();

            // Kiểm tra xem file comment_list nằm ở đâu để gọi cho đúng:
            // Nếu nằm trong resources/views/components/ => Để nguyên 'components.comment_list'
            // Nếu nằm ngoài resources/views/ => Đổi thành 'comment_list'
            return response()->json([
                'html' => view('components.comment_list', compact('comments'))->render() 
            ]);
        }

        return response()->json(['error' => 'Yêu cầu không hợp lệ'], 400);
    }
    public function destroyComment(Comment4 $comment)
    {
        // Kiểm tra quyền: Chủ comment HOẶC Admin (ID = 1) mới được xóa
        if (Auth::id() == $comment->user_id || Auth::id() == 1) {
            $comment->delete();
            Post::find($comment->post_id)?->decrement('comment_count');
        }

        return back();
    }
    
    // --- CHỨC NĂNG CHIA SẺ ---
    public function share(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'nullable|string|max:500' // Lời nhắn khi share
        ]);

        $userId = Auth::id();
        if (! $userId) {
            return redirect()->route('login');
        }

        // Lưu vào bảng post_shares theo đúng cấu trúc SQL của bạn
        \Illuminate\Support\Facades\DB::table('post_shares')->insert([
            'user_id'    => $userId,
            'post_id'    => $post->id,
            'comment'    => $request->comment, // Lời nhắn (ví dụ: "Gửi cho @tuannguyen")
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tăng số lượng share_count ở bảng posts
        $post->increment('share_count');

        if ((int) $post->author_user_id !== (int) $userId) {
            Notification::create([
                'user_id' => $post->author_user_id,
                'sender_id' => $userId,
                'post_id' => $post->id,
                'type' => 'share',
                'message' => 'đã chia sẻ bài viết của bạn',
                'is_read' => 0,
            ]);
        }

        return redirect()->to(url()->previous() . '#post-' . $post->id) -> with('success', 'Bạn đã chia sẻ bài viết lên tường thành công!');
    }

    // --- CHỨC NĂNG FOLLOW ---
    public function toggleFollow(User $user)
    {
        $request = request();
        $authId = Auth::id();
        $me = $authId ? User::find($authId) : null;

        if (!$me || $me->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Không thể tự theo dõi'], 400);
            }

            return back()->with('error', 'Không thể tự theo dõi.');
        }

        $isFollowing = DB::table('followers')
                        ->where('follower_user_id', $me->id)
                        ->where('following_user_id', $user->id);

        if ($isFollowing->exists()) {
            $isFollowing->delete();
            $me->decrement('following_count');
            $user->decrement('follower_count');
            $status = 'unfollowed';
        } else {
            DB::table('followers')->insert([
                'follower_user_id' => $me->id,
                'following_user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $me->increment('following_count');
            $user->increment('follower_count');
            $status = 'followed';
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => $status]);
        }

        return back()->with('success', $status === 'followed' ? 'Đã theo dõi người dùng.' : 'Đã hủy theo dõi người dùng.');
    }
    public function likePost(Post $post)
{
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

    $likeQuery = DB::table('post_likes')->where('post_id', $post->id)->where('user_id', $userId);

    if ($likeQuery->exists()) {
        $likeQuery->delete();
        $liked = false;
        $post->decrement('like_count'); 
    } else {
        DB::table('post_likes')->insert([
            'post_id' => $post->id,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $liked = true;
        $post->increment('like_count');

        // --- ĐÂY LÀ PHẦN MỚI THÊM VÀO ---
        if ($post->author_user_id != $userId) {
            \App\Models\Notification::create([
                'user_id'   => $post->author_user_id,
                'sender_id' => $userId,
                'post_id'   => $post->id,
                'type'      => 'like',
                'message'   => 'đã thích bài viết của bạn',
                'is_read'   => 0,
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
