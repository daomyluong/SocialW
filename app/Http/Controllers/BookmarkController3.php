<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bookmark3;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class BookmarkController3 extends Controller
{
    // Thêm hàm này vào BookmarkController3.php
public function getFolders()
{
    $userId = Auth::id() ?? 1;
    // Lấy các tên thư mục duy nhất của user này
    $folders = Bookmark3::where('user_id', $userId)
                        ->distinct()
                        ->pluck('folder_name');
    return response()->json($folders);
}

public function toggleBookmark(Request $request, $postId)
{
    $userId = Auth::id() ?? 1;
    $folderName = $request->folder_name ?? 'Tất cả';

    // Tìm bookmark bao gồm cả cái đã "xóa mềm" trước đó
    $bookmark = Bookmark3::where('user_id', $userId)
                         ->where('post_id', $postId)
                         ->first();

    if ($bookmark) {
        if ($bookmark->is_deleted == 0) {
            // Nếu đang hiển thị -> Đánh dấu là đã xóa (Biến mất trên giao diện)
            $bookmark->update(['is_deleted' => 1]);
            return response()->json(['status' => 'removed']);
        } else {
            // Nếu đã xóa trước đó -> Khôi phục lại và cập nhật folder mới
            $bookmark->update([
                'is_deleted' => 0,
                'folder_name' => $folderName
            ]);
            return response()->json(['status' => 'added']);
        }
    } else {
        // Nếu chưa từng có -> Tạo mới
        Bookmark3::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'folder_name' => $folderName,
            'is_deleted' => 0
        ]);
        return response()->json(['status' => 'added']);
    }
}

public function index()
{
    $userId = Auth::id() ?? 1;
    // CHỈ LẤY những bookmark có is_deleted = 0
    $bookmarks = Bookmark3::where('user_id', $userId)
                            ->where('is_deleted', 0)
                            ->with('post.media')
                            ->latest()
                            ->get();

    return view('bookmarks3', compact('bookmarks'));
}
    
    }