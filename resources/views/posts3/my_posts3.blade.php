@extends('layouts.app')

@section('title', 'Quản lý bài viết cá nhân')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fa-solid fa-user-pen me-2 text-primary"></i>Kho lưu trữ bài viết
        </h4>
        <a href="{{ route('posts3.create') }}" class="btn btn-primary rounded-pill btn-sm px-3">
            <i class="fa-solid fa-plus me-1"></i> Tạo bài mới
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <form action="{{ route('posts3.myPosts') }}" method="GET" class="row g-2">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light" 
                               placeholder="Tìm trong nội dung bài viết..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="visibility" class="form-select border-0 bg-light">
                        <option value="">Tất cả chế độ</option>
                        <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Công khai</option>
                        <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Riêng tư</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row" id="myPostsList">
        @forelse($posts as $post)
            <div class="col-md-6 mb-4 post-item">
                {{-- Giữ nguyên card của Thanh, chỉ thêm class opacity nếu xóa --}}
                <div class="card h-100 border-0 shadow-sm position-relative {{ $post->is_deleted ? 'opacity-50' : '' }}" 
                     style="border-radius: 16px; overflow: hidden;">
                    
                    <span class="position-absolute top-0 end-0 m-3 badge {{ $post->visibility == 'public' ? 'bg-success' : 'bg-secondary' }} shadow-sm" style="z-index: 5;">
                        {{ ucfirst($post->visibility) }}
                    </span>

                    {{-- Thông báo bài viết đã xóa --}}
                    @if($post->is_deleted)
                        <div class="position-absolute top-50 start-50 translate-middle w-100 text-center" style="z-index: 10;">
                            <span class="badge bg-danger shadow-sm p-2">Bài viết này đã bị xóa</span>
                        </div>
                    @endif

                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i>{{ $post->created_at->format('d/m/Y') }}</small>
                        </div>
                        
                        <p class="card-text mb-3 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $post->content }}
                        </p>

                        @if($post->media && $post->media->count() > 0)
                            <div class="mb-3 rounded-3 overflow-hidden border">
                                <div class="row g-1">
                                    @foreach($post->media as $media)
                                        <div class="{{ $post->media->count() == 1 ? 'col-12' : 'col-6' }}">
                                            <img src="{{ Str::startsWith($media->url, 'http') ? $media->url : asset($media->url) }}" 
                                                 class="img-fluid w-100" 
                                                 style="height: 180px; object-fit: cover;"
                                                 onerror="this.onerror=null;this.src='https://placehold.co/600x400?text=Loi+Anh';">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-top-0 p-3 d-flex gap-2">
                        {{-- Logic nút bấm --}}
                        @if($post->is_deleted)
                            <button class="btn btn-light btn-sm w-100 rounded-pill border" disabled>Đã xóa</button>
                        @else
                            <a href="{{ route('posts3.edit', $post->id) }}" class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill">
                                <i class="fa-regular fa-pen-to-square me-1"></i> Sửa
                            </a>
                            <form action="{{ route('posts3.destroy', $post->id) }}" method="POST" class="flex-grow-1" 
                                  onsubmit="return confirm('Thanh có chắc chắn muốn xóa bài này không?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                                    <i class="fa-regular fa-trash-can me-1"></i> Xóa
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted">Không tìm thấy bài viết nào phù hợp.</div>
            </div>
        @endforelse
    </div>
    
    <div class="d-flex justify-content-center mt-3">
        {{ $posts->links() }}
    </div>
</div>
@endsection