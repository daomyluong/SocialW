@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    <div class="card mb-4 border-0 border-bottom">
        <div class="card-body d-flex">
            <div class="avatar bg-light rounded-circle me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="fa-solid fa-user fa-xl text-secondary" style="line-height: 50px; margin-left: 15px;"></i>
            </div>
            <div class="w-100">
                <input type="text" class="form-control border-0 bg-light" style="border-radius: 20px;" placeholder="Bạn đang nghĩ gì, {{ Auth::user()->display_name ?? 'Đào' }}?">
                <div class="mt-2 d-flex gap-3 text-primary">
                    <small><i class="fa-regular fa-image me-1"></i> Ảnh/Video</small>
                    <small><i class="fa-solid fa-at me-1"></i> Nhắc tên</small>
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
    <p class="px-2 text-muted small">Danh sách gợi ý sẽ do Quỳnh (TV4) phụ trách.</p>
@endsection