<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bookmark3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BookmarkController3 extends Controller
{
    // Thêm hàm này vào BookmarkController3.php
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
    if (! $userId) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $folderName = $request->folder_name ?? 'Tất cả';

    $hasSoftDeleteFlag = Schema::hasTable('bookmarks3') && Schema::hasColumn('bookmarks3', 'is_deleted');

    // Tìm bookmark bao gồm cả cái đã "xóa mềm" trước đó
    $bookmark = Bookmark3::where('user_id', $userId)
                         ->where('post_id', $postId)
                         ->first();

    if ($bookmark) {
        if ($hasSoftDeleteFlag && (int) $bookmark->is_deleted === 0) {
            // Nếu đang hiển thị -> Đánh dấu là đã xóa (Biến mất trên giao diện)
            $bookmark->update(['is_deleted' => 1]);
            return response()->json(['status' => 'removed']);
        }

        if ($hasSoftDeleteFlag) {
            // Nếu đã xóa trước đó -> Khôi phục lại và cập nhật folder mới
            $bookmark->update([
                'is_deleted' => 0,
                'folder_name' => $folderName
            ]);
            return response()->json(['status' => 'added']);
        }

        // Nếu bảng không có cột is_deleted thì coi như toggle theo hướng xóa cứng bản ghi bookmark.
        $bookmark->delete();
        return response()->json(['status' => 'removed']);
    } else {
        // Nếu chưa từng có -> Tạo mới
        $payload = [
            'user_id' => $userId,
            'post_id' => $postId,
            'folder_name' => $folderName,
        ];

        if ($hasSoftDeleteFlag) {
            $payload['is_deleted'] = 0;
        }

        Bookmark3::create($payload);
        return response()->json(['status' => 'added']);
    }
}

public function index()
{
    $userId = Auth::id();
    if (! $userId) {
        return redirect()->route('login');
    }

    // CHỈ LẤY những bookmark có is_deleted = 0
    $bookmarksQuery = Bookmark3::where('user_id', $userId);

    if (Schema::hasTable('bookmarks3') && Schema::hasColumn('bookmarks3', 'is_deleted')) {
        $bookmarksQuery->where('is_deleted', 0);
    }

    $bookmarks = $bookmarksQuery
        ->with('post.media')
        ->latest()
        ->get();

    return view('bookmarks3', compact('bookmarks'));
}
    
    }