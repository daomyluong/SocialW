@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-center fw-bold mb-4">Tạo tài khoản mới</h4>
                    
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tên hiển thị</label>
                            <input type="text" name="display_name" value="{{ old('display_name') }}" class="form-control" placeholder="Ví dụ: Nguyễn Văn A" required autofocus>
                            @error('display_name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập (Username)</label>
                            <input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="nguyenvana" required>
                            @error('username')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@example.com" required>
                            @error('email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                            @error('password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold">Đăng ký</button>
                        
                        <div class="text-center mt-3">
                            <small>Đã có tài khoản? <a href="{{ route('login') }}" class="text-decoration-none">Đăng nhập ngay</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection