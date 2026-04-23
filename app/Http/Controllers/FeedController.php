<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeedController extends Controller
{
    public function latest(Request $request): JsonResponse
    {
        $limit = min((int) $request->integer('limit', 20), 50);
        $since = $request->date('since');
        $postAuthorColumn = Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id')
            ? 'user_id'
            : 'author_user_id';

        try {
            $query = Post::query()
                ->with(['author:id,username,display_name,avatar_url', 'media:id,type,url,mime'])
                ->where('is_deleted', false)
                ->when($since, fn ($q) => $q->where('created_at', '>', $since));

            if (Auth::check()) {
                $userId = (int) Auth::id();
                $followingIds = DB::table('followers')
                    ->where('follower_user_id', $userId)
                    ->pluck('following_user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $query->where(function ($visibilityScope) use ($userId, $followingIds, $postAuthorColumn): void {
                    $visibilityScope
                        ->where('visibility', 'public')
                        ->orWhere($postAuthorColumn, $userId)
                        ->orWhere(function ($followersScope) use ($followingIds, $postAuthorColumn): void {
                            $followersScope
                                ->whereIn($postAuthorColumn, $followingIds)
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

            $posts = $posts
                ->map(function (Post $post): array {
                    $author = $post->author;
                    $content = (string) ($post->content ?? '');
                    preg_match_all('/@([a-zA-Z0-9_\.]+)/', $content, $matches);
                    $mentions = array_values(array_unique($matches[1] ?? []));
                    $mediaItems = $post->media
                        ->map(fn ($media) => [
                            'type' => (string) $media->type,
                            'url' => (string) $media->url,
                            'mime' => (string) ($media->mime ?? ''),
                        ])
                        ->values()
                        ->all();

                    if (empty($mediaItems) && $post->media_id) {
                        $fallback = DB::table('media')->where('id', $post->media_id)->first();
                        if ($fallback) {
                            $mediaItems[] = [
                                'type' => (string) ($fallback->type ?? 'image'),
                                'url' => (string) ($fallback->url ?? ''),
                                'mime' => (string) ($fallback->mime ?? ''),
                            ];
                        }
                    }

                    return [
                        'id' => $post->id,
                        'content' => $content,
                        'like_count' => $post->like_count,
                        'comment_count' => $post->comment_count,
                        'visibility' => $post->visibility,
                        'media_url' => $mediaItems[0]['url'] ?? null,
                        'media_items' => $mediaItems,
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
