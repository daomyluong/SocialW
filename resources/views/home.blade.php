@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    @guest
        <div class="alert alert-light border shadow-sm mb-4 d-flex justify-content-between align-items-center">
            <span>Chào mừng bạn đến với W-Social!</span>
            <div>
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary me-2">Đăng nhập</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Đăng ký</a>
                @endif
            </div>
        </div>
    @endguest

    <div class="card mb-4 border-0 border-bottom shadow-sm">
        <div class="card-body d-flex">
            <div class="avatar bg-light rounded-circle me-3" style="width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-user fa-xl text-secondary"></i>
            </div>
            <div class="w-100">
                <input type="text" class="form-control border-0 bg-light" style="border-radius: 20px;" 
                       placeholder="Bạn đang nghĩ gì, {{ Auth::check() ? Auth::user()->display_name : 'Đào' }}?">
                <div class="mt-2 d-flex gap-3 text-primary">
                    <small role="button"><i class="fa-regular fa-image me-1"></i> Ảnh/Video</small>
                    <small role="button"><i class="fa-solid fa-at me-1"></i> Nhắc tên</small>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-4">Dành cho bạn</h5>

    <div class="post-item mb-4 border-bottom pb-3">
        <div class="d-flex align-items-center mb-2">
            <img src="https://ui-avatars.com/api/?name=Tuan+MIS&background=0D8ABC&color=fff" class="rounded-circle me-2" width="40" height="40">
            <div>
                <span class="fw-bold">tuan_mis</span>
                <small class="text-muted d-block">2 giờ trước</small>
            </div>
        </div>
        <div class="post-content ps-5">
            <p>Hệ thống W-Social bắt đầu chạy thử nghiệm Layout hôm nay! Mọi người thấy giao diện mới thế nào? 🚀</p>
            <div class="rounded-4 overflow-hidden border mb-3">
                <img src="https://via.placeholder.com/600x400" class="img-fluid w-100" alt="post image">
            </div>
            <div class="post-actions d-flex gap-4 text-secondary">
                <span><i class="fa-regular fa-heart me-1"></i> 12</span>
                <span><i class="fa-regular fa-comment me-1"></i> 5</span>
                <span><i class="fa-solid fa-share me-1"></i></span>
            </div>
        </div>
    </div>

    <div class="post-item mb-4 border-bottom pb-3">
        <div class="d-flex align-items-center mb-2">
            <img src="https://ui-avatars.com/api/?name=Lan+HCMUB&background=702963&color=fff" class="rounded-circle me-2" width="40" height="40">
            <div>
                <span class="fw-bold">lan_hcmub</span>
                <small class="text-muted d-block">5 giờ trước</small>
            </div>
        </div>
        <div class="post-content ps-5">
            <p>Nhóm 1 - bài tập nhóm - mạng xã hội ❤️</p>
            <div class="post-actions d-flex gap-4 text-secondary">
                <span><i class="fa-regular fa-heart me-1"></i> 45</span>
                <span><i class="fa-regular fa-comment me-1"></i> 12</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('suggestions')
    <div class="p-3">
        @auth
            <div class="mb-4">
                <a href="{{ url('/dashboard') }}" class="btn btn-primary w-100">Đến Bảng điều khiển</a>
            </div>
        @endauth
        
        <p class="text-muted small">Danh sách gợi ý sẽ do Quỳnh (TV4) phụ trách.</p>
    </div>
@endsection