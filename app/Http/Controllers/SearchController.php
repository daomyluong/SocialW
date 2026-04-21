<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function mentionSuggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        try {
            $users = User::query()
                ->select(['id', 'username', 'display_name', 'avatar_url'])
                ->when($query !== '', function ($builder) use ($query): void {
                    $builder->where(function ($q) use ($query): void {
                        $q->where('username', 'like', "%{$query}%")
                            ->orWhere('display_name', 'like', "%{$query}%");
                    });
                })
                ->whereNotNull('username')
                ->limit(8)
                ->get();
        } catch (QueryException|Throwable) {
            $users = collect();
        }

        return response()->json([
            'data' => $users->map(fn (User $user): array => [
                'id' => $user->id,
                'username' => (string) $user->username,
                'display_name' => (string) ($user->display_name ?? $user->username),
                'avatar_url' => $user->avatar_url,
            ])->values(),
        ]);
    }

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('query', ''));

        $users = collect();
        $posts = collect();

        if ($query !== '') {
            try {
                $users = User::query()
                    ->select(['id', 'username', 'display_name', 'name', 'avatar_url'])
                    ->where(function ($q) use ($query): void {
                        $q->where('username', 'like', "%{$query}%")
                            ->orWhere('display_name', 'like', "%{$query}%")
                            ->orWhere('name', 'like', "%{$query}%");
                    })
                    ->limit(10)
                    ->get();

                $posts = Post::query()
                    ->with(['author:id,username,display_name,name'])
                    ->where('is_deleted', false)
                    ->where('visibility', 'public')
                    ->where('content', 'like', "%{$query}%")
                    ->latest()
                    ->limit(10)
                    ->get();
            } catch (QueryException|Throwable $e) {
                // Keep search page responsive even when DB is temporarily unavailable.
                $users = collect();
                $posts = collect();
            }
        }

        return view('search', [
            'query' => $query,
            'users' => $users,
            'posts' => $posts,
        ]);
    }
}