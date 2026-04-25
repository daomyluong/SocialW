<!DOCTYPE html>
<html lang="vi">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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


        body,
        html {
            height: 100%;
            background-color: var(--hlink-bg);
            color: #212529;
        }


        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            border-right: 1px solid #e2e8f0;
            padding: 20px;
            background: #fff;
            z-index: 1000;
            overflow-y: auto;
        }


        .logo h3 {
            background: linear-gradient(45deg, var(--hlink-blue), var(--hlink-green));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }


        .sidebar .nav-link {
            padding: 12px 15px;
            border-radius: 10px;
            transition: 0.3s;
            margin-bottom: 5px;
            color: #4a5568 !important;
        }


        .sidebar .nav-link:hover,
        .sidebar .active {
            background: #eef6ff;
            color: var(--hlink-blue) !important;
            font-weight: bold;
        }


        .top-banner {
            margin-left: 250px;
            height: 70px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 999;
        }


        .search-box {
            background: var(--threads-gray);
            border-radius: 25px;
            border: 1px solid transparent;
            padding: 8px 20px;
            width: 350px;
        }


        .main-content {
            margin-left: 250px;
            height: calc(100vh - 70px);
        }


        .scrollable-column {
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px;
        }


        .scrollable-column::-webkit-scrollbar {
            width: 5px;
        }


        .scrollable-column::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }


        .bg-primary {
            background-color: var(--hlink-blue) !important;
        }


        .text-primary {
            color: var(--hlink-blue) !important;
        }


        .btn-follow {
            color: var(--hlink-green);
            border: 1px solid var(--hlink-green);
            font-weight: 600;
            transition: 0.3s;
        }


        .btn-follow:hover {
            background-color: var(--hlink-green);
            color: #fff;
        }
    </style>
</head>


<body>
    <div class="sidebar">
        <div class="logo mb-5 px-3">
            <h3><i class="fa-solid fa-link"></i> W-Social</h3>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                <i class="fa-solid fa-house me-3"></i> Trang chủ
            </a>
            <a class="nav-link {{ Route::is('search') ? 'active' : '' }}" href="{{ route('search') }}">
                <i class="fa-solid fa-magnifying-glass me-3"></i> Tìm kiếm
            </a>
            <a class="nav-link {{ Route::is('notifications.index') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                <i class="fa-regular fa-heart me-3"></i> Thông báo
            </a>
            <a class="nav-link {{ Route::is('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                <i class="fa-regular fa-comments me-3"></i> Nhắn tin
            </a>
            <a class="nav-link {{ Route::is('posts3.create') ? 'active' : '' }}" href="{{ route('posts3.create') }}">
                <i class="fa-regular fa-square-plus me-3"></i> Tạo bài viết
            </a>


            <a class="nav-link {{ Route::is('bookmarks.index') ? 'active' : '' }}" href="{{ route('bookmarks.index') }}">
                <i class="fa-regular fa-bookmark me-3"></i> Đã lưu
            </a>
            <a class="nav-link {{ Route::is('post3.myPosts') ? 'active' : '' }}" href="{{ route('posts3.myPosts') }}">
                <i class="fa-solid fa-list-ul"></i> Bài viết của tôi
            </a>
        </nav>
    </div>


    <div class="top-banner">


        <div class="user-area dropdown ms-auto">
            @auth
            <button type="button" class="btn d-flex align-items-center text-decoration-none dropdown-toggle text-dark p-0 border-0 bg-transparent" id="userDropdown" data-bs-toggle="dropdown">
                <div class="text-end me-3 d-none d-sm-block">
                    <div class="fw-bold">{{ auth()->user()?->display_name ?? auth()->user()?->name ?? 'Bạn' }}</div>
                    <small class="text-muted">{{ auth()->user()?->username }}</small>
                </div>
                @php
    $user = auth()->user();
@endphp


<div style="width:45px; height:45px;">
    <img
        src="{{ $user->avatar_url ? asset($user->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name ?? 'User') }}"
        style="width:100%; height:100%; border-radius:50%; object-fit:cover;"
        alt="avatar"
    >
</div>
            </button>


            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item py-2" href="{{ route('profile') }}"><i class="fa-regular fa-circle-user me-2 text-primary"></i> Profile cá nhân</a></li>
               
                <li><a class="dropdown-item py-2 text-primary fw-bold" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-user-shield me-2"></i> Quản trị hệ thống</a></li>
               
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger" style="border: none; background: none; width: 100%; text-align: left;">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
            @else
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Đăng ký</a>
            </div>
            @endauth
        </div>
    </div>


    <div class="main-content">
        <div class="container-fluid h-100">
            <div class="row h-100 g-0">
                <div class="col-lg-8 border-end scrollable-column bg-white">
                    @yield('content')
                </div>


                <div class="col-lg-4 scrollable-column">
                    @include('partials.suggestions')
                    <hr>
                    <div class="px-2"><small class="text-muted">© 2026 W-Social - Nhóm 1 HUB</small></div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>


</html>

