<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Media;
use App\Models\Bookmark3;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\Story3;


class PostController3 extends Controller
{
    private function postAuthorColumn(): string
    {
        return Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id')
            ? 'user_id'
            : 'user_id';
    }

    // Hiển thị trang đăng bài
    public function create()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('posts3.create3');
    }

    // Xử lý lưu bài viết và ảnh
    public function store(Request $request)
{
    $request->validate([
        'content' => 'nullable|required_without_all:image,video',
        'image.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        'video.*' => 'mimes:mp4,avi,mov|max:10240',
    ]);

    $post = new \App\Models\Post();
    $post->user_id = Auth::id() ?? 1;
    $post->content = $request->content;
    $post->visibility = 'public';
    $post->is_deleted = 0;

    $post->save(); 

if ($request->hasFile('image')) {
        foreach ($request->file('image') as $file) {
            // 1. Tạo tên file duy nhất
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // 2. DI CHUYỂN VÀO PUBLIC (Sửa chỗ này nè Thanh)
            $file->move(public_path('uploads/posts'), $fileName);

            // 3. Lưu vào DB
            $media = Media::create([
                'owner_user_id' => Auth::id(),
                'type' => 'image',
                'url' => 'uploads/posts/' . $fileName,
                'filename' => $fileName,
                'mime' => $file->getClientMimeType(),
            ]);

            $post->media()->attach($media->id);
        }
    }

    // Xử lý video (cũng sửa tương tự luôn)
    if ($request->hasFile('video')) {
        foreach ($request->file('video') as $file) {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Di chuyển vào public
            $file->move(public_path('uploads/posts'), $fileName);

            $media = Media::create([
                'owner_user_id' => Auth::id(),
                'type' => 'video',
                'url' => 'uploads/posts/' . $fileName,
                'filename' => $fileName,
                'mime' => $file->getClientMimeType(),
            ]);

            $post->media()->attach($media->id);
        }
    }


    return redirect()->route('home')->with('success', 'Đăng bài thành công!');
}

 public function myPosts(Request $request)
{
    $query = Post::with('media')->where('user_id', auth()->id());

    // Tìm kiếm theo từ khóa
    if ($request->has('search') && $request->search != '') {
        $query->where('content', 'like', '%' . $request->search . '%');
    }

    // Lọc theo chế độ hiển thị
    if ($request->has('visibility') && $request->visibility != '') {
        $query->where('visibility', $request->visibility);
    }

    // Lấy dữ liệu và phân trang (mỗi trang 6 bài cho đẹp grid)
    $posts = $query->latest()->paginate(6);

    return view('posts3.my_posts3', compact('posts'));
}

    public function destroy($id)
    {
        $post = \App\Models\Post::findOrFail($id);
        $this->authorize('delete', $post);

        $post->update(['is_deleted' => 1]);
        return back()->with('success', 'Đã xóa bài viết!');
    }

    // 1. Hàm hiện form edit
    public function edit($id)
    {
        $post = \App\Models\Post::findOrFail($id);
        $this->authorize('update', $post);

        return view('posts3.edit3', compact('post'));
    }

    // 2. Hàm xử lý lưu dữ liệu đã sửa
    public function update(Request $request, $id)
    {
        // 1. Tìm bài viết
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        $this->authorize('update', $post);

        // 2. Cập nhật nội dung chữ và quyền riêng tư
        $post->update([
            'content' => $request->content,
            'visibility' => $request->visibility,
            'is_edited' => 1
        ]);

        // 3. Xử lý ảnh (Nếu người dùng có chọn ảnh mới)
        if ($request->hasFile('image')) {

            // Tùy chọn: Xóa ảnh cũ nếu bạn muốn thay thế toàn bộ ảnh mới
            // $post->media()->detach(); 

            foreach ($request->file('image') as $file) {
                // Tạo tên file duy nhất
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/posts'), $fileName);

                // Lưu vào bảng media
                $media = Media::create([
                    'owner_user_id' => Auth::id(),
                    'type' => 'image',
                    'url' => 'uploads/posts/' . $fileName,
                    'filename' => $fileName,
                    'mime' => $file->getClientMimeType(),
                ]);

                // Gắn ảnh vào bài viết (bảng trung gian)
                $post->media()->attach($media->id);

                if (! $post->media_id) {
                    $post->media_id = $media->id;
                    $post->save();
                }
            }
        }

        if ($request->hasFile('video')) {
            foreach ($request->file('video') as $file) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/posts'), $fileName);

                $media = Media::create([
                    'owner_user_id' => Auth::id(),
                    'type' => 'video',
                    'url' => 'uploads/posts/' . $fileName,
                    'filename' => $fileName,
                    'mime' => $file->getClientMimeType(),
                ]);

                $post->media()->attach($media->id);

                if (! $post->media_id) {
                    $post->media_id = $media->id;
                    $post->save();
                }
            }
        }

        return redirect()->route('posts3.myPosts')->with('success', 'Đã cập nhật bài viết thành công!');
    }
    public function index()
    {
        $currentUserId = Auth::id() ? (int) Auth::id() : null;

        $postsQuery = \App\Models\Post::query()
            ->where('is_deleted', 0)
            ->with([
                'author:id,username,display_name,avatar_url',
                'media',
                'comments' => function ($query) {
                    $query->with('user:id,username,display_name,avatar_url')->latest()->take(5);
                },
            ])
            ->withCount('comments');

        $postAuthorColumn = $this->postAuthorColumn();

        if ($currentUserId) {
            $followingIds = DB::table('followers')
                ->where('follower_user_id', $currentUserId)
                ->pluck('following_user_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $postsQuery->where(function ($visibilityScope) use ($currentUserId, $followingIds, $postAuthorColumn): void {
                $visibilityScope
                    ->where('visibility', 'public')
                    ->orWhere($postAuthorColumn, $currentUserId)
                    ->orWhere(function ($followersScope) use ($followingIds, $postAuthorColumn): void {
                        $followersScope
                            ->whereIn($postAuthorColumn, $followingIds)
                            ->whereIn('visibility', ['public', 'follower']);
                    });
            });
        } else {
            $postsQuery->where('visibility', 'public');
        }

        $posts = $postsQuery->latest()->get();

        $likedPostIds = [];
        $bookmarkedPostIds = [];
        $suggestedUsers = collect();
        $savedPosts = collect();

        if ($currentUserId) {
            $bookmarksQuery = Bookmark3::query()->where('user_id', $currentUserId);
            $savedPostsQuery = Bookmark3::query()->where('user_id', $currentUserId);

            if (Schema::hasTable('bookmarks3') && Schema::hasColumn('bookmarks3', 'is_deleted')) {
                $bookmarksQuery->where('is_deleted', 0);
                $savedPostsQuery->where('is_deleted', 0);
            }

            $likedPostIds = DB::table('post_likes')
                ->where('user_id', $currentUserId)
                ->pluck('post_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $bookmarkedPostIds = $bookmarksQuery
                ->pluck('post_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $followingIds = DB::table('followers')
                ->where('follower_user_id', $currentUserId)
                ->pluck('following_user_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $suggestedUsers = User::query()
                ->where('id', '!=', $currentUserId)
                ->where('is_active', 1)
                ->whereNotIn('id', $followingIds)
                ->inRandomOrder()
                ->limit(6)
                ->get();

            $savedPosts = $savedPostsQuery
                ->with(['post.author:id,username,display_name'])
                ->latest()
                ->limit(5)
                ->get();
        }

        // 2. Lấy dữ liệu Story trong vòng 24h qua
        $stories = collect();

        if (Schema::hasTable('stories')) {
            $stories = \App\Models\Story3::active24h()
                ->with('user') // Lấy thông tin người đăng để hiện avatar
                ->latest()
                ->get()
                ->groupBy('user_id');
        }

        return view('home', compact('posts', 'stories', 'likedPostIds', 'bookmarkedPostIds', 'suggestedUsers', 'savedPosts'));
    }
    public function notifications()
    {
        $userId = Auth::id();

        if (! $userId) {
            return redirect()->route('login');
        }

        // Lấy tất cả thông báo của User 
        $notifications = \App\Models\Notification::where('user_id', $userId)
            ->latest()
            ->get();

        // Khi người dùng vào trang này, chúng ta coi như họ đã đọc hết
        \App\Models\Notification::where('user_id', $userId)->update(['is_read' => 1]);

        return view('posts3.notifications3', compact('notifications'));
    }
    public function show($id)
    {
        $post = \App\Models\Post::with([
            'author:id,username,display_name,avatar_url',
            'media',
            'comments.user:id,username,display_name,avatar_url',
        ])->findOrFail($id);

        $stories = \App\Models\Story3::latest()->get();

        return view('posts3.show3', compact('post', 'stories'));
    }
}
