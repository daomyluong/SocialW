@extends('layouts.admin')

@section('title', 'Bài viết #' . $post->id)

@section('content')
<style>
    .post-shell {
        border-radius: 1.5rem;
        border: 1px solid rgba(94, 169, 255, 0.14);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(241, 248, 255, 0.88));
        box-shadow: 0 18px 40px rgba(45, 90, 138, 0.08);
        backdrop-filter: blur(12px);
    }

    .post-page-wrap {
        max-width: 940px;
    }

    .comment-card {
        border: 1px solid rgba(94, 169, 255, 0.12);
        background: rgba(255, 255, 255, 0.86);
        border-radius: 1rem;
    }

    .comment-card.focused {
        border-color: rgba(47, 125, 247, 0.28);
        background: linear-gradient(180deg, rgba(94, 169, 255, 0.11), rgba(255, 255, 255, 0.92));
        box-shadow: 0 12px 28px rgba(47, 125, 247, 0.12);
    }

    .comment-meta {
        color: #6b86a6;
        font-size: 0.84rem;
    }

    #storyContent img,
    #storyContent video {
        max-height: 80vh;
        max-width: 90vw;
        border-radius: 12px;
    }

    /* Thêm CSS cho link user để hover có gạch chân cho đẹp */
    .profile-link {
        text-decoration: none;
        transition: 0.2s;
    }
    .profile-link:hover {
        opacity: 0.8;
    }
    .profile-link:hover h4, .profile-link:hover .fw-bold {
        text-decoration: underline;
    }
</style>

<div class="container post-page-wrap px-3 px-md-4" data-focus-comment="{{ $focusCommentId ? 'comment-' . $focusCommentId : '' }}">
    <div class="post-shell p-3 p-md-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            
            {{-- ĐÃ SỬA: Thêm Avatar và bọc link Profile cho Tác giả bài viết --}}
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url('/profile/' . $post->user_id) }}" class="profile-link">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($post->author_name) }}&background=4facfe&color=fff" class="rounded-circle shadow-sm" width="54" height="54" alt="avatar">
                </a>
                <div>
                    <div class="text-uppercase small fw-bold text-primary mb-1">Bài viết gốc</div>
                    <a href="{{ url('/profile/' . $post->user_id) }}" class="profile-link">
                        <h4 class="fw-bold mb-1" style="color: #17324d;">{{ $post->author_name }}</h4>
                    </a>
                    <div class="comment-meta">
                        <a href="{{ url('/profile/' . $post->user_id) }}" class="profile-link text-secondary">
                            {{ '@' . ($post->author_username ?? 'unknown') }}
                        </a> · #{{ $post->id }}
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm rounded-pill px-3" style="background: rgba(94, 169, 255, 0.12); color: #1d68c1; border: 1px solid rgba(94, 169, 255, 0.18);">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay về
            </a>
        </div>

        <div class="p-3 rounded-4 mb-3" style="background: rgba(94, 169, 255, 0.06); border: 1px solid rgba(94, 169, 255, 0.12);">
            <p class="mb-0" style="white-space: pre-wrap; line-height: 1.75; color: #223547;">{{ $post->content }}</p>
        </div>

        {{-- ĐÃ SỬA: Lấy media đúng chuẩn từ bảng trung gian post_media để hiển thị Ảnh/Video --}}
        @php
            $media = DB::table('media')
                ->join('post_media', 'media.id', '=', 'post_media.media_id')
                ->where('post_media.post_id', $post->id)
                ->select('media.*')
                ->first();
        @endphp

        @if($media && $media->type === 'image')
            <div class="rounded-4 overflow-hidden mb-3 border" style="border-color: rgba(94, 169, 255, 0.12) !important;">
                <img src="{{ asset('storage/' . $media->url) }}" onerror="this.src='{{ asset($media->url) }}'" class="img-fluid w-100" alt="post image">
            </div>
        @elseif($media && $media->type === 'video')
            <div class="rounded-4 overflow-hidden mb-3 border" style="border-color: rgba(94, 169, 255, 0.12) !important;">
                <video controls class="w-100">
                    <source src="{{ asset('storage/' . $media->url) }}" onerror="this.src='{{ asset($media->url) }}'">
                </video>
            </div>
        @endif

        <div class="d-flex gap-4 text-secondary fw-semibold small">
            <span><i class="fa-regular fa-heart me-1"></i> {{ $post->like_count ?? 0 }}</span>
            <span><i class="fa-regular fa-comment me-1"></i> {{ $post->comment_count ?? $comments->count() }}</span>
        </div>
    </div>

    <div class="post-shell p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-1" style="color: #17324d;">Bình luận</h5>
                <div class="comment-meta">Bình luận được ưu tiên theo báo cáo sẽ xuất hiện đầu danh sách.</div>
            </div>
            @if($focusCommentId)
            <span class="badge rounded-pill px-3 py-2" style="background: rgba(47, 125, 247, 0.12); color: #1d68c1; border: 1px solid rgba(47, 125, 247, 0.18);">Focus: #{{ $focusCommentId }}</span>
            @endif
        </div>

        <div class="d-grid gap-3">
            @forelse($comments as $comment)
            <div id="comment-{{ $comment->id }}" class="comment-card p-3 {{ $focusCommentId == $comment->id ? 'focused' : '' }}">
                <div class="d-flex gap-3">
                    
                    {{-- ĐÃ SỬA: Gắn link Profile cho Avatar người bình luận --}}
                    <a href="{{ url('/profile/' . $comment->user_id) }}" class="profile-link flex-shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name) }}&background=5ea9ff&color=fff" class="rounded-circle shadow-sm" width="42" height="42" alt="avatar">
                    </a>

                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                            
                            {{-- ĐÃ SỬA: Gắn link Profile cho Tên người bình luận --}}
                            <a href="{{ url('/profile/' . $comment->user_id) }}" class="profile-link text-dark">
                                <div class="fw-bold">{{ $comment->author_name }}</div>
                            </a>

                            <small class="comment-meta">{{ \Carbon\Carbon::parse($comment->created_at)->format('H:i - d/m/Y') }}</small>
                        </div>
                        <div class="text-dark" style="white-space: pre-wrap; line-height: 1.7;">{{ $comment->content }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">Bài viết này chưa có bình luận nào.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Chia sẻ bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('posts.share', $post->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <textarea name="comment" class="form-control mb-3" rows="3" placeholder="Viết lời nhắn..."></textarea>
                    <p class="small text-muted mb-2">Nhắc tên bạn bè:</p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($allUsers ?? [] as $u)
                            <span class="badge rounded-pill bg-light text-dark border p-2" style="cursor:pointer;" onclick="addMention('{{ $u->username }}', {{ $post->id }})">@ {{ $u->username }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Chia sẻ ngay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const container = document.querySelector('[data-focus-comment]');
        const targetId = container ? container.dataset.focusComment : '';
        if (!targetId) {
            return;
        }

        window.addEventListener('load', function() {
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });
    })();
</script>
@endsection