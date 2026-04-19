@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 500px; margin-top: 50px;">
    <div class="card border-0 shadow-sm p-4" style="background: white; border-radius: 15px;">
        <h4 class="fw-bold mb-4 text-center">Chỉnh sửa trang cá nhân</h4>
        
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên hiển thị</label>
                <input type="text" name="display_name" class="form-control shadow-none" 
                       value="{{ $user->display_name }}" style="border-radius: 8px;">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Tiểu sử</label>
                <textarea name="bio" class="form-control shadow-none" rows="3" 
                          style="border-radius: 8px;">{{ $user->bio }}</textarea>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-dark fw-bold" style="border-radius: 8px; padding: 10px;">
                    Lưu thay đổi
                </button>
                <a href="{{ route('profile') }}" class="btn btn-link text-decoration-none text-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection