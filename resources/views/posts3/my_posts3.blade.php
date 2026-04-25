@extends('layouts.app')

@section('title', 'Bài viết của tôi')

@section('content')
<div class="container" style="max-width: 600px;">
    
    <div class="d-flex align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử bài đăng</h4>
    </div>

    {{-- Hiển thị thông báo (khi sửa/xóa thành công) --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Vòng lặp in ra các bài viết --}}
    @forelse($posts as $post)
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body">
                
                {{-- Header bài viết: Avatar, Tên, Thời gian --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-light rounded-circle me-3 d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-user text-secondary"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block">{{ Auth::user()->display_name ?? 'Đao'}}</span>
                            <small class="text-muted" title="Trạng thái hiển thị">
                                {{ $post->created_at->diffForHumans() }} • 
                                @if(($post->visibility ?? 'public') === 'public')
                                    <i class="fa-solid fa-earth-americas"></i>
                                @elseif(($post->visibility ?? 'public') === 'follower')
                                    <i class="fa-solid fa-user-group"></i>
                                @else
                                    <i class="fa-solid fa-lock"></i>
                                @endif
                            </small>
                        </div>
                    </div>

                    {{-- Nút Chỉnh sửa / Xóa --}}
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                           <li>
                                <a class="dropdown-item" href="{{ route('posts3.edit', $post->id) }}">
                                    <i class="fa-solid fa-pen me-2"></i>Chỉnh sửa
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('posts3.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Xác nhận xóa bài viết này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start">
                                        <i class="fa-solid fa-trash me-2"></i>Xóa bài viết
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Nội dung chữ --}}
                <p class="mb-3">{{ $post->content }}</p>

               {{-- Nội dung ảnh (Nếu có nhiều ảnh) --}}
@if($post->media && $post->media->count() > 0)
    <div class="rounded-4 overflow-hidden border">
        <div class="row g-1"> {{-- g-1 tạo khoảng cách nhỏ giữa các ảnh --}}
            @foreach($post->media as $media)
                {{-- Nếu có 1 ảnh thì hiện full, nếu nhiều hơn thì chia đôi --}}
                <div class="{{ $post->media->count() == 1 ? 'col-12' : 'col-6' }}">
                    @if($post->media->count() > 1)
                        <img src="{{ asset($media->url) }}" class="img-fluid w-100" style="height: 200px; object-fit: cover;" alt="Ảnh bài viết">
                    @else
                        <img src="{{ asset($media->url) }}" class="img-fluid w-100" style="object-fit: cover;" alt="Ảnh bài viết">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
                
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="fa-regular fa-folder-open fa-3x mb-3 text-light"></i>
            <p>Bạn chưa đăng bài viết nào.</p>
            <a href="{{ route('posts3.create') }}" class="btn btn-primary rounded-pill mt-2">Tạo bài viết đầu tiên</a>
        </div>
    @endforelse

</div>
@endsection