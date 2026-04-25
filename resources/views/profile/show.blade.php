@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #ffffff;
        color: #000000;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .profile-card {
        max-width: 570px;
        margin: 40px auto;
        padding: 20px;
    }

    .avatar-wrapper {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #eee;
        flex-shrink: 0;
    }

    .avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn-outline-custom {
        border: 1px solid #dbdbdb;
        color: black;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        padding: 7px;
        background: white;
        font-size: 14px;
    }

    .nav-tabs-threads {
        border-bottom: 1px solid #efefef;
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
    }

    .nav-item-threads {
        padding: 12px;
        color: #999;
        cursor: pointer;
        font-weight: 600;
        border-bottom: 2px solid transparent;
    }

    .nav-item-threads.active {
        color: black;
        border-bottom: 2px solid black;
    }

    .username-link {
        font-size: 14px;
        color: #000;
        font-weight: 400;
    }

    .badge-threads {
        background-color: #f5f5f5;
        color: #999;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 12px;
    }
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
        <div class="avatar-wrapper">
            <img src="{{ $user->avatar_url 
        ? asset($user->avatar_url) 
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->display_name) }}"
                alt="Avatar">
        </div>
    </div>

    <div class="mt-3">
        <p style="white-space: pre-wrap;">{{ $user->bio ?? 'Chưa có tiểu sử.' }}</p>
    </div>

    <div class="d-flex gap-2 mt-4">
        @auth

        @if($isOwnProfile)
        <a href="{{ route('profile.edit') }}"
            class="btn btn-outline-dark btn-sm fw-semibold">
            Chỉnh sửa trang cá nhân
        </a>
        @else
        <div class="d-flex gap-2">
            <button type="button"
                class="btn btn-sm follow-btn {{ $isFollowing ? 'btn-primary' : 'btn-outline-primary' }}"
                data-user-id="{{ $user->id }}">
                <span class="follow-text">{{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}</span>
            </button>
            
            {{-- Nút Báo cáo Người dùng --}}
            <button type="button" class="btn btn-sm btn-outline-warning fw-semibold" 
                    onclick="openGeneralReportModal('user', {{ $user->id }})">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </button>
        </div>
        @endif

        @endauth
    </div>

    <div class="nav-tabs-threads" id="profileTabs">
        <div class="nav-item-threads active" role="button">Tất cả</div>
        <div class="nav-item-threads" role="button">Ảnh</div>
        <div class="nav-item-threads" role="button">Album</div>
    </div>

<div class="mt-4" id="profile-posts">
        @forelse($posts as $post)
            {{-- Nhúng giao diện post-card chuẩn (có sẵn nút Xóa/Sửa) vào đây --}}
            @include('components.post-card', ['post' => $post])
        @empty
            <div class="text-muted text-center p-4">
                Người dùng này chưa có bài đăng nào.
            </div>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('#profileTabs .nav-item-threads');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            document.addEventListener('click', async function(event) {
                const followBtn = event.target.closest('.follow-btn');
                if (!followBtn) {
                    return;
                }

                const userId = followBtn.getAttribute('data-user-id');

                try {
                    const response = await fetch(`/users/${userId}/follow`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    if (!data || !data.status) {
                        return;
                    }

                    const followed = data.status === 'followed';

                    document.querySelectorAll(`.follow-btn[data-user-id="${userId}"]`).forEach((btn) => {
                        btn.classList.toggle('btn-primary', followed);
                        btn.classList.toggle('btn-outline-primary', !followed);
                        btn.classList.toggle('text-primary', !followed);
                        btn.classList.toggle('text-secondary', followed);

                        const textNode = btn.querySelector('.follow-text');
                        if (textNode) {
                            textNode.textContent = followed ? 'Đang theo dõi' : 'Theo dõi';
                        } else {
                            btn.textContent = followed ? 'Đang theo dõi' : 'Theo dõi';
                        }
                    });
                } catch (e) {
                    console.error(e);
                }
            });
        });
        document.addEventListener('DOMContentLoaded', () => {

            // LIKE
            document.querySelectorAll('.like-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const postId = this.dataset.postId;

                    const res = await fetch(`/posts/${postId}/like`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await res.json();

                    this.classList.toggle('text-danger', data.liked);
                    this.querySelector('.like-count').textContent = data.count;
                });
            });

            // TOGGLE COMMENT
            document.querySelectorAll('.comment-toggle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.postId;
                    const box = document.getElementById(`comment-box-${id}`);
                    box.style.display = box.style.display === 'none' ? 'block' : 'none';
                });
            });

            // SEND COMMENT
            document.querySelectorAll('.send-comment').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const postId = this.dataset.postId;
                    const input = this.previousElementSibling;
                    const content = input.value;

                    if (!content.trim()) return;

                    const res = await fetch(`/comments/store`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            post_id: postId,
                            content: content
                        })
                    });

                    if (res.ok) {
                        location.reload();
                    }
                });
            });

        });
    </script>
</div>
@endsection