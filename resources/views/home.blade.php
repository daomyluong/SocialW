@extends('layouts.app')

@section('content')
<style>
    .feed-shell {
        max-width: 720px;
    }
    .feed-hero {
        background: linear-gradient(135deg, #e8f1ff 0%, #f8fbff 62%, #fff6ea 100%);
        border: 1px solid #e6eef9;
        border-radius: 20px;
        padding: 18px;
    }
    .post-card {
        border: 1px solid #e8edf5;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(20, 40, 70, 0.05);
    }
    .post-toolbar button {
        border: 0;
        background: transparent;
        color: #4f5b6b;
        font-weight: 600;
    }
    .post-toolbar button:hover {
        color: #0d6efd;
    }
    .story-badge {
        min-width: 86px;
    }
    .bookmark-btn .fa-bookmark {
        font-size: 1.05rem;
    }
</style>

<div class="container feed-shell">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @auth
        <div class="feed-hero mb-4">
            <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                @csrf
                <input type="text" name="content" class="form-control border-0" style="border-radius: 14px; background: #fff;" placeholder="Bạn đang nghĩ gì, {{ auth()->user()?->display_name ?? 'bạn' }}?">
                <input type="hidden" name="visibility" value="public">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <label for="homePostImage" class="btn btn-outline-primary btn-sm rounded-pill mb-0"><i class="fa-regular fa-image me-1"></i> Ảnh</label>
                    <input type="file" id="homePostImage" name="image[]" class="d-none" accept="image/*" multiple>
                    <label for="homePostVideo" class="btn btn-outline-danger btn-sm rounded-pill mb-0"><i class="fa-solid fa-video me-1"></i> Video</label>
                    <input type="file" id="homePostVideo" name="video[]" class="d-none" accept="video/*" multiple>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Đăng bài</button>
                </div>
            </form>
            <hr>
            <form action="{{ route('stories3.store') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="file" name="media" class="form-control form-control-sm" accept="image/*,video/*" required>
                <button type="submit" class="btn btn-warning btn-sm rounded-pill">Đăng story</button>
            </form>
        </div>
    @endauth

    @if(isset($stories) && $stories->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex gap-3 overflow-auto">
                @foreach($stories as $userId => $userStories)
                    @php
                        $storyOwner = $userStories->first()?->user;
                    @endphp
                    <div class="text-center story-badge">
                        <div class="rounded-circle p-1 mx-auto mb-1" style="width: 58px; height: 58px; background: linear-gradient(45deg, #f59e0b, #ef4444, #db2777);">
                            <img src="{{ $storyOwner?->avatar_url ? asset($storyOwner->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($storyOwner?->display_name ?? ('User '.$userId)).'&background=random' }}" class="rounded-circle border border-2 border-white w-100 h-100" style="object-fit: cover;" alt="story">
                        </div>
                        <small class="text-muted d-block text-truncate">{{ $storyOwner?->display_name ?? $storyOwner?->username ?? ('User #'.$userId) }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <h5 class="fw-bold mb-3">Bảng tin</h5>

    @forelse($posts as $post)
        @php
            $isLiked = in_array((int) $post->id, $likedPostIds ?? [], true);
            $isBookmarked = in_array((int) $post->id, $bookmarkedPostIds ?? [], true);
            $formattedContent = preg_replace('/@([a-zA-Z0-9_\.]+)/', '<span class="text-primary fw-semibold">@$1</span>', e($post->content ?? ''));
        @endphp
        <div class="card post-card mb-3" id="post-{{ $post->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $post->author?->avatar_url ? asset($post->author->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($post->author?->display_name ?? $post->author?->username ?? ('User '.$post->author_user_id)).'&background=random' }}" class="rounded-circle" width="42" height="42" alt="avatar">
                        <div>
                            <div class="fw-bold">{{ $post->author?->display_name ?? $post->author?->username ?? ('User #'.$post->author_user_id) }}</div>
                            <small class="text-muted">{{ optional($post->created_at)->diffForHumans() }}</small>
                        </div>
                    </div>
                    @auth
                        <button type="button" class="btn btn-sm bookmark-btn" data-post-id="{{ $post->id }}" title="Lưu bài viết">
                            <i class="fa-{{ $isBookmarked ? 'solid' : 'regular' }} fa-bookmark {{ $isBookmarked ? 'text-primary' : 'text-secondary' }}"></i>
                        </button>
                    @endauth
                </div>

                <p class="mb-2">{!! $formattedContent !!}</p>

                @if($post->media->count() > 0)
                    <div class="d-grid gap-2 mb-3">
                        @foreach($post->media as $mediaItem)
                            @if(($mediaItem->type ?? '') === 'video')
                                <div class="rounded-3 overflow-hidden border">
                                    <video controls class="w-100" style="max-height: 460px; background: #000;">
                                        <source src="{{ asset($mediaItem->url) }}" type="{{ $mediaItem->mime ?? 'video/mp4' }}">
                                    </video>
                                </div>
                            @else
                                <div class="rounded-3 overflow-hidden border">
                                    <img src="{{ asset($mediaItem->url) }}" class="img-fluid w-100" alt="media">
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="post-toolbar d-flex flex-wrap gap-3 align-items-center mb-2">
                    <form action="{{ route('posts.like', $post->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit">
                            <i class="fa-{{ $isLiked ? 'solid text-danger' : 'regular' }} fa-heart me-1"></i>{{ $post->like_count ?? 0 }}
                        </button>
                    </form>
                    <button class="btn p-0" type="button" data-bs-toggle="collapse" data-bs-target="#commentArea{{ $post->id }}">
                        <i class="fa-regular fa-comment me-1"></i>{{ $post->comment_count ?? 0 }}
                    </button>
                    <button class="btn p-0" type="button" data-bs-toggle="collapse" data-bs-target="#shareArea{{ $post->id }}">
                        <i class="fa-regular fa-share-from-square me-1"></i>{{ $post->share_count ?? 0 }}
                    </button>
                </div>

                <div class="collapse" id="commentArea{{ $post->id }}">
                    <x-comment :post="$post" />
                    @auth
                        <form action="{{ route('comments.store', $post->id) }}" method="POST" class="mt-2">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="text" name="content" class="form-control rounded-pill" placeholder="Viết bình luận..." required>
                                <button class="btn btn-primary rounded-pill ms-2" type="submit">Gửi</button>
                            </div>
                        </form>
                    @endauth
                </div>

                <div class="collapse" id="shareArea{{ $post->id }}">
                    @auth
                        <form action="{{ route('posts.share', $post->id) }}" method="POST" class="mt-2 d-flex gap-2">
                            @csrf
                            <input type="text" name="comment" class="form-control form-control-sm" placeholder="Lời nhắn khi chia sẻ (tuỳ chọn)">
                            <button type="submit" class="btn btn-success btn-sm">Chia sẻ</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-light border">Chưa có bài viết nào trên bảng tin.</div>
    @endforelse
</div>
@endsection

@section('suggestions')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="fw-bold mb-3">Gợi ý theo dõi</div>
        @auth
            @forelse($suggestedUsers as $user)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="{{ route('profile.show', $user->id) }}" class="text-decoration-none text-dark d-flex align-items-center gap-2">
                        <img src="{{ $user->avatar_url ? asset($user->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($user->display_name ?? $user->username).'&background=random' }}" class="rounded-circle" width="34" height="34" alt="user">
                        <div>
                            <div class="fw-semibold" style="font-size: 0.88rem;">{{ $user->display_name ?? $user->username }}</div>
                            <small class="text-muted">@{{ $user->username }}</small>
                        </div>
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill follow-btn" data-user-id="{{ $user->id }}">Theo dõi</button>
                </div>
            @empty
                <p class="text-muted small mb-0">Hiện không có gợi ý mới.</p>
            @endforelse
        @else
            <p class="text-muted small mb-0">Đăng nhập để xem gợi ý theo dõi.</p>
        @endauth
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-bold">Bài đã lưu</div>
            <a href="{{ route('bookmarks.index') }}" class="small text-decoration-none">Xem tất cả</a>
        </div>
        @auth
            @forelse($savedPosts as $saved)
                <div class="mb-2">
                    <a href="{{ route('home') }}#post-{{ $saved->post_id }}" class="text-decoration-none">
                        <div class="small fw-semibold text-dark text-truncate">{{ $saved->post?->content ?? 'Bài viết đã bị xóa' }}</div>
                        <small class="text-muted">{{ $saved->post?->author?->display_name ?? $saved->post?->author?->username ?? 'Không xác định' }}</small>
                    </a>
                </div>
            @empty
                <p class="text-muted small mb-0">Bạn chưa lưu bài viết nào.</p>
            @endforelse
        @else
            <p class="text-muted small mb-0">Đăng nhập để xem bài viết đã lưu.</p>
        @endauth
    </div>
</div>
@endsection

@section('scripts')
<script>
    (() => {
        const csrf = "{{ csrf_token() }}";

        document.body.addEventListener('click', async (event) => {
            const followBtn = event.target.closest('.follow-btn');
            if (followBtn) {
                const userId = followBtn.getAttribute('data-user-id');
                try {
                    const response = await fetch(`/users/${userId}/follow`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    followBtn.textContent = payload.status === 'followed' ? 'Đang theo dõi' : 'Theo dõi';
                    followBtn.classList.toggle('btn-primary', payload.status === 'followed');
                    followBtn.classList.toggle('btn-outline-primary', payload.status !== 'followed');
                } catch (error) {
                    // ignore
                }
                return;
            }

            const bookmarkBtn = event.target.closest('.bookmark-btn');
            if (bookmarkBtn) {
                const postId = bookmarkBtn.getAttribute('data-post-id');
                try {
                    const response = await fetch(`/bookmarks/toggle/${postId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            Accept: 'application/json'
                        },
                        body: JSON.stringify({ folder_name: 'Tất cả' })
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const icon = bookmarkBtn.querySelector('i');
                    const added = payload.status === 'added';
                    icon.classList.toggle('fa-solid', added);
                    icon.classList.toggle('fa-regular', !added);
                    icon.classList.toggle('text-primary', added);
                    icon.classList.toggle('text-secondary', !added);
                } catch (error) {
                    // ignore
                }
                return;
            }

            const loadMoreBtn = event.target.closest('.load-more-btn');
            if (!loadMoreBtn) {
                return;
            }

            const postId = loadMoreBtn.getAttribute('data-post-id');
            const container = document.getElementById(`extra-comments-${postId}`);
            if (!container) {
                return;
            }

            if (container.innerHTML.trim() !== '') {
                container.classList.toggle('d-none');
                return;
            }

            try {
                const response = await fetch(`/posts/${postId}/load-more-comments`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                container.innerHTML = payload.html || '';
            } catch (error) {
                // ignore
            }
        });
    })();
</script>
@endsection
