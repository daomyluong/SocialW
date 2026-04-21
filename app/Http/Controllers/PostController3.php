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

class PostController3 extends Controller
{
    // Hiển thị trang đăng bài
    public function create() {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return view('posts3.create3'); 
    }

    // Xử lý lưu bài viết và ảnh
public function store(Request $request)
{
    if (! Auth::check()) {
        return redirect()->route('login')->with('status', 'Vui lòng đăng nhập để đăng bài viết.');
    }

    $currentUserId = (int) Auth::id();

    // 1. Validate dữ liệu
    $request->validate([
        'content' => 'nullable|string|max:5000|required_without_all:image,video',
        'image.*' => 'nullable|image|max:5120',
        'video.*' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm,m4v|max:102400',
    ]);

    try {
        // 2. Tạo bài viết mới
        $post = Post::create([
            'author_user_id' => $currentUserId,
            'content' => $request->content,
            'visibility' => $request->visibility ?? 'public',
            'is_deleted' => 0
        ]);

        // 3. Xử lý lưu nhiều ảnh
        if ($request->hasFile('image')) {
            File::ensureDirectoryExists(public_path('uploads/posts'));
            foreach ($request->file('image') as $file) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/posts'), $fileName);

                // Tạo bản ghi trong bảng media
                $media = Media::create([
                    'owner_user_id' => $currentUserId,
                    'type' => 'image',
                    'url' => 'uploads/posts/' . $fileName,
                    'filename' => $fileName,
                    'mime' => $file->getClientMimeType(),
                ]);

                // Kết nối bài viết với ảnh qua bảng trung gian
                $post->media()->attach($media->id);

                if (! $post->media_id) {
                    $post->media_id = $media->id;
                    $post->save();
                }
            }
        }

        // 4. Xử lý lưu nhiều video
        if ($request->hasFile('video')) {
            File::ensureDirectoryExists(public_path('uploads/posts'));
            foreach ($request->file('video') as $file) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/posts'), $fileName);

                $media = Media::create([
                    'owner_user_id' => $currentUserId,
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

        // Chuyển hướng về trang danh sách bài viết của tôi
        return redirect()->route('home')->with('success', 'Chúc mừng! Bài viết của bạn đã hiển thị trên bảng tin.');

    } catch (\Exception $e) {
        report($e);
        // Nếu có lỗi thì quay lại và hiện thông báo lỗi
        return back()->withInput()->withErrors(['post_store' => 'Không thể lưu bài viết. ' . $e->getMessage()]);
    }
}

    public function myPosts()
    {
        // 1. Lấy ID của người dùng đang đăng nhập
        $userId = (int) Auth::id();

        if (! $userId) {
            return redirect()->route('login');
        }

        // 2. Lấy ra những bài viết của người dùng đó, kèm theo thông tin ảnh
        $posts = \App\Models\Post::where('author_user_id', $userId)
                                ->where('is_deleted', 0) // Chỉ lấy những bài chưa bị xóa
                                 ->with(['media', 'comments' => function($query) {
                                     $query->latest()->take(5);
                                 }])
                                 ->withCount('comments')
                                 ->latest()
                                 ->get();

        return view('posts3.my_posts3', compact('posts'));
    }

    // Hàm XÓA: Chạy ngầm rồi quay lại trang cũ
    public function destroy($id)
    {
        $post = \App\Models\Post::findOrFail($id);
        $this->authorize('delete', $post);

        $post->update(['is_deleted' => 1]);
        return back()->with('success', 'Đã xóa bài viết!');
    }

    // 1. Hàm hiện form edit
    public function edit($id) {
        $post = \App\Models\Post::findOrFail($id);
        $this->authorize('update', $post);

        return view('posts3.edit3', compact('post'));
    }

    // 2. Hàm xử lý lưu dữ liệu đã sửa
   public function update(Request $request, $id)
{
    // 1. Tìm bài viết
    $post = Post::findOrFail($id);

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

    if ($currentUserId) {
        $followingIds = DB::table('followers')
            ->where('follower_user_id', $currentUserId)
            ->pluck('following_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $postsQuery->where(function ($visibilityScope) use ($currentUserId, $followingIds): void {
            $visibilityScope
                ->where('visibility', 'public')
                ->orWhere('author_user_id', $currentUserId)
                ->orWhere(function ($followersScope) use ($followingIds): void {
                    $followersScope
                        ->whereIn('author_user_id', $followingIds)
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
            ->map(fn ($id) => (int) $id)
            ->all();

        $bookmarkedPostIds = $bookmarksQuery
            ->pluck('post_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $followingIds = DB::table('followers')
            ->where('follower_user_id', $currentUserId)
            ->pluck('following_user_id')
            ->map(fn ($id) => (int) $id)
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
            ->groupBy('user_id'); // Nhóm lại theo từng người dùng
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
    // Tìm bài viết theo ID
    $post = \App\Models\Post::with('media')->findOrFail($id);

    // Trả về view chi tiết  
    return view('posts3.show3', compact('post'));
}
}