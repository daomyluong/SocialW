@extends('layouts.app')

@section('content')
<style>
    /* Giao diện màu trắng */
    body { background-color: #ffffff; color: #000000; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .profile-card { max-width: 570px; margin: 40px auto; padding: 20px; }
    .avatar-img { width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 1px solid #efefef; }
    .btn-outline-custom { border: 1px solid #dbdbdb; color: black; border-radius: 10px; font-weight: 600; width: 100%; padding: 7px; background: white; font-size: 14px; }
    .nav-tabs-threads { border-bottom: 1px solid #efefef; display: flex; justify-content: space-around; margin-top: 20px; }
    .nav-item-threads { padding: 12px; color: #999; cursor: pointer; font-weight: 600; border-bottom: 2px solid transparent; }
    .nav-item-threads.active { color: black; border-bottom: 2px solid black; }
    .username-link { font-size: 14px; color: #000; font-weight: 400; }
    .badge-threads { background-color: #f5f5f5; color: #999; font-size: 11px; padding: 4px 8px; border-radius: 12px; }
</style>

<div class="profile-card">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;">{{ $user->display_name }}</h2>
            <div class="d-flex align-items-center gap-2">
                <span class="username-link">{{ $user->username }}</span>
                <span class="badge-threads">W-social</span>
            </div>
        </div>
        <div>
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->display_name) }}&background=ebebeb&color=000" 
                 class="avatar-img" alt="Avatar">
        </div>
    </div>

    <div class="mt-3">
        <p style="white-space: pre-wrap;">{{ $user->bio ?? 'Chưa có tiểu sử.' }}</p>
    </div>

    <div class="mt-2" style="color: #999; font-size: 14px;">
        <span>{{ $user->follower_count }} người theo dõi</span>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-outline-custom">Chỉnh sửa trang cá nhân</button>
        <button class="btn btn-outline-custom">Chia sẻ trang cá nhân</button>
    </div>

    <div class="nav-tabs-threads">
        <div class="nav-item-threads active">W-social</div>
        <div class="nav-item-threads">Trả lời</div>
        <div class="nav-item-threads">Bài đăng lại</div>
    </div>

    <div class="mt-4 text-center" style="color: #999; padding-top: 40px;">
        <p>Bạn chưa có đoạn thread nào.</p>
    </div>
</div>
@endsection