@forelse($posts as $post)
@php
$isLiked = in_array((int) $post->id, $likedPostIds ?? [], true);
$isBookmarked = in_array((int) $post->id, $bookmarkedPostIds ?? [], true);
@endphp

<div class="card post-card mb-3" id="post-{{ $post->id }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ $post->author?->avatar_url ? asset($post->author->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($post->author?->display_name ?? $post->author?->username ?? ('User '.$post->user_id)).'&background=random' }}" class="rounded-circle" width="42" height="42" alt="avatar">
                <div>
                    <div class="fw-bold">{{ $post->author?->display_name ?? $post->author?->username ?? ('User #'.$post->user_id) }}</div>
                    <small class="text-muted">{{ optional($post->created_at)->diffForHumans() }}</small>
                </div>
            </div>
            @auth
            @php $isBookmarked = in_array((int) $post->id, $bookmarkedPostIds ?? [], true); @endphp
            <button type="button" class="btn btn-sm bookmark-btn" data-post-id="{{ $post->id }}">
                <i class="fa-{{ $isBookmarked ? 'solid text-primary' : 'regular text-secondary' }} fa-bookmark"></i>
            </button>
            @endauth
        </div>

        @php
        $formattedContent = preg_replace(
        '/@([a-zA-Z0-9_\.]+)/',
        '<span class="text-primary fw-semibold">@$1</span>',
        e($post->content ?? '')
        );
        @endphp

        <p class="mb-2">{!! $formattedContent !!}</p>

        @if($post->media->count() > 0)
        <div class="d-grid gap-2 mb-3">
            @foreach($post->media as $mediaItem)
            @if(($mediaItem->type ?? '') === 'video')
            <div class="rounded-3 overflow-hidden border">
                <video controls class="w-100" style="max-height: 460px; background: #000;">
                    <source src="{{ asset('storage/' . $mediaItem->url) }}" type="{{ $mediaItem->mime ?? 'video/mp4' }}">
                </video>
            </div>
            @else
            <div class="rounded-3 overflow-hidden border">
                <img src="{{ asset('storage/' . $mediaItem->url) }}" class="img-fluid w-100" alt="media">
            </div>
            @endif
            @endforeach
        </div>
        @endif

        <div class="post-toolbar d-flex flex-wrap gap-3 align-items-center mb-2">
            <form action="{{ route('posts.like', $post->id) }}" method="POST" class="m-0 ajax-like-form">
                @csrf
                <button type="submit">
                    <i class="like-icon fa-{{ $isLiked ? 'solid text-danger' : 'regular' }} fa-heart me-1"></i>
                    <span class="like-count">{{ $post->like_count ?? 0 }}</span>
                </button>
            </form>
            <a href="{{ route('posts3.show', $post->id) }}" class="btn p-0 text-decoration-none text-dark">
                <i class="fa-regular fa-comment me-1"></i><span class="comment-count">{{ $post->comment_count ?? 0 }}</span>
            </a>
            <button class="btn p-0" type="button" data-bs-toggle="collapse" data-bs-target="#shareArea{{ $post->id }}">
                <i class="fa-regular fa-share-from-square me-1"></i><span class="share-count">{{ $post->share_count ?? 0 }}</span>
            </button>
        </div>

        <div class="collapse" id="shareArea{{ $post->id }}">
            @auth
            <form action="{{ route('posts.share', $post->id) }}" method="POST" class="mt-2 d-flex gap-2 ajax-share-form">
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