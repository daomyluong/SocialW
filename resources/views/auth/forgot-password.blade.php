@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">Khôi phục mật khẩu</h4>
                    
                    @if (session('status'))
                        <div class="alert alert-success small">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email của bạn</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <span class="text-danger small text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Gửi link khôi phục
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection