@extends('layouts.admin')

{{-- Tên trang hiển thị trên Top Banner --}}
@section('admin_title', 'Test Layout Quản Trị')

@section('content')
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body text-center py-5">
            <h2 class="text-success fw-bold">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Tuyệt vời!
            </h2>
            <h5 class="text-secondary mt-3">Layout Admin của cậu đã hoạt động hoàn hảo!</h5>
            <p class="text-muted">Sidebar bên trái, Banner bên trên và khu vực không gian rộng rãi này đã sẵn sàng để hiển thị danh sách người dùng và bài viết.</p>
        </div>
    </div>
@endsection