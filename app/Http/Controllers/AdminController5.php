<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class AdminController5 extends Controller
{
    public function backHome()
    {
        return redirect()->route('home');
    }

    
    private function postUserColumn(): string
    {
        return Schema::hasColumn('posts', 'user_id') ? 'posts.user_id' : 'posts.user_id';
    }

    private function currentAdminUserId(): ?int
    {
        $adminId = Auth::id();

        return $adminId ? (int) $adminId : null;
    }

    private function storeAdminAction(string $actionType, string $note): void
    {
        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminUserId(),
            'action_type' => $actionType,
            'note' => $note,
            'created_at' => now(),
        ]);
    }


    // ===================================================
    // 1. TRANG BẢNG ĐIỀU KHIỂN (DASHBOARD)
    // ===================================================
    public function dashboard()
    {
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
        $admin_recent_actions = DB::table('admin_actions')->orderBy('created_at', 'desc')->limit(5)->get();


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
        // Khởi tạo câu truy vấn (Chỉ lấy những user chưa bị xóa mềm)
        $query = DB::table('users')->where('is_deleted', 0);


        // 1. Xử lý TÌM KIẾM (Theo tên hoặc email)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }


        // 2. Xử lý LỌC THEO VAI TRÒ
        if ($request->has('role') && $request->role != '') {
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

        $admin_users = $query->paginate(10)->appends($request->all());

            foreach ($admin_users as $user) {
            // 1. Đếm lại số bài viết thực tế (chưa bị xóa)
            $user->post_count = DB::table('posts')
                ->where('user_id', $user->id)
                ->where('is_deleted', 0)
                ->count();

            // 2. Lấy 3 vi phạm gần nhất
            $user->recent_violations = DB::table('reports')
                ->where('reported_entity_type', 'user')
                ->where('reported_entity_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
                
            // 3. Đếm TỔNG SỐ vi phạm
            $user->total_violations = DB::table('reports')
                ->where('reported_entity_type', 'user')
                ->where('reported_entity_id', $user->id)
                ->count();
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
            'password' => Hash::make($request->temp_password),      // <--- THÊM DÒNG NÀY ĐỂ FIX LỖI DB
            'password_hash' => Hash::make($request->temp_password), // Vẫn giữ phòng trường hợp cậu cần
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
            $this->storeAdminAction('Update Status', $actionName . ' tài khoản #' . $id . ' (' . ($user->display_name ?? 'N/A') . ')');
           
            return redirect()->back()->with('status', 'Đã ' . $actionName . ' tài khoản thành công!');
        }
        return redirect()->back()->with('error', 'Không tìm thấy người dùng!');
    }


    public function togglePostVisibility($id)
    {
        $post = DB::table('posts')->where('id', $id)->first();
        if (! $post) {
            return redirect()->back()->with('error', 'Không tìm thấy bài viết!');
        }


        $currentStatus = $post->status ?? 'visible';
        $newStatus = $currentStatus === 'hidden' ? 'visible' : 'hidden';


        DB::table('posts')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);


        $actionName = $newStatus === 'hidden' ? 'Ẩn' : 'Hiện';
        $this->storeAdminAction('Toggle Post Visibility', $actionName . ' bài viết #' . $id);


        return redirect()->back()->with('status', 'Đã ' . strtolower($actionName) . ' bài viết thành công!');
    }

    // ===================================================
    // 4. XỬ LÝ XÓA MỀM TÀI KHOẢN
    // ===================================================
    public function deleteUser($id)
    {
        // Xóa mềm: Chuyển is_deleted = 1 thay vì xóa vĩnh viễn
        DB::table('users')->where('id', $id)->update(['is_deleted' => 1]);


        DB::table('admin_actions')->insert([
            'admin_user_id' => $this->currentAdminUserId(),
            'action_type' => 'Delete User',
            'note' => 'Đã xóa tài khoản ID: ' . $id,
            'created_at' => now()
        ]);


        return redirect()->back()->with('status', 'Đã xóa tài khoản ra khỏi hệ thống!');
    }
   


    // ===================================================
    // 4. TRANG QUẢN LÝ BÀI VIẾT
    // ===================================================


    public function managePosts(Request $request)
    {
        $postUserColumn = $this->postUserColumn();

        // Xóa lệnh lọc status != hidden đi để quản lý được cả bài đã ẩn
        $query = DB::table('posts')
            ->join('users', $postUserColumn, '=', 'users.id')
            ->select(
                'posts.*',
                DB::raw($postUserColumn . ' as user_id'),
                'users.display_name as author_name',
                'users.email as author_email',
                'users.created_at as author_created_at',
                'users.is_active as author_status'
            )
            ->where('posts.is_deleted', 0);

        // 1. TÌM KIẾM
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('posts.content', 'like', '%' . $search . '%')
                  ->orWhere('users.display_name', 'like', '%' . $search . '%');
            });
        }

        // 2. LỌC CHẾ ĐỘ HIỂN THỊ (Public / Private)
        if ($request->has('visibility') && $request->visibility != '') {
            $query->where('posts.visibility', $request->visibility);
        }
        
        // 3. LỌC TRẠNG THÁI (Mới thêm: Visible / Hidden)
        if ($request->has('content_status') && $request->content_status != '') {
            if ($request->content_status === 'visible') {
                $query->where(function($q) {
                    $q->whereNull('posts.status')->orWhere('posts.status', 'visible');
                });
            } else {
                $query->where('posts.status', 'hidden');
            }
        }

        // 4. SẮP XẾP
        $sort = $request->get('sort', 'latest');
        if ($sort == 'hot') {
            $query->orderBy('posts.like_count', 'desc');
        } else {
            $query->orderBy('posts.created_at', 'desc');
        }

        $admin_posts = $query->paginate(10)->appends($request->all());

        // CHỖ NÀY PHẢI CÓ FOREACH ĐỂ NÓ DUYỆT TỪNG BÀI VIẾT NÈ
        foreach ($admin_posts as $post) {
            // Lấy media thông qua bảng trung gian post_media
            $post->media_type = null;
            $post->media_url = null;
            
            $media = DB::table('media')
                ->join('post_media', 'media.id', '=', 'post_media.media_id')
                ->where('post_media.post_id', $post->id)
                ->select('media.*')
                ->first(); // Lấy tạm 1 cái ảnh/video đầu tiên để làm hình thu nhỏ (preview)
            
            if ($media) {
                $post->media_type = $media->type ?? 'image';
                $post->media_url = $media->url ?? null;
            }

            // Lấy danh sách Report giống trang User (Đã join với users để lấy tên người báo cáo)
            $post->report_entries = DB::table('reports')
                ->leftJoin('users as reporters', 'reports.reporter_user_id', '=', 'reporters.id')
                ->where('reports.reported_entity_type', 'post')
                ->where('reports.reported_entity_id', $post->id)
                ->select(
                    'reports.*',
                    'reporters.display_name as reporter_name' // Lấy tên người báo cáo
                )
                ->orderBy('reports.created_at', 'desc')
                ->get();
            
            // Đếm report chưa xử lý
            $post->open_report_count = $post->report_entries->where('status', 'pending')->count();
            
            // Đếm TỔNG số report (Để hiện nút Xem tất cả)
            $post->total_violations = $post->report_entries->count();
        } // ĐỪNG QUÊN DẤU ĐÓNG NGOẶC NÀY CỦA VÒNG LẶP NHÉ

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
        DB::table('posts')->where('id', $id)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);


        $this->storeAdminAction('Delete Post', 'Đã xóa bài viết ID: #' . $id);


        return redirect()->back()->with('status', 'Đã xóa bài viết thành công!');
    }




    // ===================================================
    // QUẢN LÝ BÌNH LUẬN (COMMENTS)
    // ===================================================

    public function manageComments(Request $request)
    {
        // Join bảng users (lấy tác giả comment), join bảng posts (để lấy nội dung bài gốc và tìm kiếm)
        $query = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('posts', 'comments.post_id', '=', 'posts.id')
            ->select(
                'comments.*',
                'users.id as commenter_user_id',
                'users.display_name as author_name',
                'users.is_active as user_status',
                'users.created_at as author_created_at',
                'posts.content as post_content'
            )
            // Đếm số Like của comment bằng subquery để dễ sort
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM comment_likes WHERE comment_likes.comment_id = comments.id) as like_count'))
            ->where('comments.is_deleted', 0); // Chỉ hiển thị bình luận chưa xóa mềm

        // 1. TÌM KIẾM (Nội dung comment, nội dung post gốc, hoặc tên người bình luận)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comments.content', 'like', '%' . $search . '%')
                  ->orWhere('posts.content', 'like', '%' . $search . '%')
                  ->orWhere('users.display_name', 'like', '%' . $search . '%');
            });
        }

        // 2. LỌC TRẠNG THÁI ẨN/HIỆN
        if ($request->has('content_status') && $request->content_status != '') {
            if ($request->content_status === 'visible') {
                $query->where(function($q) {
                    $q->whereNull('comments.status')->orWhere('comments.status', 'visible');
                });
            } else {
                $query->where('comments.status', 'hidden');
            }
        }

        // 3. SẮP XẾP
        $sort = $request->get('sort', 'latest');
        if ($sort == 'hot') {
            $query->orderBy('like_count', 'desc'); // Sắp xếp theo lượng Like nhiều nhất
        } else {
            $query->orderBy('comments.created_at', 'desc');
        }

        $admin_comments = $query->paginate(10)->appends($request->all());

        foreach ($admin_comments as $comment) {
            // Lấy media của bài viết gốc (để hiển thị trong Modal Xem xét)
            $comment->post_media_type = null;
            $comment->post_media_url = null;
            $media = DB::table('media')
                ->join('post_media', 'media.id', '=', 'post_media.media_id')
                ->where('post_media.post_id', $comment->post_id)
                ->select('media.*')
                ->first();
            if ($media) {
                $comment->post_media_type = $media->type ?? 'image';
                $comment->post_media_url = $media->url ?? null;
            }

            // LẤY DANH SÁCH BÁO CÁO CỦA BÌNH LUẬN (Chỉ lấy báo cáo thuộc về comment)
            $comment->report_entries = DB::table('reports')
                ->leftJoin('users as reporters', 'reports.reporter_user_id', '=', 'reporters.id')
                ->where('reports.reported_entity_type', 'comment')
                ->where('reports.reported_entity_id', $comment->id)
                ->select('reports.*', 'reporters.display_name as reporter_name')
                ->orderBy('reports.created_at', 'desc')
                ->get();
            
            // Đếm báo cáo
            $comment->open_report_count = $comment->report_entries->where('status', 'pending')->count();
            $comment->total_violations = $comment->report_entries->count();
        }

        return view('admin.comments.index', compact('admin_comments'));
    }


    public function toggleCommentVisibility($id)
    {
        $comment = DB::table('comments')->where('id', $id)->first();
        if (! $comment) return redirect()->back()->with('error', 'Không tìm thấy bình luận!');

        $currentStatus = $comment->status ?? 'visible';
        $newStatus = $currentStatus === 'hidden' ? 'visible' : 'hidden';

        DB::table('comments')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        // Tự động chuyển báo cáo thành "Đã xử lý" nếu ẩn bình luận
        if ($newStatus === 'hidden') {
            DB::table('reports')
                ->where('reported_entity_type', 'comment')
                ->where('reported_entity_id', $id)
                ->where('status', 'pending')
                ->update(['status' => 'resolved', 'updated_at' => now()]);
        }

        $actionName = $newStatus === 'hidden' ? 'Ẩn' : 'Hiện';
        $this->storeAdminAction('Toggle Comment', $actionName . ' bình luận #' . $id);

        return redirect()->back()->with('status', 'Đã ' . mb_strtolower($actionName) . ' bình luận thành công!');
    }


    public function deleteComment($id)
    {
        // Chỉ xóa mềm (is_deleted = 1)
        DB::table('comments')->where('id', $id)->update([
            'is_deleted' => 1,
            'updated_at' => now(),
        ]);

        // Xóa bình luận thì cũng chốt luôn đơn báo cáo
        DB::table('reports')
            ->where('reported_entity_type', 'comment')
            ->where('reported_entity_id', $id)
            ->where('status', 'pending')
            ->update(['status' => 'resolved', 'updated_at' => now()]);

        $this->storeAdminAction('Delete Comment', 'Xóa mềm bình luận ID: #' . $id);

        return redirect()->back()->with('status', 'Đã xóa mềm bình luận thành công!');
    }


    // ===================================================
    // 6. TRANG QUẢN LÝ BÁO CÁO
    // ===================================================
    public function manageReports(Request $request)
    {
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

        if ($search !== '') $query->where('reports.reason', 'like', '%' . $search . '%');

        $status = $request->get('status', 'pending');
        if ($status != 'all') $query->where('reports.status', $status);

        if ($request->has('type') && $request->type != '') $query->where('reports.reported_entity_type', $request->type);
        if ($request->filled('reason')) $query->where('reports.reason', $request->reason);

        $sort = $request->get('sort', 'latest');
        if ($sort == 'most') $query->orderBy('total_reports', 'desc');
        else $query->orderBy('latest_report_time', 'desc');

        $admin_reports = $query->groupBy('reports.id', 'reports.reported_entity_type', 'reports.reported_entity_id', 'reports.reason', 'reports.status')
                               ->paginate(20)->appends($request->all());

        foreach ($admin_reports as $report) {
            $report->full_content = '';
            $report->author_id = null;
            $report->author_name = 'N/A';
            $report->author_status = 1; 
            $report->content_status = 'visible'; 
            $report->deep_link = '#';

            $report->post_id = null;
            $report->post_content = null;
            $report->post_thumbnail = null; // Khởi tạo biến ảnh cho post gốc của comment
            $report->post_is_video = false;

            if ($report->reported_entity_type == 'post') {
                $post = DB::table('posts')->where('id', $report->reported_entity_id)->first();
                $report->display_name = $post ? Str::limit($post->content, 40) : 'Bài viết đã xóa';
                $report->full_content = $post ? $post->content : 'Nội dung không còn tồn tại trên hệ thống.';
                $report->thumbnail = null;
                $report->is_video = false;
                $report->content_status = $post->status ?? 'visible';

                if ($post) {
                    // FIX: Lấy media qua bảng trung gian post_media
                    $media = DB::table('media')
                        ->join('post_media', 'media.id', '=', 'post_media.media_id')
                        ->where('post_media.post_id', $post->id)
                        ->select('media.*')
                        ->first();
                        
                    if ($media) {
                        $report->thumbnail = $media->url ?? null;
                        $report->is_video = ($media->type ?? '') === 'video';
                    }
                }

                $report->author_id = $post ? ($post->user_id ?? null) : null;
                if ($post) $report->deep_link = route('post.show', ['id' => $report->reported_entity_id]);
                
            } 
            elseif ($report->reported_entity_type == 'user') {
                $user = DB::table('users')->where('id', $report->reported_entity_id)->first();
                $report->display_name = $user->display_name ?? 'N/A';
                $report->full_content = 'Trang cá nhân của: ' . ($user->display_name ?? 'N/A');
                $report->thumbnail = "https://ui-avatars.com/api/?name=" . urlencode($report->display_name) . "&background=4facfe&color=fff";
                $report->author_id = $report->reported_entity_id;
                $report->deep_link = url('/profile/' . $report->reported_entity_id);
            } 
            else { // COMMENT
                $comment = DB::table('comments')->where('id', $report->reported_entity_id)->first();
                $report->display_name = $comment ? Str::limit($comment->content, 40) : 'Bình luận đã xóa';
                $report->full_content = $comment ? $comment->content : 'Nội dung không còn tồn tại.';
                $report->thumbnail = null;
                $report->content_status = $comment->status ?? 'visible';
                
                if ($comment) {
                    $report->author_id = $comment->user_id ?? null;
                    $report->deep_link = route('post.show', ['id' => $comment->post_id, 'focus_comment' => $report->reported_entity_id]);
                    
                    $report->post_id = $comment->post_id;
                    $parentPost = DB::table('posts')->where('id', $comment->post_id)->first();
                    
                    if ($parentPost) {
                        $report->post_content = $parentPost->content;
                        
                        // FIX: Lấy media qua bảng trung gian post_media
                        $media = DB::table('media')
                            ->join('post_media', 'media.id', '=', 'post_media.media_id')
                            ->where('post_media.post_id', $parentPost->id)
                            ->select('media.*')
                            ->first();
                            
                        if ($media) {
                            $report->post_thumbnail = $media->url ?? null;
                            $report->post_is_video = ($media->type ?? '') === 'video';
                        }
                    }
                }
            }

            if ($report->author_id) {
                $author = DB::table('users')->where('id', $report->author_id)->first();
                if ($author) {
                    $report->author_name = $author->display_name;
                    $report->author_status = $author->is_active;
                }
            }

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


    public function processReport(Request $request)
    {
        $entityType = $request->entity_type;
        $entityId = $request->entity_id;
        $reason = $request->reason;
        $authorId = $request->author_id;
        $action = $request->action;

        if (!$entityType || !$entityId || !$action) return back()->with('error', 'Dữ liệu không hợp lệ.');

        // 1. Cập nhật trạng thái đơn (Bác bỏ hoặc Xử lý)
        if ($action == 'dismiss') {
            DB::table('reports')->where('reported_entity_type', $entityType)->where('reported_entity_id', $entityId)->where('reason', $reason)
                ->update(['status' => 'dismissed', 'updated_at' => now()]);
            $actionLog = 'Bác bỏ đơn tố cáo';
        } else {
            DB::table('reports')->where('reported_entity_type', $entityType)->where('reported_entity_id', $entityId)->where('reason', $reason)
                ->update(['status' => 'resolved', 'updated_at' => now()]);
            $actionLog = 'Xử lý đơn tố cáo';
        }

        // 2. Thao tác lên nội dung (Ẩn/Hiện, Khóa/Mở, Xóa)
        if ($action == 'toggle_hide' && in_array($entityType, ['post', 'comment'])) {
            $table = $entityType == 'post' ? 'posts' : 'comments';
            $entity = DB::table($table)->where('id', $entityId)->first();
            if ($entity) {
                $newStatus = ($entity->status ?? 'visible') === 'hidden' ? 'visible' : 'hidden';
                DB::table($table)->where('id', $entityId)->update(['status' => $newStatus, 'updated_at' => now()]);
                $actionLog = $newStatus === 'hidden' ? 'Ẩn nội dung' : 'Hiện nội dung';
            }
        } 
        elseif ($action == 'delete' && in_array($entityType, ['post', 'comment'])) {
            $table = $entityType == 'post' ? 'posts' : 'comments';
            DB::table($table)->where('id', $entityId)->update(['is_deleted' => 1, 'updated_at' => now()]);
            $actionLog = 'Xóa mềm nội dung vi phạm';
        } 
        elseif ($action == 'toggle_ban' && $authorId) {
            $user = DB::table('users')->where('id', $authorId)->first();
            if ($user) {
                $newStatus = $user->is_active == 1 ? 0 : 1;
                DB::table('users')->where('id', $authorId)->update(['is_active' => $newStatus, 'updated_at' => now()]);
                $actionLog = $newStatus == 0 ? 'Khóa tài khoản' : 'Mở khóa tài khoản';
            }
        }

        $this->storeAdminAction('Process Report', $actionLog . ' | Đối tượng: ' . strtoupper($entityType) . ' #' . $entityId . ' | Lý do: ' . $reason);

        return back()->with('status', 'Đã thực hiện thao tác: ' . $actionLog);
    }

    // ===================================================
    // HIỂN THỊ BÀI VIẾT GỐC NGOÀI TRANG CHỦ
    // ===================================================
    public function showPost(Request $request, $id)
    {
        $post = DB::table('posts')
            ->join('users', $this->postUserColumn(), '=', 'users.id')
            ->select('posts.*', DB::raw($this->postUserColumn() . ' as user_id'), 'users.display_name as author_name', 'users.username as author_username')
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
}



