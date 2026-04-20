<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController3 extends Controller
{
    // Hiển thị trang đăng bài
    public function create() {
        return view('posts3.create3'); 
    }

    // Xử lý lưu bài viết và ảnh
public function store(Request $request)
{
    // 1. Validate dữ liệu
    $request->validate([
        'content' => 'required',
        'image.*' => 'nullable|image|max:2048'
    ]);

    try {
        // 2. Tạo bài viết mới
        // Lưu ý: Đảm bảo 'author_user_id' khớp với id người dùng (đang để mặc định là 1)
        $post = Post::create([
            
            'author_user_id' => Auth::id(), 
            'content' => $request->content,
            'visibility' => $request->visibility ?? 'public',
            'is_deleted' => 0
        ]);

        // 3. Xử lý lưu nhiều ảnh
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/posts'), $fileName);

                // Tạo bản ghi trong bảng media
                $media = Media::create([
                    'owner_user_id' => Auth::id(),
                    'type' => 'image',
                    'url' => 'uploads/posts/' . $fileName,
                    'filename' => $fileName,
                    'mime' => $file->getClientMimeType(),
                ]);

                // Kết nối bài viết với ảnh qua bảng trung gian
                $post->media()->attach($media->id);
            }
        }

        // Chuyển hướng về trang danh sách bài viết của tôi
        return redirect()->route('home')->with('success', 'Chúc mừng! Bài viết của bạn đã hiển thị trên bảng tin.');

    } catch (\Exception $e) {
        // Nếu có lỗi thì quay lại và hiện thông báo lỗi
        return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    }
}

    public function myPosts()
    {
        // 1. Lấy ID của người dùng đang đăng nhập
        $userId = Auth::id();

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
        // Kiểm tra xem người dùng có phải là tác giả không
        if ($post->author_user_id !== Auth::id()) {
            return back()->with('error', 'Bạn không có quyền xóa bài viết này!');
        }
        $post->update(['is_deleted' => 1]);
        return back()->with('success', 'Đã xóa bài viết!');
    }

    // 1. Hàm hiện form edit
    public function edit($id) {
        $post = \App\Models\Post::findOrFail($id);
        // Kiểm tra xem người dùng có phải là tác giả không
        if ($post->author_user_id !== Auth::id()) {
            return back()->with('error', 'Bạn không có quyền chỉnh sửa bài viết này!');
        }
        return view('posts3.edit3', compact('post'));
    }

    // 2. Hàm xử lý lưu dữ liệu đã sửa
   public function update(Request $request, $id)
{
    // 1. Tìm bài viết
    $post = Post::findOrFail($id);
    
    // Kiểm tra xem người dùng có phải là tác giả không
    if ($post->author_user_id !== Auth::id()) {
        return back()->with('error', 'Bạn không có quyền cập nhật bài viết này!');
    }

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
        }
    }

    return redirect()->route('posts3.myPosts')->with('success', 'Đã cập nhật bài viết thành công!');
}
    public function index()
{

    // 1. Lấy dữ liệu bài viết 
    $userId = Auth::id() ?? 1;
    // 1. Lấy dữ liệu bài viết (Gộp logic: lấy 5 cmt mới nhất + đếm tổng số cmt)
    $posts = \App\Models\Post::where('is_deleted', 0)
                ->with(['media', 'comments' => function($query) {
                    $query->latest()->take(5); 
                }])
                ->withCount('comments') 
                ->latest()
                ->get();

    // 2. Lấy dữ liệu Story trong vòng 24h qua
    $stories = \App\Models\Story3::active24h()
                ->with('user') // Lấy thông tin người đăng để hiện avatar
                ->latest()
                ->get()
                ->groupBy('user_id'); // Nhóm lại theo từng người dùng
    return view('home', compact('posts', 'stories'));
}
    public function notifications()
{
    $userId = 1; 

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

    
    $posts->each(function($post) use ($userId) {
        $post->is_liked_by_me = DB::table('post_likes')
            ->where('post_id', $post->id)
            ->where('user_id', $userId)
            ->exists(); // Trả về true hoặc false
    });

    // 2. Lấy danh sách người dùng để hiển thị trong danh sách chia sẻ
    $allUsers = \App\Models\User::where('id', '!=', Auth::id())->get();

    // 3. Lấy danh sách gợi ý người dùng để follow
    $followingIds = DB::table('followers')
        ->where('follower_id', $userId)
        ->pluck('following_id')
        ->toArray();

    $suggestedUsers = User::where('id', '!=', $userId)
        ->whereNotIn('id', $followingIds)
        ->inRandomOrder()
        ->limit(5)
        ->get();

    return view('home', compact('posts', 'allUsers', 'suggestedUsers'));
}
}