@extends('layouts.admin')

@section('admin_title', 'Quản Lý Bình Luận')

@section('content')
<style>
    .card-glass { border-radius: 1.25rem; border: none; background: rgba(255, 255, 255, 0.9); box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05); }
    .table-custom td { vertical-align: middle; padding: 12px 20px; border-bottom: 1px solid #f1f2f6; }
    .post-ref { font-size: 0.75rem; color: #8854d0; background: #f6f0fb; padding: 4px 8px; border-radius: 6px; display: inline-block; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none;}
    .post-ref:hover { background: #ecdffd; }
    
    /* Bổ sung CSS cho các nút thao tác */
    .action-btn { width: 32px; height: 32px; border-radius: 0.5rem; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; font-size: 0.85rem; text-decoration: none;}
    .action-btn:hover { transform: translateY(-2px); opacity: 0.8; }
    .bg-soft-blue { background-color: #eef6ff; color: #0062ff; }
    .bg-soft-red { background-color: #fcebeb; color: #dc3545; }
</style>

<div class="container-fluid px-0">

    <div class="card card-glass mb-4 p-3">
        <form action="{{ route('admin.comments.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted rounded-start-pill ps-4"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-light rounded-end-pill" placeholder="Tìm nội dung bình luận hoặc người đăng..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Tìm kiếm</button>
            </div>
        </form>
    </div>

    <div class="card card-glass overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4">Người bình luận</th>
                        <th class="border-0">Nội dung</th>
                        <th class="border-0">Bài viết gốc</th>
                        <th class="border-0">Thời gian</th>
                        <th class="border-0 text-end px-4" style="min-width: 150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admin_comments as $comment)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name) }}&background=random" class="rounded-circle me-2" width="30" height="30">
                                <span class="fw-bold {{ isset($comment->user_status) && $comment->user_status == 0 ? 'text-decoration-line-through text-muted' : 'text-dark' }}" style="font-size: 0.85rem;">
                                    {{ $comment->author_name }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="text-dark" style="font-size: 0.85rem; max-width: 300px;">
                                "{{ $comment->content }}"
                            </div>
                        </td>
                        <td>
                            <a href="#" class="post-ref" title="{{ $comment->post_content }}">
                                <i class="fa-solid fa-link me-1"></i> {{ $comment->post_content }}
                            </a>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size: 0.8rem;">
                                {{ \Carbon\Carbon::parse($comment->created_at)->format('H:i - d/m/Y') }}
                            </span>
                        </td>
                        <td class="text-end px-4">
                            <a href="#" class="action-btn bg-soft-blue text-primary me-1" title="Xem tại bài viết"><i class="fa-solid fa-up-right-from-square"></i></a>
                            
                            @if(isset($comment->user_status) && $comment->user_status == 1)
                            <form action="{{ route('admin.comments.quick_ban', $comment->author_user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Khóa tài khoản người này ngay lập tức?');">
                                @csrf
                                <button type="submit" class="action-btn bg-soft-red text-danger me-1" title="Khóa người dùng này"><i class="fa-solid fa-user-slash"></i></button>
                            </form>
                            @endif

                            <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bình luận này?');">
                                @csrf
                                <button type="submit" class="action-btn bg-light text-muted" title="Xóa bình luận"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-comments fa-2x mb-3 opacity-25 d-block"></i>
                            Không có bình luận nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center p-3">
            {{ $admin_comments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection