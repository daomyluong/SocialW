@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    {{-- ======================================================= --}}
    {{-- HIỂN THỊ THÔNG BÁO THÀNH CÔNG (NẾU CÓ)                  --}}
    {{-- ======================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 15px;" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- PHẦN 1: KHUNG ĐĂNG BÀI NHANH (ĐÃ SỬA LỖI MẤT HÌNH)      --}}
    {{-- ======================================================= --}}
    <div class="card mb-4 border-0 border-bottom">
        <div class="card-body">
            <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="d-flex">
                    <div class="avatar bg-light rounded-circle me-3" style="width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user fa-xl text-secondary"></i>
                    </div>
                    <div class="w-100">
                        {{-- Ô nhập nội dung --}}
                        <input type="text" name="content" class="form-control border-0 bg-light" style="border-radius: 20px;" placeholder="Bạn đang nghĩ gì, {{ Auth::user()->display_name ?? 'Thanh' }}?" required>
                        
                        <input type="hidden" name="visibility" value="public">

                        {{-- KHU VỰC XEM TRƯỚC ẢNH KHI CHỌN --}}
                        <div id="homeImagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3 text-primary">
                                {{-- Nút tải ảnh - Quan trọng: Đã đổi tên thành image[] và thêm multiple --}}
                                <label for="homePostImage" class="mb-0" style="cursor: pointer;">
                                    <small><i class="fa-regular fa-image me-1"></i> Ảnh/Video</small>
                                </label>
                                <input type="file" name="image[]" id="homePostImage" class="d-none" accept="image/*" multiple>
                                
                                <small style="cursor: pointer;"><i class="fa-solid fa-at me-1"></i> Nhắc tên</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Đăng</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h5 class="fw-bold mb-4">Dành cho bạn</h5>

    {{-- ======================================================= --}}
    {{-- PHẦN 2: DANH SÁCH BÀI VIẾT LẤY TỪ DATABASE              --}}
    {{-- ======================================================= --}}
    @forelse($posts as $post)
        <div class="post-item mb-4 border-bottom pb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center">
                    {{-- Avatar người đăng --}}
                    <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle me-2" width="40" height="40">
                    <div>
                        <span class="fw-bold">User #{{ $post->author_user_id }}</span>
                        <small class="text-muted d-block">{{ $post->created_at->diffForHumans() }}</small>
                    </div>
                </div>

                {{-- Nút menu sửa/xóa cho chủ bài viết --}}
                @if(Auth::id() == $post->author_user_id)
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" data-bs-toggle="dropdown" style="text-decoration: none;">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><a class="dropdown-item" href="{{ route('posts3.edit', $post->id) }}"><i class="fa-solid fa-pen me-2"></i>Chỉnh sửa</a></li>
                        <li>
                            <form action="{{ route('posts3.destroy', $post->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Bạn có chắc muốn xóa?')"><i class="fa-solid fa-trash me-2"></i>Xóa bài</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            <div class="post-content ps-5">
                <p>{{ $post->content }}</p>

                {{-- HIỂN THỊ HÌNH ẢNH THẬT --}}
                @if($post->media && $post->media->count() > 0)
                    <div class="rounded-4 overflow-hidden border mb-3">
                        <div class="row g-1">
                            @foreach($post->media as $m)
                                <div class="{{ $post->media->count() == 1 ? 'col-12' : 'col-6' }}">
                                    <img src="{{ asset($m->url) }}" class="img-fluid w-100" style="height: 250px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="post-actions d-flex gap-4 text-secondary">
                    <span><i class="fa-regular fa-heart me-1"></i> Thích</span>
                    <span><i class="fa-regular fa-comment me-1"></i> Bình luận</span>
                    <span><i class="fa-solid fa-share me-1"></i></span>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-muted py-5">Chưa có bài viết nào được đăng.</p>
    @endforelse
</div>

{{-- CSS hỗ trợ phần xem trước ảnh --}}
<style>
    .preview-box { position: relative; width: 60px; height: 60px; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .remove-btn { 
        position: absolute; top: -5px; right: -5px; background: red; color: white; 
        border-radius: 50%; width: 18px; height: 18px; font-size: 10px; 
        display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid white;
    }
</style>

{{-- SCRIPT xử lý chọn và xem trước ảnh (giúp input file luôn đúng định dạng) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inp = document.getElementById('homePostImage');
    const pre = document.getElementById('homeImagePreview');
    let dt = new DataTransfer();

    if(inp) {
        inp.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                if(!file.type.match('image.*')) return;
                dt.items.add(file);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-box';
                    div.innerHTML = `<img src="${e.target.result}"><span class="remove-btn">&times;</span>`;
                    div.querySelector('.remove-btn').onclick = () => {
                        div.remove();
                        let ndt = new DataTransfer();
                        Array.from(dt.files).filter(f => f !== file).forEach(f => ndt.items.add(f));
                        dt = ndt;
                        inp.files = dt.files;
                    };
                    pre.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
            inp.files = dt.files;
        });
    }
});
</script>
@endsection

@section('suggestions')
    <div class="px-2">
        <h6 class="fw-bold text-secondary mb-3">Gợi ý cho bạn</h6>
        @if(isset($suggestedUsers) && $suggestedUsers->count() > 0)
            <div class="d-flex flex-column gap-3">
                @foreach($suggestedUsers as $user)
                    <div class="d-flex align-items-center justify-content-between">
                        
                        <div class="d-flex align-items-center">
                            {{-- Sử dụng ảnh avatar mẫu hoặc avatar thật từ DB --}}
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=random&color=fff" 
                                 class="rounded-circle me-2" 
                                 width="40" height="40" 
                                 alt="{{ $user->username }}">
                            
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    {{ $user->username }}
                                </span>
                                <span class="text-muted" style="font-size: 0.8rem;">
                                    {{ $user->display_name }}
                                </span>
                            </div>
                        </div>

                        <div>
                            {{-- Tái sử dụng component nút Follow của bạn, hoặc dùng trực tiếp Form ở đây --}}
                            <form action="{{ route('users.follow', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-dark btn-sm rounded-pill fw-bold px-3">
                                    Theo dõi
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            {{-- Hiển thị nếu không có dữ liệu hoặc đã follow hết mọi người --}}
            <p class="text-muted small">Hiện chưa có gợi ý mới nào.</p>
        @endif
    </div>
@endsection