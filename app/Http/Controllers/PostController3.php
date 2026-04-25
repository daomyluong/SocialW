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
    $post->visibility = $request->input('visibility', 'public');
    $post->is_deleted = 0;

    $post->save(); 

    if ($request->hasFile('image')) {
        foreach ($request->file('image') as $file) {
            $path = $file->store('uploads/posts', 'public');

            $media = \App\Models\Media::create([
            'owner_user_id' => Auth::id(),
            'type' => 'image',
            'url' => $path,
            'filename' => basename($path),
            'mime' => $file->getClientMimeType(),
        ]);

        $post->media()->attach($media->id);
        }
    }

    if ($request->hasFile('video')) {
        foreach ($request->file('video') as $file) {
            $path = $file->store('uploads/posts', 'public');

            $media = \App\Models\Media::create([
            'owner_user_id' => Auth::id(),
            'type' => 'video',
            'url' => $path,
            'filename' => basename($path),
            'mime' => $file->getClientMimeType(),
        ]);

        $post->media()->attach($media->id);
        }
    }

    return redirect()->route('home')->with('success', 'Đăng bài thành công!');
}

    public function myPosts()
    {
        // 1. Lấy ID của người dùng đang đăng nhập
        $userId = (int) Auth::id();

        if (! $userId) {
            return redirect()->route('login');
        }

        // 2. Lấy ra những bài viết của người dùng đó, kèm theo thông tin ảnh
        $posts = \App\Models\Post::where($this->postAuthorColumn(), $userId)
            ->where('is_deleted', 0) // Chỉ lấy những bài chưa bị xóa
            ->with(['media', 'comments' => function ($query) {
                $query->latest()->take(5);
            }])
            ->withCount('comments')
            ->latest()
            ->get();

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

        // 3. XÓA ẢNH CŨ
        if ($request->has('delete_images')) {
            $imagesToDelete = $request->input('delete_images'); 
            
            if (in_array($post->media_id, $imagesToDelete)) {
                $post->media_id = null; 
                $post->save();          
            }

            foreach ($imagesToDelete as $mediaId) {
                $media = Media::find($mediaId);
                
                if ($media) { 
                    // Đổi sang xóa file trong Storage cho đồng bộ
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($media->url)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($media->url);
                    }
                    $post->media()->detach($mediaId);
                    $media->delete();
                }
            }
            
            if (is_null($post->media_id)) {
                $firstRemainingMedia = $post->media()->first();
                $post->media_id = $firstRemainingMedia ? $firstRemainingMedia->id : null;
                $post->save();
            }
        }

        // 4. LƯU THÊM ẢNH MỚI (Lưu vào Storage giống y hệt lúc Create)
        if ($request->hasFile('image')) {
            $newMediaIds = [];
            foreach ($request->file('image') as $file) {
                // SỬA LẠI THÀNH STORE VÀO PUBLIC DISK
                $path = $file->store('uploads/posts', 'public');

                $media = Media::create([
                    'owner_user_id' => Auth::id(),
                    'type' => 'image',
                    'url' => $path, // Lưu path chuẩn vào DB
                    'filename' => basename($path),
                    'mime' => $file->getClientMimeType(),
                ]);
                $newMediaIds[] = $media->id;
            }

            if (!empty($newMediaIds)) {
                $post->media()->syncWithoutDetaching($newMediaIds);
                if (!$post->media_id) {
                     $post->media_id = $newMediaIds[0];
                     $post->save();
                }
            }
        }

        // 5. TƯƠNG TỰ CHO VIDEO
        if ($request->hasFile('video')) {
            $newVideoIds = [];
            foreach ($request->file('video') as $file) {
                $path = $file->store('uploads/posts', 'public');

                $media = Media::create([
                    'owner_user_id' => Auth::id(),
                    'type' => 'video',
                    'url' => $path,
                    'filename' => basename($path),
                    'mime' => $file->getClientMimeType(),
                ]);
                $newVideoIds[] = $media->id;
            }

             if (!empty($newVideoIds)) {
                $post->media()->syncWithoutDetaching($newVideoIds);
                if (!$post->media_id) {
                     $post->media_id = $newVideoIds[0];
                     $post->save();
                }
            }
        }

        return redirect()->route('posts3.myPosts')->with('success', 'Đã cập nhật bài viết thành công!');
    }

    public function index()
    {
        $currentUserId = Auth::id();

        // 1. Lấy bài viết (Dùng ống lọc visible)
        $posts = \App\Models\Post::visible()
            ->with([
                'author:id,username,display_name,avatar_url',
                'media',
                'comments' => function ($query) {
                    $query->with('user:id,username,display_name,avatar_url')->latest()->take(5);
                },
            ])
            ->withCount('comments')
            ->latest()
            ->get();

        $likedPostIds = [];
        $bookmarkedPostIds = [];
        $suggestedUsers = collect();
        $savedPosts = collect();

        if ($currentUserId) {
            // Lấy danh sách ID đã like
            $likedPostIds = DB::table('post_likes')->where('user_id', $currentUserId)->pluck('post_id')->toArray();

            // Lấy danh sách ID đã bookmark
            $bookmarkedPostIds = DB::table('bookmarks3')->where('user_id', $currentUserId)->where('is_deleted', 0)->pluck('post_id')->toArray();

            // Lấy gợi ý kết bạn
            $followingIds = DB::table('followers')->where('follower_user_id', $currentUserId)->pluck('following_user_id')->toArray();
            $suggestedUsers = User::where('id', '!=', $currentUserId)->whereNotIn('id', $followingIds)->inRandomOrder()->limit(6)->get();

            // Lấy bài viết đã lưu gần đây
            $savedPosts = \App\Models\Bookmark3::where('user_id', $currentUserId)->where('is_deleted', 0)->with(['post.author'])->latest()->limit(5)->get();
        }

        // 2. Lấy Story
        $stories = collect();
        if (Schema::hasTable('stories')) {
            $stories = \App\Models\Story3::active24h()->with('user')->latest()->get()->groupBy('user_id');
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
        // Nếu bài viết không Visible với người xem, Laravel tự văng lỗi 404/403 luôn
        $post = \App\Models\Post::visible()->with([
            'author:id,username,display_name,avatar_url',
            'media',
            'comments.user:id,username,display_name,avatar_url',
        ])->findOrFail($id);

        $stories = \App\Models\Story3::latest()->get();

        return view('posts3.show3', compact('post', 'stories'));
    }
}
