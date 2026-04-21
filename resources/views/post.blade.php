@extends('layouts.app')

@section('title', 'Bài viết #' . $post->id)

@section('content')
<style>
    .post-shell {
        border-radius: 1.5rem;
        border: 1px solid rgba(94, 169, 255, 0.14);
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(241, 248, 255, 0.88));
        box-shadow: 0 18px 40px rgba(45, 90, 138, 0.08);
        backdrop-filter: blur(12px);
    }

    .comment-card {
        border: 1px solid rgba(94, 169, 255, 0.12);
        background: rgba(255, 255, 255, 0.86);
        border-radius: 1rem;
    }

    .comment-card.focused {
        border-color: rgba(47, 125, 247, 0.28);
        background: linear-gradient(180deg, rgba(94, 169, 255, 0.11), rgba(255,255,255,0.92));
        box-shadow: 0 12px 28px rgba(47, 125, 247, 0.12);
    }

    .comment-meta {
        color: #6b86a6;
        font-size: 0.84rem;
    }
</style>

<div class="container-fluid px-0" data-focus-comment="{{ $focusCommentId ? 'comment-' . $focusCommentId : '' }}">
    <div class="post-shell p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="text-uppercase small fw-bold text-primary mb-1">Bài viết gốc</div>
                <h4 class="fw-bold mb-1" style="color: #17324d;">{{ $post->author_name }}</h4>
                <div class="comment-meta">{{ '@' . ($post->author_username ?? 'unknown') }} · #{{ $post->id }}</div>
            </div>
            <a href="{{ route('home') }}" class="btn btn-sm rounded-pill px-3" style="background: rgba(94, 169, 255, 0.12); color: #1d68c1; border: 1px solid rgba(94, 169, 255, 0.18);">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay về
            </a>
        </div>

        <div class="p-3 rounded-4 mb-3" style="background: rgba(94, 169, 255, 0.06); border: 1px solid rgba(94, 169, 255, 0.12);">
            <p class="mb-0" style="white-space: pre-wrap; line-height: 1.75; color: #223547;">{{ $post->content }}</p>
        </div>

        @if(!empty($post->image_url))
            <div class="rounded-4 overflow-hidden mb-3 border" style="border-color: rgba(94, 169, 255, 0.12) !important;">
                <img src="{{ $post->image_url }}" class="img-fluid w-100" alt="post image">
            </div>
        @endif

        @if(!empty($post->video_url))
            <div class="rounded-4 overflow-hidden mb-3 border" style="border-color: rgba(94, 169, 255, 0.12) !important;">
                <video controls class="w-100">
                    <source src="{{ $post->video_url }}">
                </video>
            </div>
        @endif

        <div class="d-flex gap-4 text-secondary fw-semibold small">
            <span><i class="fa-regular fa-heart me-1"></i> {{ $post->like_count ?? 0 }}</span>
            <span><i class="fa-regular fa-comment me-1"></i> {{ $post->comment_count ?? $comments->count() }}</span>
        </div>
    </div>

    <div class="post-shell p-4">
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
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name) }}&background=5ea9ff&color=fff" class="rounded-circle" width="42" height="42" alt="avatar">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                <div class="fw-bold text-dark">{{ $comment->author_name }}</div>
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
@endsection

@section('scripts')
<script>
    (function () {
        const container = document.querySelector('[data-focus-comment]');
        const targetId = container ? container.dataset.focusComment : '';
        if (!targetId) {
            return;
        }

        window.addEventListener('load', function () {
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    })();
</script>
@endsection