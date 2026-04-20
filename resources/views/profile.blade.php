@extends('layouts.app')

@section('title', 'Hồ sơ')

@section('content')
    <div class="container" style="max-width: 700px;">
        <h4 class="fw-bold mb-3">Hồ sơ người dùng</h4>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="mb-1"><span class="fw-semibold">Display name:</span> {{ auth()->user()?->display_name ?? 'Guest' }}</p>
                <p class="mb-1"><span class="fw-semibold">Username:</span> {{ auth()->user()?->username ?? 'guest' }}</p>
                <p class="mb-0 text-muted">TV2: hoàn thiện trang profile, chỉnh sửa thông tin và bảo mật tài khoản.</p>
            </div>
        </div>
    </div>
@endsection

@section('suggestions')
    <p class="px-2 text-muted small">TV2: thêm avatar upload, đổi mật khẩu, xác minh email.</p>
@endsection
