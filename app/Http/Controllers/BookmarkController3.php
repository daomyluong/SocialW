<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bookmark3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BookmarkController3 extends Controller
{
public function getFolders()
{
    $userId = Auth::id();
    if (! $userId) {
        return response()->json([]);
    }

    // Lấy các tên thư mục duy nhất của user này
    $folders = Bookmark3::where('user_id', $userId)
                        ->distinct()
                        ->pluck('folder_name');
    return response()->json($folders);
}

public function toggleBookmark(Request $request, $postId)
{
    $userId = Auth::id();
    $folderName = $request->input('folder_name', 'Tất cả');

    // Tìm xem bài này đã lưu chưa
    $bookmark = Bookmark3::where('user_id', $userId)->where('post_id', $postId)->first();

    if ($bookmark) {
        // Nếu đã tồn tại, cập nhật lại thư mục mới
        $bookmark->delete();
        return response()->json(['status' => 'removed']);
    } else {
        // Nếu chưa, tạo mới hoàn toàn
        Bookmark3::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'folder_name' => $folderName,
            'is_deleted' => 0
        ]);
        return response()->json(['status' => 'added']);
    }
}

public function index(Request $request)
{
    $userId = Auth::id();

    // 1. Lấy danh sách thư mục duy nhất (sidebar)
    $allFolders = Bookmark3::where('user_id', $userId)
                           ->whereNotNull('folder_name')
                           ->distinct()
                           ->pluck('folder_name');

    // 2. Lấy bài viết dựa trên folder được chọn
    $query = Bookmark3::where('user_id', $userId)->with('post.author','post.media');
    
    if ($request->has('folder') && $request->folder !== 'Tất cả') {
        $query->where('folder_name', $request->folder);
    }
    
    $bookmarks = $query->latest()->get();

    return view('bookmarks3', compact('bookmarks', 'allFolders'));
}
    public function storeFolder(Request $request)
{

    return back()->with('success', 'Tính năng tạo thư mục đang được cập nhật.');
}
}