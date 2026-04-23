@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 760px;">
    <a href="{{ route('home') }}" class="btn btn-link text-decoration-none text-dark mb-3">
        <i class="fa-solid fa-arrow-left"></i> Quay lại bảng tin
    </a>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $post->author?->avatar_url ? asset($post->author->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($post->author?->display_name ?? $post->author?->username ?? ('User '.$post->author_user_id)).'&background=random' }}" class="rounded-circle" width="46" height="46" alt="avatar">
                    <div>
                        <div class="fw-bold">{{ $post->author?->display_name ?? $post->author?->username ?? ('User #'.$post->author_user_id) }}</div>
                        <small class="text-muted">{{ optional($post->created_at)->diffForHumans() }}</small>
                    </div>
                </div>
            </div>

            <p class="mb-3" style="font-size: 1.05rem;">{{ $post->content }}</p>

            @if($post->media && $post->media->count() > 0)
                <div class="rounded-4 overflow-hidden border mb-3">
                    @foreach($post->media as $mediaItem)
                        @if(($mediaItem->type ?? '') === 'video')
                            <video controls class="w-100 d-block" style="max-height: 460px; background: #000;">
                                <source src="{{ asset('storage/' . $mediaItem->url) }}" type="{{ $mediaItem->mime ?? 'video/mp4' }}">
                            </video>
                        @else
                            <img src="{{ asset('storage/' . $mediaItem->url) }}" class="img-fluid w-100 d-block" alt="media">
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="d-flex flex-wrap gap-3 text-secondary border-top pt-3">
                <span><i class="fa-solid fa-heart text-danger me-1"></i>{{ $post->like_count ?? 0 }} lượt thích</span>
                <span><i class="fa-regular fa-comment me-1"></i>{{ $post->comment_count ?? 0 }} bình luận</span>
                <span><i class="fa-regular fa-share-from-square me-1"></i>{{ $post->share_count ?? 0 }} chia sẻ</span>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" id="comments" style="border-radius: 16px;">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Bình luận</h6>

            @forelse($post->comments as $comment)
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex gap-2">
                        <img src="{{ $comment->user?->avatar_url ? asset($comment->user->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($comment->user?->display_name ?? $comment->user?->username ?? ('User '.$comment->user_id)).'&background=random' }}" class="rounded-circle" width="36" height="36" alt="commenter">
                        <div>
                            <div class="fw-semibold" style="font-size: 0.92rem;">{{ $comment->user?->display_name ?? $comment->user?->username ?? ('User_'.$comment->user_id) }}</div>
                            <div class="text-dark" style="white-space: pre-wrap;">{{ $comment->content }}</div>
                            <small class="text-muted">{{ optional($comment->created_at)->diffForHumans() }}</small>
                        </div>
                    </div>

                    @if(Auth::id() == $comment->user_id || Auth::id() == 1)
                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link p-0 text-danger" style="font-size: 12px; text-decoration: none;">Xóa</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">Chưa có bình luận nào.</p>
            @endforelse
        </div>
    </div>

    @auth
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body">
                <form action="{{ route('comments.store', $post->id) }}" method="POST">
                    @csrf
                    <label class="form-label fw-semibold">Viết bình luận</label>
                    <div class="input-group">
                        <input type="text" name="content" class="form-control" placeholder="Nhập nội dung bình luận..." required>
                        <button class="btn btn-primary" type="submit">Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    @endauth
</div>
@endsection