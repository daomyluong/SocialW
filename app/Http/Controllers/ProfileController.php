<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile');
    }

    public function show(?int $id = null): View|RedirectResponse
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();

        $user = $id ? User::findOrFail($id) : Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $isOwnProfile = (bool) $authUser && (int) $authUser->id === (int) $user->id;

        $postAuthorColumn = Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id')
            ? 'user_id'
            : 'user_id';

        $postCount = Schema::hasTable('posts')
            ? DB::table('posts')
            ->where($postAuthorColumn, $user->id)
            ->where('is_deleted', false)
            ->count()
            : 0;

        $followingCount = $user->following_count ?? $user->following()->count();
        $followerCount = $user->follower_count ?? $user->followers()->count();

        $isFollowing = false;
        if ($authUser && (int)$authUser->id !== (int)$user->id) {
            $isFollowing = DB::table('followers')
                ->where('follower_user_id', $authUser->id)
                ->where('following_user_id', $user->id)
                ->exists();
        }
        $currentUserId = Auth::id();

        // Lấy bài viết giống feed
        $posts = \App\Models\Post::query()
            ->where('user_id', $user->id)
            ->where('is_deleted', 0)
            ->with([
                'author:id,username,display_name,avatar_url',
                'media',
                'comments' => function ($query) {
                    $query->with('user:id,username,display_name,avatar_url')
                        ->latest()
                        ->take(5);
                },
            ])
            ->withCount('comments')
            ->latest()
            ->get();


        // LIKE
        $likedPostIds = [];
        $bookmarkedPostIds = [];

        if ($currentUserId) {
            $likedPostIds = DB::table('post_likes')
                ->where('user_id', $currentUserId)
                ->pluck('post_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $bookmarkedPostIds = DB::table('bookmarks3')
                ->where('user_id', $currentUserId)
                ->pluck('post_id')
                ->map(fn($id) => (int) $id)
                ->all();
        }
        return view('profile.show', compact(
            'user',
            'isOwnProfile',
            'postCount',
            'followingCount',
            'followerCount',
            'isFollowing',
            'posts',
            'likedPostIds',
            'bookmarkedPostIds'
        ));
    }

    public function edit(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request, ?int $id = null): RedirectResponse
    {
        /** @var User|null $user */
        $authUser = Auth::user();

        $user = $id ? User::findOrFail($id) : $authUser;

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $authUser) {
            return redirect()->route('login');
        }

        $this->authorize('update', $user);

        $request->validate([
            'display_name' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->has('remove_avatar')) {
            if ($user->avatar_url && file_exists(public_path($user->avatar_url))) {
                unlink(public_path($user->avatar_url));
            }

            $user->avatar_url = null;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $fileName);

            $user->avatar_url = 'uploads/avatars/' . $fileName;
        }

        $user->display_name = $request->input('display_name');
        $user->bio = $request->input('bio');
        $user->save();

        Auth::setUser($user);

        return redirect()->route('profile.show', $user->id)->with('success', 'Cập nhật thành công!');
    }

    public function follow(Request $request, int $id): JsonResponse|RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        if (! $currentUser) {
            return redirect()->route('login');
        }

        if ((int) $currentUser->id === (int) $targetUser->id) {
            return back()->with('error', 'Không thể theo dõi chính mình!');
        }

        $alreadyFollowing = $currentUser->following()->where('following_user_id', $targetUser->id)->exists();

        if (! $alreadyFollowing) {
            $currentUser->following()->attach($targetUser->id);
            $targetUser->increment('follower_count');
            $currentUser->increment('following_count');
        } else {
            $currentUser->following()->detach($targetUser->id);
            $targetUser->decrement('follower_count');
            $currentUser->decrement('following_count');
        }

        $isFollowing = ! $alreadyFollowing;

        return response()->json([
            'is_following' => $isFollowing,
        ]);
    }
}
