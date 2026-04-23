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
            : 'author_user_id';

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
        $posts = \App\Models\Post::with('media')
                         ->where('author_user_id', $user->id) 
                         ->where('is_deleted', false)
                         ->latest()
                         ->get();
        return view('profile.show', compact('user', 'isOwnProfile', 'postCount', 'followingCount', 'followerCount', 'isFollowing', 'posts'));
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

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $fileName);

            if (Schema::hasColumn('users', 'avatar_url')) {
                $user->avatar_url = 'uploads/avatars/' . $fileName;
            }
        }

        $user->display_name = $request->string('display_name');
        $user->bio = $request->string('bio')->toString();
        $user->save();

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
