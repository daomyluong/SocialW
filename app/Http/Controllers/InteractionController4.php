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
    public function like(Request $request, Post $post)
    {
        $userId = Auth::id();
        if (! $userId) {
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        $likeQuery = DB::table('post_likes')
            ->where('post_id', $post->id)
            ->where('user_id', $userId);

        if ($likeQuery->exists()) {
            $likeQuery->delete();
            $post->decrement('like_count');
            $liked = false;
        } else {
            DB::table('post_likes')->insert([
                'post_id' => $post->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $post->increment('like_count');
            $liked = true;

            if ((int) $post->user_id !== (int) $userId) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'sender_id' => $userId,
                    'post_id' => $post->id,
                    'type' => 'like',
                    'message' => 'đã thích bài viết của bạn',
                    'is_read' => 0,
                ]);
            }
        }

        $post->refresh();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likeCount' => (int) $post->like_count,
            ]);
        }

        return back()->with('success', $liked ? 'Đã thích bài viết.' : 'Đã bỏ thích bài viết.');
    }

    public function comment(Request $request, Post $post)
    {
        $userId = Auth::id();
        if (! $userId) {
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        $validated = $request->validate(['content' => 'required|string|max:2000']);

        $comment = Comment4::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'content' => $validated['content'],
        ]);

        $post->increment('comment_count');

        if ((int) $post->user_id !== (int) $userId) {
            Notification::create([
                'user_id' => $post->user_id,
                'sender_id' => $userId,
                'post_id' => $post->id,
                'comment_id' => $comment->id,
                'type' => 'comment',
                'message' => 'đã bình luận về bài viết của bạn',
                'is_read' => 0,
            ]);
        }

        $post->refresh();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'commentCount' => (int) $post->comment_count,
            ]);
        }

        return redirect()->route('posts3.show', $post->id)->with('success', 'Đã thêm bình luận.');
    }

    public function show(Request $request, Post $post)
    {
        if ($request->ajax()) {
            $comments = Comment4::where('post_id', $post->id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->skip(5)
                ->take(100)
                ->get();

            return response()->json([
                'html' => view('components.comment_list', compact('comments'))->render(),
            ]);
        }

        return response()->json(['error' => 'Yêu cầu không hợp lệ'], 400);
    }

    public function destroyComment(Comment4 $comment)
    {
        if (Auth::id() == $comment->user_id || Auth::id() == 1) {
            $comment->delete();
            Post::find($comment->post_id)?->decrement('comment_count');
        }

        return back();
    }

    public function share(Request $request, Post $post)
    {
        $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();
        if (! $userId) {
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => 'Unauthenticated'], 401)
                : redirect()->route('login');
        }

        $sharedComment = trim((string) $request->input('comment', ''));
        $post->loadMissing(['media', 'author']);
        $originalAuthorName = $post->author?->display_name ?? $post->author?->username ?? 'một người dùng';

        DB::transaction(function () use ($userId, $post, $sharedComment, $originalAuthorName): void {
            $sharedPost = new Post();
            $sharedPost->user_id = $userId;
            $sharedPost->content = trim(
                ($sharedComment !== '' ? $sharedComment . PHP_EOL . PHP_EOL : '') .
                'Đã chia sẻ bài viết của ' . $originalAuthorName . ':' . PHP_EOL . PHP_EOL .
                (string) $post->content
            );
            $sharedPost->visibility = $post->visibility ?? 'public';
            $sharedPost->is_deleted = 0;
            $sharedPost->save();

            if ($post->relationLoaded('media') && $post->media->isNotEmpty()) {
                $sharedPost->media()->attach($post->media->pluck('id')->all());
            }

            DB::table('post_shares')->insert([
                'user_id' => $userId,
                'post_id' => $post->id,
                'comment' => $sharedComment !== '' ? $sharedComment : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $post->increment('share_count');

            if ((int) $post->user_id !== (int) $userId) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'sender_id' => $userId,
                    'post_id' => $post->id,
                    'type' => 'share',
                    'message' => 'đã chia sẻ bài viết của bạn',
                    'is_read' => 0,
                ]);
            }
        });

        $post->refresh();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'shareCount' => (int) $post->share_count,
                'message' => 'Bạn đã chia sẻ bài viết thành công.',
            ]);
        }

        return back()->with('success', 'Bạn đã chia sẻ bài viết lên tường thành công!');
    }

    public function toggleFollow(User $user)
    {
        $request = request();
        $authId = Auth::id();
        $me = $authId ? User::find($authId) : null;

        if (! $me || $me->id === $user->id) {
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => $status,
                'is_following' => $status === 'followed',
            ]);
        }

        return back()->with('success', $status === 'followed' ? 'Đã theo dõi người dùng.' : 'Đã hủy theo dõi người dùng.');
    }

    public function likePost(Post $post)
    {
        return $this->like(request(), $post);
    }

    public function report(Request $request)
    {
        \Illuminate\Support\Facades\DB::table('reports')->insert([
            'reporter_user_id' => auth()->id(),
            'reported_entity_type' => $request->type,
            'reported_entity_id' => $request->id,
            'reason' => $request->reason,
            'additional_notes' => $request->notes,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    public function likeComment($commentId)
    {
        $comment = \App\Models\Comment4::findOrFail($commentId);
        $user = auth()->user();

        // toggle() sẽ tự động thêm nếu chưa like, và xóa nếu đã like rồi
        $liked = $comment->likes()->toggle($user->id);
        
        return response()->json([
            'liked' => count($liked['attached']) > 0,
            'count' => $comment->likes()->count()
        ]);
    }
}
