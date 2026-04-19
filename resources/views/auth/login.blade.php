@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="text-center mb-4">Đăng nhập</h4>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Mật khẩu</label>
                               
                            </div>
                            <input type="password" name="password" class="form-control mt-1" required autocomplete="current-password">
                            @error('password')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember_me">
                            <label class="form-check-label small" for="remember_me">Ghi nhớ đăng nhập</label>
                        </div>
 @if (Route::has('password.request'))
                                    <a class="text-decoration-none small" href="{{ route('password.request') }}">
                                        Quên mật khẩu?
                                    </a>
                                @endif
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection