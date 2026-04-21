<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController5 extends Controller
{
    private function currentAdminId(): ?int
    {
        return Auth::id() ? (int) Auth::id() : null;
    }

    // ===================================================
    // 1. TRANG BẢNG ĐIỀU KHIỂN (DASHBOARD)
    // ===================================================
    public function dashboard()
    {
        $this->authorize('manageAdmin', User::class);

        // Thống kê 4 thẻ Tổng quan
        $admin_total_users = DB::table('users')->count();
        $admin_total_posts = DB::table('posts')->count();
        $admin_total_likes = DB::table('posts')->sum('like_count'); 
        $admin_banned_users = DB::table('users')->where('is_active', 0)->count(); 

        // Dữ liệu Biểu đồ (7 ngày)
        $posts_by_day = DB::table('posts')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc') 
            ->limit(7)
            ->get();
        
        $chart_labels = $posts_by_day->pluck('date');
        $chart_data = $posts_by_day->pluck('total');

        // Nhật ký hoạt động
        $admin_recent_actions = Schema::hasTable('admin_actions')
            ? DB::table('admin_actions')->orderBy('created_at', 'desc')->limit(5)->get()
            : collect();

        // Top 5
        $top_users = DB::table('users')->orderBy('follower_count', 'desc')->limit(5)->get();
        $top_posts = DB::table('posts')->orderBy('like_count', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'admin_total_users', 'admin_total_posts', 'admin_total_likes', 'admin_banned_users',
            'chart_labels', 'chart_data', 'admin_recent_actions',
            'top_users', 'top_posts'
        ));
    }

    // ===================================================
    // 2. TRANG QUẢN LÝ NGƯỜI DÙNG (USERS)
    // ===================================================
    public function manageUsers(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // Khởi tạo câu truy vấn (chỉ lọc is_deleted nếu cột này tồn tại)
        $query = DB::table('users');
        if (Schema::hasColumn('users', 'is_deleted')) {
            $query->where('is_deleted', 0);
        }

        // 1. Xử lý TÌM KIẾM (Theo tên hoặc email)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // 2. Xử lý LỌC THEO VAI TRÒ
        if ($request->has('role') && $request->role != '' && Schema::hasColumn('users', 'role')) {
            $query->where('role', $request->role);
        }

        // 3. Xử lý LỌC THEO TRẠNG THÁI
        if ($request->has('status') && $request->status != '') {
            $query->where('is_active', $request->status);
        }

        // Thực thi truy vấn và PHÂN TRANG (10 user / trang)
        $admin_users = $query->orderBy('id', 'desc')->paginate(10)->appends($request->all());

        return view('admin.users.index', compact('admin_users'));
    }

    // ===================================================
    // 3. XỬ LÝ KHÓA/MỞ KHÓA TÀI KHOẢN
    // ===================================================
    public function toggleUserStatus($id)
    {
        $this->authorize('manageAdmin', User::class);

        $user = DB::table('users')->where('id', $id)->first();
        if($user) {
            // Đảo ngược trạng thái: Đang 1 thành 0, đang 0 thành 1
            $newStatus = $user->is_active == 1 ? 0 : 1;
            DB::table('users')->where('id', $id)->update(['is_active' => $newStatus]);
            
            // Ghi log
            $actionName = $newStatus == 0 ? 'Khóa' : 'Mở khóa';
            DB::table('admin_actions')->insert([
                'admin_user_id' => $this->currentAdminId(),
                'action_type' => 'Update Status',
                'note' => $actionName . ' tài khoản: ' . $user->display_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect()->back()->with('status', 'Đã ' . $actionName . ' tài khoản thành công!');
        }
        return redirect()->back()->with('error', 'Không tìm thấy người dùng!');
    }

    // ===================================================
    // 4. XỬ LÝ XÓA MỀM TÀI KHOẢN
    // ===================================================
    public function deleteUser($id)
    {
        $this->authorize('manageAdmin', User::class);

        // Xóa mềm: ưu tiên dùng is_deleted nếu có, fallback sang khóa tài khoản.
        if (Schema::hasColumn('users', 'is_deleted')) {
            DB::table('users')->where('id', $id)->update(['is_deleted' => 1]);
        } else {
            DB::table('users')->where('id', $id)->update(['is_active' => 0]);
        }

        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminId(),
            'action_type' => 'Delete User',
            'note' => 'Đã xóa tài khoản ID: ' . $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Đã xóa tài khoản ra khỏi hệ thống!');
    }
    

    // --- CHỨC NĂNG QUẢN LÝ BÀI VIẾT ---

    public function managePosts(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // Join với bảng users để lấy tên tác giả
        $query = DB::table('posts')
            ->join('users', 'posts.author_user_id', '=', 'users.id')
            ->select('posts.*', 'users.display_name as author_name', 'users.email as author_email')
            ->where('posts.is_deleted', 0);

        // 1. Tìm kiếm theo nội dung bài viết hoặc tên tác giả
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('posts.content', 'like', '%' . $search . '%')
                  ->orWhere('users.display_name', 'like', '%' . $search . '%');
            });
        }

        // 2. Lọc theo Chế độ hiển thị
        if ($request->has('visibility') && $request->visibility != '') {
            $query->where('posts.visibility', $request->visibility);
        }

        // 3. Sắp xếp (Mới nhất hoặc Hot nhất)
        $sort = $request->get('sort', 'latest');
        if ($sort == 'hot') {
            $query->orderBy('posts.like_count', 'desc');
        } else {
            $query->orderBy('posts.created_at', 'desc');
        }

        $admin_posts = $query->paginate(10)->appends($request->all());

        return view('admin.posts.index', compact('admin_posts'));
    }

    public function deletePost($id)
    {
        $this->authorize('moderate', Post::class);

        DB::table('posts')->where('id', $id)->update(['is_deleted' => 1]);

        // Ghi lại nhật ký
        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminId(),
            'action_type' => 'Delete Post',
            'note' => 'Đã xóa bài viết ID: #' . $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Đã xóa bài viết thành công!');
    }


    // ===================================================
    // 5. TRANG QUẢN LÝ BÌNH LUẬN (COMMENTS)
    // ===================================================
    public function manageComments(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // Nối 3 bảng: Bình luận + Người dùng + Bài viết
        $query = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('posts', 'comments.post_id', '=', 'posts.id')
            ->select(
                'comments.*', 
                'comments.user_id as author_user_id',
                'users.display_name as author_name', 
                'users.is_active as user_status',
                'posts.content as post_content'
            );

        // Tìm kiếm theo nội dung bình luận hoặc tên người bình luận
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comments.content', 'like', '%' . $search . '%')
                  ->orWhere('users.display_name', 'like', '%' . $search . '%');
            });
        }

        // Sắp xếp mới nhất và phân trang 15 dòng/trang cho gọn
        $admin_comments = $query->orderBy('comments.created_at', 'desc')->paginate(15)->appends($request->all());

        return view('admin.comments.index', compact('admin_comments'));
    }

    // Khóa nhanh người dùng khi thấy comment vi phạm
    public function quickBanUser($userId)
    {
        $this->authorize('manageAdmin', User::class);

        DB::table('users')->where('id', $userId)->update(['is_active' => 0]);
        
        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminId(),
            'action_type' => 'Quick Ban',
            'note' => 'Khóa nhanh User ID #' . $userId . ' từ quản lý bình luận',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Đã khóa tài khoản người dùng vi phạm!');
    }

    public function deleteComment($id)
    {
        $this->authorize('manageAdmin', User::class);

        // Xóa cứng bình luận bay khỏi database luôn (vì đây thường là rác/spam)
        DB::table('comments')->where('id', $id)->delete();

        // Ghi nhật ký
        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminId(),
            'action_type' => 'Delete Comment',
            'note' => 'Đã xóa 1 bình luận vi phạm (ID: #' . $id . ')',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Đã dọn dẹp bình luận thành công!');
    }


    // ===================================================
    // 6. TRANG QUẢN LÝ BÁO CÁO (TÒA ÁN)
    // ===================================================
    public function manageReports(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // 1. Khởi tạo truy vấn: Gộp nhóm theo Đối tượng bị report và Lý do
        $query = DB::table('reports')
            ->select(
                'reported_entity_type',
                'reported_entity_id',
                'reason',
                'status',
                DB::raw('COUNT(id) as total_reports'),
                DB::raw('MAX(created_at) as latest_report_time')
            );

        // Lọc theo Trạng thái (Mặc định là đang chờ xử lý)
        $status = $request->get('status', 'pending');
        if ($status != 'all') {
            $query->where('status', $status);
        }

        // Lọc theo Loại đối tượng (Bài viết, Bình luận, User)
        if ($request->has('type') && $request->type != '') {
            $query->where('reported_entity_type', $request->type);
        }

        // Lọc theo lý do
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        // 2. Sắp xếp: Mới nhất (latest) hoặc Nhiều báo cáo nhất (most)
        $sort = $request->get('sort', 'latest');
        if ($sort == 'most') {
            $query->orderBy('total_reports', 'desc');
        } else {
            $query->orderBy('latest_report_time', 'desc');
        }

        // Phân trang 20 báo cáo/trang
            $admin_reports = $query->groupBy('reported_entity_type', 'reported_entity_id', 'reason', 'status')
                                ->paginate(20)->appends($request->all());

        // 3. Lấy dữ liệu chi tiết (Thumbnail/Avatar/Nội dung full/Danh sách người report)
        foreach ($admin_reports as $report) {
            $report->full_content = '';
            $report->author_id = null;
            $report->deep_link = '#';

            if ($report->reported_entity_type == 'post') {
                $post = DB::table('posts')->where('id', $report->reported_entity_id)->first();
                $report->display_name = $post ? Str::limit($post->content, 40) : 'Bài viết đã xóa';
                $report->full_content = $post ? $post->content : 'Nội dung không còn tồn tại trên hệ thống.';
                $report->thumbnail = $post->image_url ?? null;
                $report->is_video = isset($post->video_url) && $post->video_url != null;
                $report->author_id = $post ? $post->author_user_id : null;
                $report->deep_link = url('/posts/' . $report->reported_entity_id); // Nhảy cóc đến bài viết
            } elseif ($report->reported_entity_type == 'user') {
                $user = DB::table('users')->where('id', $report->reported_entity_id)->first();
                $report->display_name = $user?->display_name ?? 'N/A';
                $report->full_content = 'Trang cá nhân của: ' . ($user?->display_name ?? 'N/A');
                $report->thumbnail = "https://ui-avatars.com/api/?name=" . urlencode($report->display_name) . "&background=4facfe&color=fff";
                $report->author_id = $report->reported_entity_id;
                $report->deep_link = url('/profile/' . $report->reported_entity_id); // Nhảy cóc đến user
            } else {
                $comment = DB::table('comments')->where('id', $report->reported_entity_id)->first();
                $report->display_name = $comment ? Str::limit($comment->content, 40) : 'Bình luận đã xóa';
                $report->full_content = $comment ? $comment->content : 'Nội dung không còn tồn tại.';
                $report->thumbnail = null;
                $report->author_id = $comment ? $comment->user_id : null;
                $report->deep_link = url('/posts/' . ($comment->post_id ?? 0) . '#comment-' . $report->reported_entity_id); // Nhảy cóc đến comment
            }

            // Lấy danh sách những người đã report cái ID này với lý do này
            $report->reporters = DB::table('reports')
                ->leftJoin('users', 'reports.reporter_user_id', '=', 'users.id')
                ->where('reported_entity_type', $report->reported_entity_type)
                ->where('reported_entity_id', $report->reported_entity_id)
                ->where('reason', $report->reason)
                ->where('reports.status', 'pending')
                ->select('users.display_name', 'reports.additional_notes', 'reports.created_at')
                ->get();
        }
        
        return view('admin.reports.index', compact('admin_reports'));
    }

    // ===================================================
    // 6. XỬ LÝ BÁO CÁO (PROCESS REPORT)
    // ===================================================
    public function processReport(Request $request)
    {
        $entityType = $request->entity_type;
        $entityId = $request->entity_id;
        $reason = $request->reason;
        $authorId = $request->author_id;
        $action = $request->action;

        // Validate input
        if (!$entityType || !$entityId || !$action) {
            return back()->with('error', 'Dữ liệu không hợp lệ.');
        }

        // Update all reports for this entity with this reason to resolved or dismissed
        if ($action == 'dismiss') {
            DB::table('reports')
                ->where('reported_entity_type', $entityType)
                ->where('reported_entity_id', $entityId)
                ->where('reason', $reason)
                ->update(['status' => 'dismissed', 'updated_at' => now()]);
        } else {
            DB::table('reports')
                ->where('reported_entity_type', $entityType)
                ->where('reported_entity_id', $entityId)
                ->where('reason', $reason)
                ->update(['status' => 'resolved', 'updated_at' => now()]);
        }

        // Perform action based on type
        if ($action == 'hide') {
            if ($entityType == 'post') {
                $payload = [
                    'content' => 'Nội dung này đã bị ẩn vì vi phạm quy định cộng đồng.',
                ];

                if (Schema::hasColumn('posts', 'image_url')) {
                    $payload['image_url'] = null;
                }

                if (Schema::hasColumn('posts', 'video_url')) {
                    $payload['video_url'] = null;
                }

                DB::table('posts')->where('id', $entityId)->update($payload);
            } elseif ($entityType == 'comment') {
                DB::table('comments')->where('id', $entityId)->update([
                    'content' => 'Bình luận này đã bị ẩn vì vi phạm quy định cộng đồng.'
                ]);
            }
        } elseif ($action == 'delete') {
            if ($entityType == 'post') {
                DB::table('posts')->where('id', $entityId)->delete();
            } elseif ($entityType == 'comment') {
                DB::table('comments')->where('id', $entityId)->delete();
            } elseif ($entityType == 'user') {
                // Soft delete user?
                DB::table('users')->where('id', $entityId)->update(['is_active' => 0]);
            }
        } elseif ($action == 'ban') {
            if ($authorId) {
                // Ban user for 24 hours - set is_active = 0 (need job to re-enable after 24h)
                DB::table('users')->where('id', $authorId)->update([
                    'is_active' => 0
                ]);
            }
        }

        return back()->with('success', 'Đã xử lý báo cáo thành công.');
    }
}
