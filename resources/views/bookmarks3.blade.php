@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    {{-- PHẦN XỬ LÝ LOGIC DỮ LIỆU --}}
    @php
    // 1. Lấy tất cả bookmark (đã lọc is_deleted = 0 ở controller)
    $allBookmarks = collect($bookmarks);

    // 2. Lấy danh sách tên folder duy nhất
    $folders = $allBookmarks->pluck('folder_name')->unique()->filter()->sort();

    // 3. Lọc bài viết theo folder từ URL (?folder=...)
    $selectedFolder = request('folder');
    $displayItems = $selectedFolder
        ? $allBookmarks->where('folder_name', $selectedFolder)
        : $allBookmarks;
    @endphp

    {{-- Tiêu đề & Tổng số lượng chuẩn --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-bookmark me-2 text-primary"></i>Mục đã lưu
        </h4>
        <span class="badge bg-white text-primary rounded-pill border px-3 py-2 shadow-sm">
            {{ $displayItems->count() }} bài viết
        </span>
    </div>

    {{-- PHẦN 1: THANH CHỌN THƯ MỤC --}}
    <div class="mb-4">
        <h6 class="text-muted fw-bold small text-uppercase mb-3">THƯ MỤC CỦA BẠN</h6>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Nút Tất cả --}}
            <a href="{{ route('bookmarks.index') }}"
                class="btn btn-sm {{ !request('folder') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                📂 Tất cả
            </a>

            {{-- Vòng lặp hiển thị thư mục động --}}
            @foreach($allFolders as $folder)
            @if($folder != 'Tất cả')
            <a href="{{ route('bookmarks.index', ['folder' => $folder]) }}"
                class="btn btn-sm {{ request('folder') == $folder ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                📁 {{ $folder }}
            </a>
            @endif
            @endforeach
        </div>
    </div>

    <hr class="opacity-10 mb-4">

    {{-- PHẦN 2: DANH SÁCH BÀI VIẾT --}}
    <div class="row g-3" id="bookmark-container">
        @forelse($displayItems as $bm)
        <div class="col-12 bookmark-item" id="bm-item-{{ $bm->post_id }}">
            <div class="card border-0 shadow-sm mb-2" style="border-radius: 20px;">
                <div class="card-body p-4">
                    {{-- Header bài viết --}}
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <img src="{{ $bm->post->author?->avatar_url 
    ? asset($bm->post->author->avatar_url) 
    : 'https://ui-avatars.com/api/?name=' . urlencode($bm->post->author?->display_name ?? $bm->post->author?->username ?? 'User') 
}}"
                                class="rounded-circle me-2 border" width="40" height="40">

                            <div class="fw-bold small">
                                {{ $bm->post->author?->display_name ?? $bm->post->author?->username ?? 'User #' . $bm->post->user_id }}
                            </div>
                        </div>
                        <span class="badge bg-light text-muted fw-normal border-0">
                            <i class="fa-regular fa-folder me-1"></i>{{ $bm->folder_name }}
                        </span>
                    </div>

                    {{-- Nội dung chữ --}}
                    <p class="text-dark mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                        {{ $bm->post->content }}
                    </p>

                    {{-- Media ảnh --}}
                    @if($bm->post && $bm->post->media && $bm->post->media->isNotEmpty())
                    @foreach($bm->post->media as $media)
                    @if($media->type === 'video')
                    <div class="rounded-4 overflow-hidden border mb-3">
                        <video controls class="w-100" style="max-height: 350px;">
                            <source src="{{ asset('storage/' . $media->url) }}">
                        </video>
                    </div>
                    @else
                    <div class="rounded-4 overflow-hidden border mb-3">
                        <img src="{{ asset('storage/' . $media->url) }}"
                            class="w-100"
                            style="max-height: 350px; object-fit: cover;">
                    </div>
                    @endif
                    @endforeach
                    @endif

                    {{-- Nút bấm --}}
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <a href="{{ route('home') }}#post-{{ $bm->post_id }}" class="text-primary text-decoration-none small fw-bold">
                            Xem bài viết gốc <i class="fa-solid fa-arrow-right ms-1" style="font-size: 0.7rem;"></i>
                        </a>
                        <button data-post-id="{{ $bm->post_id }}" onclick="handleRemoveBookmark(this.dataset.postId, this)" class="btn btn-link text-danger text-decoration-none p-0 small fw-bold">
                            <i class="fa-solid fa-trash-can me-1"></i> Bỏ lưu
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        {{-- Giao diện khi rỗng --}}
        <div class="text-center py-5">
            <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 80px; height: 80px;">
                <i class="fa-regular fa-bookmark fa-2x text-muted"></i>
            </div>
            <h5 class="fw-bold text-dark">Chưa có bài viết nào</h5>
            <p class="text-muted small">Hãy lưu những bài viết thú vị để xem lại sau nhé!</p>
            {{-- ĐÃ SỬA: Đổi từ posts3.index sang home --}}
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 mt-2">Khám phá ngay</a>
        </div>
        @endforelse
    </div>
</div>

<script>
    function handleRemoveBookmark(postId, btn) {
        if (!confirm('Bạn muốn bỏ lưu bài viết này?')) return;

        fetch(`/bookmarks/toggle/${postId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    folder_name: 'Tất cả',
                    action: 'remove'
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log('Response:', data);
                if (data.status === 'removed') {
                    const item = document.getElementById(`bm-item-${postId}`);
                    if (item) {
                        item.style.transition = '0.4s ease';
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';

                        setTimeout(() => {
                            item.remove();
                            window.location.reload();
                        }, 400);
                    } else {
                        console.error('Item not found:', `bm-item-${postId}`);
                        window.location.reload();
                    }
                } else {
                    alert('Không thể bỏ lưu: ' + (data.error || data.status));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Lỗi kết nối server.');
            });
    }
</script>
@endsection