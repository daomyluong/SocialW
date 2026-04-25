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
                <div class="user-stat-link" role="button" data-bs-toggle="modal" data-bs-target="#userListModal" data-type="followers" data-user-id="{{ $user->id }}">
                    <strong id="count-followers">{{ $followerCount }}</strong> Theo dõi
                </div>
                <div class="user-stat-link" role="button" data-bs-toggle="modal" data-bs-target="#userListModal" data-type="following" data-user-id="{{ $user->id }}">
                    <strong id="count-following">{{ $followingCount }}</strong> Đang theo dõi
                </div>
            </div>

            <style>
                .user-stat-link { cursor: pointer; }
                .user-stat-link:hover { text-decoration: underline; color: #000; }
                #userListContent { max-height: 400px; overflow-y: auto; }
                .user-item { padding: 10px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; }
                .user-item:last-child { border-bottom: none; }
            </style>
            <div class="modal fade" id="userListModal" tabindex="-1" aria-labelledby="userListModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-sm modal-md">
                    <div class="modal-content" style="border-radius: 15px;">
                        <div class="modal-header border-0">
                            <h6 class="modal-title fw-bold" id="userListTitle">Danh sách</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0" id="userListContent">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                            </div>
                        </div>
                    </div>
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
        document.addEventListener('DOMContentLoaded', function() {
        const userListModal = document.getElementById('userListModal');
        
        userListModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const type = button.getAttribute('data-type'); // 'followers' hoặc 'following'
            const userId = button.getAttribute('data-user-id');
            const title = type === 'followers' ? 'Người theo dõi' : 'Đang theo dõi';
            
            document.getElementById('userListTitle').innerText = title;
            const contentContainer = document.getElementById('userListContent');
            
            // Hiển thị loading
            contentContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

            // Gọi API (Bạn cần tạo Route này trong Laravel)
            fetch(`/profile/${userId}/network?type=${type}`)
                .then(response => response.json())
                .then(data => {
                    if (data.users && data.users.length > 0) {
                        let html = '';
                        data.users.forEach(u => {
                            html += `
                                <div class="user-item">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="${u.avatar_url}" class="rounded-circle" width="35" height="35">
                                        <div>
                                            <div class="fw-bold" style="font-size: 14px;">${u.display_name}</div>
                                            <div class="text-muted" style="font-size: 12px;">@${u.username}</div>
                                        </div>
                                    </div>
                                    <a href="/profile/${u.id}" class="btn btn-sm btn-outline-dark fw-bold" style="font-size: 12px;">Xem</a>
                                </div>
                            `;
                        });
                        contentContainer.innerHTML = html;
                    } else {
                        contentContainer.innerHTML = '<div class="text-center py-4 text-muted">Trống.</div>';
                    }
                })
                .catch(error => {
                    contentContainer.innerHTML = '<div class="text-center py-4 text-danger">Không thể tải dữ liệu.</div>';
                });
        });
    });
    </script>
</div>
@endsection