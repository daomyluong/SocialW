@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    {{-- PHẦN XỬ LÝ LOGIC DỮ LIỆU SẠCH --}}
    @php
        // 1. Chỉ lấy những bookmark CHƯA bị xóa mềm (is_deleted = 0) 
        // và bài viết gốc phải còn tồn tại (post != null)
        $activeBookmarks = collect($bookmarks)->filter(function($bm) {
            return $bm->is_deleted == 0 && $bm->post != null;
        });

        // 2. Lấy danh sách tên folder duy nhất từ những bài chưa xóa
        $folders = $activeBookmarks->pluck('folder_name')->unique()->filter()->sort();

        // 3. Lọc bài viết theo folder từ URL (?folder=...)
        $selectedFolder = request('folder');
        $displayItems = $selectedFolder 
            ? $activeBookmarks->where('folder_name', $selectedFolder) 
            : $activeBookmarks;
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
        <h6 class="text-muted fw-bold small text-uppercase mb-3" style="letter-spacing: 1px;">Thư mục của bạn</h6>
        <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
            <style>.d-flex::-webkit-scrollbar { display: none; }</style>
            
            {{-- Nút Tất cả --}}
            <a href="{{ route('bookmarks.index') }}" 
               class="btn btn-sm {{ !$selectedFolder ? 'btn-primary shadow' : 'btn-outline-secondary' }} rounded-pill px-3 border-0">
                📂 Tất cả
            </a>

            @foreach($folders as $fName)
                @if($fName != 'Tất cả')
                <a href="{{ route('bookmarks.index', ['folder' => $fName]) }}" 
                   class="btn btn-sm {{ $selectedFolder == $fName ? 'btn-primary shadow' : 'btn-outline-secondary' }} rounded-pill px-3 text-nowrap border-0">
                    📁 {{ $fName }}
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
                                <img src="https://ui-avatars.com/api/?name=User{{ $bm->post->author_user_id }}&background=random" 
                                     class="rounded-circle me-2 border" width="40" height="40">
                                <div>
                                    <div class="fw-bold small">User #{{ $bm->post->author_user_id }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $bm->updated_at->diffForHumans() }}</small>
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
                        @if($bm->post->media && $bm->post->media->count() > 0)
                            <div class="rounded-4 overflow-hidden border mb-3">
                                <img src="{{ asset($bm->post->media->first()->url) }}" class="w-100" style="max-height: 350px; object-fit: cover;">
                            </div>
                        @endif

                        {{-- Nút bấm --}}
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                            {{-- Sửa link trỏ về trang chủ kèm theo ID của bài viết --}}
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
    if(!confirm('Bạn muốn bỏ lưu bài viết này?')) return;

    fetch(`/bookmarks/toggle/${postId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ folder_name: 'Tất cả' })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'removed') {
            const item = document.getElementById(`bm-item-${postId}`);
            item.style.transition = '0.4s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.remove();
                // Tải lại trang để cập nhật Badge và Trạng thái trống
                window.location.reload();
            }, 400);
        }
    })
    .catch(err => alert('Lỗi kết nối server.'));
}
</script>
@endsection