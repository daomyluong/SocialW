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

            <div class="d-flex gap-3 mt-3" style="font-size: 14px; color: #555;">
                <div>
                    <strong>{{ $postCount }}</strong> Bài viết
                </div>
                <div>
                    <strong>{{ $followerCount }}</strong> Theo dõi
                </div>
                <div>
                    <strong>{{ $followingCount }}</strong> Đang theo dõi
                </div>
            </div>
        </div>
        <div>
            <img src="{{ $user->avatar_url ? asset($user->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name) . '&background=ebebeb&color=000' }}" 
                 class="avatar-img" alt="Avatar">
        </div>
    </div>

    <div class="mt-3">
        <p style="white-space: pre-wrap;">{{ $user->bio ?? 'Chưa có tiểu sử.' }}</p>
    </div>

    <div class="d-flex gap-2 mt-4">
        @if ($isOwnProfile)
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-custom">Chỉnh sửa trang cá nhân</a>
        @else
            <form action="{{ route('profile.follow', $user->id) }}" method="POST" style="width: 100%;">
                @csrf
                <button type="submit" class="btn btn-outline-custom w-100">Theo dõi</button>
            </form>
        @endif
      
    </div>

    <div class="nav-tabs-threads" id="profileTabs">
        <div class="nav-item-threads active" role="button">Tất cả</div>
        <div class="nav-item-threads" role="button">Ảnh</div>
        <div class="nav-item-threads" role="button">Album</div>
    </div>

    <div class="mt-4 text-center" style="color: #999; padding-top: 40px;">
        <p>Bạn chưa có đoạn W-social nào.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('#profileTabs .nav-item-threads');
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    tabs.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</div>
@endsection