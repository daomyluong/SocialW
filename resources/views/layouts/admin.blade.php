<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin W-Social | @yield('title', 'Quản trị')</title>
    {{-- Tận dụng các thư viện Leader đã nhúng --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Giữ nguyên biến màu sắc của Leader */
        :root {
            --hlink-blue: #0062ff; 
            --hlink-green: #28a745;
            --hlink-bg: #f8fafc;
            --threads-gray: #f1f3f5;
        }

        body, html {
            height: 100%; overflow: hidden;
            background-color: var(--hlink-bg); color: #212529;
        }

        /* Sidebar giữ nguyên cấu trúc nhưng đổi icon và link */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 250px;
            border-right: 1px solid #e2e8f0; padding: 20px; background: #fff;
            z-index: 1000; overflow-y: auto;
        }

        .logo h3 {
            background: linear-gradient(45deg, var(--hlink-blue), var(--hlink-green));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .sidebar .nav-link {
            padding: 12px 15px; border-radius: 10px; transition: 0.3s;
            margin-bottom: 5px; color: #4a5568 !important;
        }

        .sidebar .nav-link:hover, .sidebar .active {
            background: #eef6ff; color: var(--hlink-blue) !important; font-weight: bold;
        }

        /* Top banner giữ nguyên style */
        .top-banner {
            margin-left: 250px; height: 70px; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 30px; position: sticky; top: 0;
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); z-index: 999;
        }

        /* Main content: Bỏ chia cột 8-4, để 100% cho bảng dữ liệu */
        .main-content { margin-left: 250px; height: calc(100vh - 70px); }
        .scrollable-column { height: 100%; overflow-y: auto; overflow-x: hidden; padding: 30px; }

        /* CSS Dùng chung cho toàn bộ trang Admin */
.card-glass { border-radius: 1.25rem; border: none; background: rgba(255, 255, 255, 0.9); box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05); }
.table-custom th { border-bottom: none; color: #a4b0be; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; background-color: #f8fafc; padding: 15px 20px;}
.table-custom td { vertical-align: middle; border-bottom: 1px solid #f1f2f6; padding: 15px 20px; transition: 0.2s;}
.table-custom tbody tr:hover td { background-color: #f0f4fd; }

/* Các nhãn màu Pastel */
.bg-soft-blue { background-color: #eef6ff; color: #0062ff; }
.bg-soft-red { background-color: #fcebeb; color: #dc3545; }
.bg-soft-purple { background-color: #f6f0fb; color: #8854d0; }
.bg-soft-green { background-color: #eafaf1; color: #20bf6b; }

/* Nút thao tác nhanh */
.action-btn { width: 32px; height: 32px; border-radius: 0.5rem; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; font-size: 0.85rem; }
.action-btn:hover { transform: translateY(-2px); opacity: 0.8; }
    </style>
</head>

<body>
    <div class="sidebar">
        {{-- Logo Admin --}}
        <div class="logo mb-5 px-3">
            <h3><i class="fa-solid fa-lock"></i> Admin Panel</h3>
        </div>

        <nav class="nav flex-column">
            {{-- Các link quản lý --}}
            <a class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-chart-line me-3"></i> Bảng điều khiển
            </a>
            <a class="nav-link {{ Route::is('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="fa-solid fa-users me-3"></i> Quản lý Thành viên
            </a>
            <a class="nav-link {{ Route::is('admin.posts.*') ? 'active' : '' }}" href="{{ route('admin.posts.index') }}">
                <i class="fa-solid fa-newspaper me-3"></i> Quản lý Bài viết
            </a>
            <a class="nav-link {{ Route::is('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}">
                <i class="fa-solid fa-comments me-3"></i> Quản lý Bình luận
            </a>
            <hr>
            <h6 class="text-muted px-3" style="font-size: 0.8rem;">MỞ RỘNG</h6>
            <a class="nav-link text-muted opacity-50" href="#" style="cursor: not-allowed;">
                <i class="fa-solid fa-bullhorn me-3"></i> Thông báo hệ thống
            </a>
            <a class="nav-link text-muted opacity-50" href="#" style="cursor: not-allowed;">
                <i class="fa-solid fa-flag me-3"></i> Quản lý Báo cáo
            </a>
            {{-- Link QUAY LẠI TRANG CHỦ như cậu yêu cầu --}}
            <a class="nav-link text-danger" href="{{ route('home') }}">
                <i class="fa-solid fa-arrow-left me-3"></i> Quay lại W-Social
            </a>
        </nav>
    </div>

    <div class="top-banner">
        {{-- Tiêu đề trang hiện tại --}}
        <h5 class="fw-bold mb-0">@yield('admin_title')</h5>

        <div class="user-area dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                <span class="me-3 fw-bold">Admin: {{ Auth::user()?->display_name ?? 'Quản Trị Viên' }}</span>
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item py-2" href="#"><i class="fa-solid fa-address-card me-2 text-primary"></i> Hồ sơ Admin</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="#"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="scrollable-column bg-white">
            {{-- Thông báo thành công/lỗi từ Session (Slide chương 3) --}}
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>