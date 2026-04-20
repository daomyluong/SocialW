<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function latest(Request $request): JsonResponse
    {
        $limit = min((int) $request->integer('limit', 20), 50);
        $since = $request->date('since');

        try {
            $query = Post::query()
                ->with(['author:id,username,display_name,avatar_url'])
                ->where('is_deleted', false)
                ->when($since, fn ($q) => $q->where('created_at', '>', $since));

            if (Auth::check()) {
                $userId = (int) Auth::id();
                $followingIds = DB::table('followers')
                    ->where('follower_user_id', $userId)
                    ->pluck('following_user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $query->where(function ($visibilityScope) use ($userId, $followingIds): void {
                    $visibilityScope
                        ->where('visibility', 'public')
                        ->orWhere('author_user_id', $userId)
                        ->orWhere(function ($followersScope) use ($followingIds): void {
                            $followersScope
                                ->whereIn('author_user_id', $followingIds)
                                ->whereIn('visibility', ['public', 'follower']);
                        });
                });
            } else {
                $query->where('visibility', 'public');
            }

            $posts = $query
                ->latest()
                ->limit($limit)
                ->get();

            $mediaById = DB::table('media')
                ->whereIn('id', $posts->pluck('media_id')->filter()->all())
                ->pluck('url', 'id');

            $posts = $posts
                ->map(function (Post $post) use ($mediaById): array {
                    $author = $post->author;
                    $content = (string) ($post->content ?? '');
                    preg_match_all('/@([a-zA-Z0-9_\.]+)/', $content, $matches);
                    $mentions = array_values(array_unique($matches[1] ?? []));

                    return [
                        'id' => $post->id,
                        'content' => $content,
                        'like_count' => $post->like_count,
                        'comment_count' => $post->comment_count,
                        'visibility' => $post->visibility,
                        'media_url' => $post->media_id ? $mediaById->get($post->media_id) : null,
                        'mentions' => $mentions,
                        'created_at' => optional($post->created_at)->toIso8601String(),
                        'created_at_human' => optional($post->created_at)->diffForHumans(),
                        'author' => [
                            'id' => $author?->id,
                            'username' => $author?->username ?? 'guest',
                            'display_name' => $author?->display_name ?? $author?->name ?? 'Nguoi dung',
                            'avatar_url' => $author?->avatar_url,
                        ],
                    ];
                })
                ->values();
        } catch (\Throwable) {
            $posts = collect();
        }

        return response()->json([
            'data' => $posts,
            'meta' => [
                'count' => $posts->count(),
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }
}
