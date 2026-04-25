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
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'Chưa đăng nhập'], 401);
            }
            
            $folderName = $request->input('folder_name', 'Tất cả');
            $action = $request->input('action', 'toggle'); // toggle, remove, add

            // Tìm xem bài này đã lưu chưa (chỉ tìm bookmark chưa bị xóa)
            $bookmark = Bookmark3::where('user_id', $userId)
                                 ->where('post_id', $postId)
                                 ->where('is_deleted', 0)
                                 ->first();

            // Xử lý bỏ lưu (remove)
            if ($action === 'remove' && $bookmark) {
                $bookmark->update(['is_deleted' => 1]);
                return response()->json(['status' => 'removed']);
            }

            if ($bookmark) {
                // Nếu đã tồn tại, cập nhật lại thư mục mới
                $bookmark->update(['folder_name' => $folderName]);
                return response()->json(['status' => 'updated']);
            } else {
                // Kiểm tra xem có bookmark bị xóa mềm không, nếu có thì khôi phục
                $deletedBookmark = Bookmark3::where('user_id', $userId)
                                             ->where('post_id', $postId)
                                             ->where('is_deleted', 1)
                                             ->first();
                
                if ($deletedBookmark) {
                    $deletedBookmark->update([
                        'folder_name' => $folderName,
                        'is_deleted' => 0
                    ]);
                    return response()->json(['status' => 'restored']);
                }
                
                // Nếu chưa, tạo mới hoàn toàn
                $newBookmark = Bookmark3::create([
                    'user_id' => $userId,
                    'post_id' => $postId,
                    'folder_name' => $folderName,
                    'is_deleted' => 0
                ]);
                
                return response()->json(['status' => 'added', 'bookmark' => $newBookmark]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Lấy tất cả bookmark (bao gồm cả is_deleted = 0 và is_deleted = 1)
        $allBookmarks = Bookmark3::where('user_id', $userId)
                                 ->with('post.author', 'post.media')
                                 ->latest()
                                 ->get();

        // 2. Lấy danh sách thư mục duy nhất (chỉ lấy folder của bookmark chưa xóa)
        $allFolders = $allBookmarks->where('is_deleted', 0)
                                   ->pluck('folder_name')
                                   ->unique()
                                   ->filter()
                                   ->sort()
                                   ->values();

        // 3. Lọc theo folder từ URL
        $selectedFolder = $request->folder;
        if ($selectedFolder && $selectedFolder !== 'Tất cả') {
            $bookmarks = $allBookmarks->where('folder_name', $selectedFolder);
        } else {
            $bookmarks = $allBookmarks;
        }

        return view('bookmarks3', compact('bookmarks', 'allFolders', 'selectedFolder'));
    }
    public function storeFolder(Request $request)
{

    return back()->with('success', 'Tính năng tạo thư mục đang được cập nhật.');
}
}