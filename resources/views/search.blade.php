@extends('layouts.app')

@section('title', 'Tìm kiếm')

@section('content')
<div class="container" style="max-width: 700px;">
    <h4 class="fw-bold mb-3">Tìm kiếm</h4>
    <p class="text-muted mb-4">Tìm người dùng và bài viết công khai theo từ khóa.</p>

    <form action="{{ route('search') }}" method="GET" class="card border-0 shadow-sm">
        <div class="card-body d-flex gap-2">
            <input
                type="text"
                name="query"
                value="{{ $query ?? request('query') }}"
                class="form-control"
                placeholder="Nhập từ khóa...">
            <button class="btn btn-primary" type="submit">Tìm</button>
        </div>
    </form>

    @if(!empty($query))
    <div class="mt-4">
        <h6 class="fw-bold">Người dùng</h6>
        @forelse($users as $user)
        <a href="{{ route('profile.show', $user->id) }}" class="text-decoration-none text-dark">
            <div class="border rounded-3 p-2 mb-2 bg-white">
                <div class="fw-semibold">{{ $user->display_name ?? $user->name ?? 'Người dùng' }}</div>
                <small class="text-muted">{{ '@'.$user->username }}</small>
            </div>
        </a>
        @empty
        <p class="text-muted small">Không tìm thấy người dùng phù hợp.</p>
        @endforelse
    </div>

    <div class="mt-3">
        <h6 class="fw-bold">Bài viết</h6>
        @forelse($posts as $post)
        <div class="border rounded-3 p-2 mb-2 bg-white">
            <div class="small text-muted mb-1">
                {{ '@'.($post->author->username ?? 'guest') }} · {{ optional($post->created_at)->diffForHumans() }}
            </div>
            <div>{{ $post->content }}</div>
        </div>
        @empty
        <p class="text-muted small">Không tìm thấy bài viết phù hợp.</p>
        @endforelse
    </div>
    @endif
</div>
@endsection
