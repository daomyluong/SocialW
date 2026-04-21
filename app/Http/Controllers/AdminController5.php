<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
<<<<<<< HEAD
use Illuminate\Support\Facades\Schema;
=======
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
use Illuminate\Support\Str;

class AdminController5 extends Controller
{
<<<<<<< HEAD
    private function currentAdminId(): ?int
    {
        return Auth::id() ? (int) Auth::id() : null;
=======
    private function storeAdminAction(string $actionType, string $note): void
    {
        DB::table('admin_actions')->insert([
            'admin_user_id' => 1,
            'action_type' => $actionType,
            'note' => $note,
            'created_at' => now(),
        ]);
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
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

        // 4. Sắp xếp
        $sort = $request->get('sort', 'latest');
        if ($sort === 'followers') {
            $query->orderBy('follower_count', 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        // Thực thi truy vấn và PHÂN TRANG (10 user / trang)
        $admin_users = $query->paginate(10)->appends($request->all());

        // Giữ thuộc tính để tương thích giao diện modal
        foreach ($admin_users as $user) {
            $user->recent_violations = collect();
        }

        return view('admin.users.index', compact('admin_users'));
    }

    public function updateUserRole(Request $request, $id)
    {
        $newRole = $request->get('role');
        if (!in_array($newRole, ['admin', 'user'], true)) {
            return redirect()->back()->with('error', 'Vai trò không hợp lệ.');
        }

        $targetUser = DB::table('users')->where('id', $id)->first();
        if (! $targetUser) {
            return redirect()->back()->with('error', 'Không tìm thấy người dùng!');
        }

        $oldRole = $targetUser->role ?? 'user';
        DB::table('users')->where('id', $id)->update([
            'role' => $newRole,
            'updated_at' => now(),
        ]);

        $this->storeAdminAction(
            'Update Role',
            'Đổi vai trò tài khoản #' . $id . ' từ ' . $oldRole . ' sang ' . $newRole
        );

        return redirect()->back()->with('status', 'Đã cập nhật vai trò thành công.');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'temp_password' => ['required', 'string', 'min:6', 'max:72'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $baseUsername = Str::slug(Str::before($request->email, '@'), '_');
        $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
        $username = $baseUsername;
        $counter = 1;

        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $counter;
            $counter++;
        }

        $newUserId = DB::table('users')->insertGetId([
            'name' => $request->display_name,
            'display_name' => $request->display_name,
            'email' => $request->email,
            'password' => Hash::make($request->temp_password),
            'username' => $username,
            'role' => $request->role,
            'is_active' => 1,
            'is_deleted' => 0,
            'post_count' => 0,
            'follower_count' => 0,
            'following_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->storeAdminAction(
            'Create User',
            'Tạo tài khoản mới #' . $newUserId . ' (' . $request->email . ') với vai trò ' . $request->role
        );

        return redirect()->back()->with('status', 'Đã tạo tài khoản mới thành công.');
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

            $actingAdminId = Auth::id();
            if ($actingAdminId && (int) $actingAdminId === (int) $id && $newStatus === 0) {
                return redirect()->back()->with('error', 'Bạn không thể tự khóa tài khoản quản trị của chính mình.');
            }

            DB::table('users')->where('id', $id)->update(['is_active' => $newStatus]);
            
            // Ghi log
            $actionName = $newStatus == 0 ? 'Khóa' : 'Mở khóa';
<<<<<<< HEAD
            DB::table('admin_actions')->insert([
                'admin_user_id' => $this->currentAdminId(),
                'action_type' => 'Update Status',
                'note' => $actionName . ' tài khoản: ' . $user->display_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
=======
            $this->storeAdminAction('Update Status', $actionName . ' tài khoản #' . $id . ' (' . ($user->display_name ?? 'N/A') . ')');
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
            
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
    

    // ===================================================
    // 4. TRANG QUẢN LÝ BÀI VIẾT
    // ===================================================

    public function managePosts(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // Join với bảng users để lấy tên tác giả
        $query = DB::table('posts')
            ->join('users', 'posts.author_user_id', '=', 'users.id')
            ->select(
                'posts.*',
                'users.display_name as author_name',
                'users.email as author_email',
                'users.created_at as author_created_at'
            )
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

        foreach ($admin_posts as $post) {
            $post->media_type = null;
            $post->media_url = null;
            if (! empty($post->media_id)) {
                $media = DB::table('media')->where('id', $post->media_id)->first();
                if ($media) {
                    $post->media_type = $media->type ?? null;
                    $post->media_url = $media->url ?? null;
                }
            }

            $post->previous_violation_count = 0;
            $post->report_entries = collect();
            $post->open_report_count = 0;
        }

        return view('admin.posts.index', compact('admin_posts'));
    }

    public function moderatePost(Request $request, $id)
    {
        $action = $request->get('action');
        if (! in_array($action, ['hide', 'delete'], true)) {
            return redirect()->back()->with('error', 'Hành động không hợp lệ.');
        }

        $post = DB::table('posts')->where('id', $id)->first();
        if (! $post) {
            return redirect()->back()->with('error', 'Không tìm thấy bài viết!');
        }

        if ($action === 'hide') {
            DB::table('posts')->where('id', $id)->update([
                'content' => 'Nội dung này đã bị ẩn do vi phạm tiêu chuẩn cộng đồng.',
                'updated_at' => now(),
            ]);

            DB::table('reports')
                ->where('reported_entity_type', 'post')
                ->where('reported_entity_id', $id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'resolved',
                    'updated_at' => now(),
                ]);

            $this->storeAdminAction('Hide Post', 'Ẩn/Thay thế nội dung bài viết #' . $id . ' sau khi xem xét');

            return redirect()->back()->with('status', 'Đã ẩn/thay thế nội dung bài viết.');
        }

        DB::table('posts')->where('id', $id)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);

        DB::table('reports')
            ->where('reported_entity_type', 'post')
            ->where('reported_entity_id', $id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'updated_at' => now(),
            ]);

        $this->storeAdminAction('Delete Post', 'Xóa mềm bài viết #' . $id . ' từ modal xem xét');

        return redirect()->back()->with('status', 'Đã xóa mềm bài viết thành công.');
    }

    public function deletePost($id)
    {
<<<<<<< HEAD
        $this->authorize('moderate', Post::class);

        DB::table('posts')->where('id', $id)->update(['is_deleted' => 1]);

        // Ghi lại nhật ký
        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminId(),
            'action_type' => 'Delete Post',
            'note' => 'Đã xóa bài viết ID: #' . $id,
            'created_at' => now(),
=======
        DB::table('posts')->where('id', $id)->update([
            'is_deleted' => 1,
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
            'updated_at' => now(),
        ]);

        $this->storeAdminAction('Delete Post', 'Đã xóa bài viết ID: #' . $id);

        return redirect()->back()->with('status', 'Đã xóa bài viết thành công!');
    }


    // ===================================================
    // 5. TRANG QUẢN LÝ BÌNH LUẬN 
    // ===================================================
    public function manageComments(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // Nối 3 bảng: Bình luận + Người dùng + Bài viết
        $query = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('posts', 'comments.post_id', '=', 'posts.id')
            ->select(
<<<<<<< HEAD
                'comments.*', 
                'comments.user_id as author_user_id',
                'users.display_name as author_name', 
                'users.is_active as user_status',
                'posts.content as post_content'
=======
                'comments.*',
                'users.display_name as author_name',
                'users.is_active as user_status',
                'users.created_at as author_created_at',
                'posts.content as post_content',
                'posts.author_user_id as post_author_user_id',
                'comments.author_user_id as commenter_user_id'
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
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

        foreach ($admin_comments as $comment) {
            $comment->previous_violation_count = 0;
            $comment->report_entries = collect();
            $comment->thread_comments = collect([
                (object) [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'author_name' => $comment->author_name,
                ],
            ]);
        }

        return view('admin.comments.index', compact('admin_comments'));
    }

    // Khóa nhanh người dùng khi thấy comment vi phạm
    public function quickBanUser($userId)
    {
<<<<<<< HEAD
        $this->authorize('manageAdmin', User::class);

        DB::table('users')->where('id', $userId)->update(['is_active' => 0]);
        
        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminId(),
            'action_type' => 'Quick Ban',
            'note' => 'Khóa nhanh User ID #' . $userId . ' từ quản lý bình luận',
            'created_at' => now(),
=======
        DB::table('users')->where('id', $userId)->update([
            'is_active' => 0,
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
            'updated_at' => now(),
        ]);

        $this->storeAdminAction('Quick Ban', 'Khóa nhanh User ID #' . $userId . ' trong 24 giờ từ quản lý bình luận');

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
    // 6. TRANG QUẢN LÝ BÁO CÁO 
    // ===================================================
    public function manageReports(Request $request)
    {
        $this->authorize('manageAdmin', User::class);

        // 1. Khởi tạo truy vấn: Gộp nhóm theo Đối tượng bị report và Lý do
        $search = trim((string) $request->get('search', ''));

        $query = DB::table('reports as reports')
            ->select(
                'reports.reported_entity_type',
                'reports.reported_entity_id',
                'reports.reason',
                'reports.status',
                DB::raw('COUNT(reports.id) as total_reports'),
                DB::raw('MAX(reports.created_at) as latest_report_time')
            );

        if ($search !== '') {
            $query->where('reports.reason', 'like', '%' . $search . '%');
        }

        // Lọc theo Trạng thái (Mặc định là đang chờ xử lý)
        $status = $request->get('status', 'pending');
        if ($status != 'all') {
            $query->where('reports.status', $status);
        }

        // Lọc theo Loại đối tượng (Bài viết, Bình luận, User)
        if ($request->has('type') && $request->type != '') {
            $query->where('reports.reported_entity_type', $request->type);
        }

        // Lọc theo lý do
        if ($request->filled('reason')) {
            $query->where('reports.reason', $request->reason);
        }

        // 2. Sắp xếp: Mới nhất (latest) hoặc Nhiều báo cáo nhất (most)
        $sort = $request->get('sort', 'latest');
        if ($sort == 'most') {
            $query->orderBy('total_reports', 'desc');
        } else {
            $query->orderBy('latest_report_time', 'desc');
        }

        // Phân trang 20 báo cáo/trang
            $admin_reports = $query->groupBy('reports.reported_entity_type', 'reports.reported_entity_id', 'reports.reason', 'reports.status')
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
                $report->thumbnail = null;
                $report->is_video = false;

                if ($post && ! empty($post->media_id)) {
                    $media = DB::table('media')->where('id', $post->media_id)->first();
                    if ($media) {
                        $report->thumbnail = ($media->type ?? '') === 'image' ? ($media->url ?? null) : null;
                        $report->is_video = ($media->type ?? '') === 'video';
                    }
                }

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
<<<<<<< HEAD
                $report->author_id = $comment ? $comment->user_id : null;
                $report->deep_link = url('/posts/' . ($comment->post_id ?? 0) . '#comment-' . $report->reported_entity_id); // Nhảy cóc đến comment
=======
                $report->author_id = null;
                if ($comment) {
                    $report->author_id = $comment->user_id ?? $comment->author_user_id ?? null;
                }
                $report->deep_link = $comment
                    ? url('/post/' . $comment->post_id . '?focus_comment=' . $report->reported_entity_id . '#comment-' . $report->reported_entity_id)
                    : '#'; // Nhảy cóc đến comment
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
            }

            // Lấy danh sách những người đã report cái ID này với lý do này
            $report->reporters = DB::table('reports')
                ->leftJoin('users', 'reports.reporter_user_id', '=', 'users.id')
                ->where('reported_entity_type', $report->reported_entity_type)
                ->where('reported_entity_id', $report->reported_entity_id)
                ->where('reason', $report->reason)
                ->where('reports.status', $report->status)
                ->select('users.display_name', 'reports.additional_notes', 'reports.created_at')
                ->orderBy('reports.created_at', 'desc')
                ->get();
        }
        
        return view('admin.reports.index', compact('admin_reports'));
    }

    public function showPost(Request $request, $id)
    {
        $post = DB::table('posts')
            ->join('users', 'posts.author_user_id', '=', 'users.id')
            ->select('posts.*', 'users.display_name as author_name', 'users.username as author_username')
            ->where('posts.id', $id)
            ->where('posts.is_deleted', 0)
            ->first();

        if (! $post) {
            return redirect()->route('home')->with('error', 'Bài viết không còn tồn tại.');
        }

        $focusCommentId = $request->integer('focus_comment');

        $comments = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select(
                'comments.*',
                'users.display_name as author_name',
                'users.username as author_username',
                'users.is_active as author_is_active'
            )
            ->where('comments.post_id', $id)
            ->where('comments.is_deleted', 0)
            ->orderByRaw('CASE WHEN comments.id = ? THEN 0 ELSE 1 END', [$focusCommentId ?: 0])
            ->orderBy('comments.created_at', 'desc')
            ->get();

        return view('post', compact('post', 'comments', 'focusCommentId'));
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
<<<<<<< HEAD
                ];

                if (Schema::hasColumn('posts', 'image_url')) {
                    $payload['image_url'] = null;
                }

                if (Schema::hasColumn('posts', 'video_url')) {
                    $payload['video_url'] = null;
                }

                DB::table('posts')->where('id', $entityId)->update($payload);
=======
                    'updated_at' => now(),
                ]);
>>>>>>> f08ac2c4ffce977e93c6bcc25f2e5ed81b7c9cb2
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
                DB::table('users')->where('id', $authorId)->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            }
        }

        $this->storeAdminAction(
            'Process Report',
            'Đã ' . ($action == 'dismiss' ? 'bác bỏ' : ($action == 'hide' ? 'ẩn nội dung' : ($action == 'delete' ? 'xóa nội dung' : 'khóa tài khoản 24h'))) .
            ' đối tượng ' . $entityType . ' #' . $entityId . ' với lý do: ' . $reason
        );

        return back()->with('success', 'Đã xử lý báo cáo thành công.');
    }
}
