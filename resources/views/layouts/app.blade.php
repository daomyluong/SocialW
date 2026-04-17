<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W-Social | @yield('title', 'Mạng xã hội')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --hlink-blue: #0062ff; 
            --hlink-green: #28a745;
            --hlink-bg: #f8fafc;
            --threads-gray: #f1f3f5;
        }

        body, html {
            height: 100%;
            overflow: hidden;
            background-color: var(--hlink-bg);
            color: #212529;
        }

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

        .top-banner {
            margin-left: 250px; height: 70px; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 30px; position: sticky; top: 0;
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); z-index: 999;
        }

        .search-box {
            background: var(--threads-gray); border-radius: 25px;
            border: 1px solid transparent; padding: 8px 20px; width: 350px;
        }

        .main-content { margin-left: 250px; height: calc(100vh - 70px); }

        .scrollable-column { height: 100%; overflow-y: auto; overflow-x: hidden; padding: 20px; }

        .scrollable-column::-webkit-scrollbar { width: 5px; }
        .scrollable-column::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

        .bg-primary { background-color: var(--hlink-blue) !important; }
        .text-primary { color: var(--hlink-blue) !important; }
        
        .btn-follow {
            color: var(--hlink-green); border: 1px solid var(--hlink-green);
            font-weight: 600; transition: 0.3s;
        }
        .btn-follow:hover { background-color: var(--hlink-green); color: #fff; }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo mb-5 px-3"><h3><i class="fa-solid fa-link"></i> W-Social</h3></div>
        <nav class="nav flex-column">
            <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                <i class="fa-solid fa-house me-3"></i> Trang chủ
            </a>
            <a class="nav-link {{ Route::is('search') ? 'active' : '' }}" href="{{ route('search') }}">
                <i class="fa-solid fa-magnifying-glass me-3"></i> Tìm kiếm
            </a>
            <a class="nav-link text-dark" href="#"><i class="fa-regular fa-heart me-3"></i> Thông báo</a>
            <a class="nav-link text-dark" href="#"><i class="fa-regular fa-square-plus me-3"></i> Tạo bài viết</a>
        </nav>
    </div>

    <div class="top-banner">
        <form action="{{ route('search') }}" method="GET">
            <div class="position-relative">
                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="query" class="search-box ps-5" placeholder="Tìm kiếm trên W-Social...">
            </div>
        </form>

        <div class="user-area dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown">
                <div class="text-end me-3 d-none d-sm-block">
                    <div class="fw-bold">{{ Auth::user()->display_name ?? 'Lương Mỵ Đào' }}</div>
                    <small class="text-muted">@auth {{ Auth::user()->username }} @else guest @endauth</small>
                </div>
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="fa-solid fa-user"></i>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item py-2" href="{{ route('profile') }}"><i class="fa-regular fa-circle-user me-2 text-primary"></i> Profile cá nhân</a></li>
                <li><a class="dropdown-item py-2 text-primary fw-bold" href="#"><i class="fa-solid fa-user-shield me-2"></i> Quản trị hệ thống</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="#"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row h-100 g-0">
            <div class="col-lg-8 border-end scrollable-column bg-white">
                @yield('content')
            </div>

            <div class="col-lg-4 scrollable-column">
                <h6 class="fw-bold text-dark mb-4 px-2">Gợi ý cho bạn</h6>
                @yield('suggestions') 
                <hr>
                <div class="px-2"><small class="text-muted">© 2026 W-Social - Nhóm 1 HUB</small></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>