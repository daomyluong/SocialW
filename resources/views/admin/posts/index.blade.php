@extends('layouts.admin')

@section('admin_title', 'Quản Lý Bài Viết')

@section('content')
<style>
    /* Style đồng bộ Pastel & Glassmorphism */
    .card-glass { border-radius: 1.25rem; border: none; background: rgba(255, 255, 255, 0.9); box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05); }
    .thumb-preview { width: 80px; height: 50px; object-fit: cover; border-radius: 0.75rem; background: #f1f2f6; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .bg-soft-green { background-color: #eafaf1; color: #20bf6b; }
    .bg-soft-blue { background-color: #f0f4fd; color: #4b7bec; }
    .bg-soft-yellow { background-color: #fef9e7; color: #f1c40f; }
    .table-custom td { vertical-align: middle; padding: 15px 20px; border-bottom: 1px solid #f1f2f6; }
    .stat-badge { font-size: 0.75rem; font-weight: 600; color: #747d8c; }
</style>

<div class="container-fluid px-0">

    <div class="card card-glass mb-4 p-3">
        <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control border-0 bg-light rounded-pill px-3" placeholder="Tìm nội dung hoặc người đăng..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="visibility" class="form-select border-0 bg-light rounded-pill">
                    <option value="">Chế độ</option>
                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Công khai</option>
                    <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Riêng tư</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select border-0 bg-light rounded-pill">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="hot" {{ request('sort') == 'hot' ? 'selected' : '' }}>Tương tác nhất</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Lọc</button>
            </div>
        </form>
    </div>

    <div class="card card-glass overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4">Tác giả</th>
                        <th class="border-0">Preview</th>
                        <th class="border-0">Nội dung</th>
                        <th class="border-0">Tương tác</th>
                        <th class="border-0">Ngày đăng</th>
                        <th class="border-0 text-end px-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admin_posts as $post)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($post->author_name) }}&background=random" class="rounded-circle me-2" width="35" height="35">
                                <div>
                                    <span class="d-block fw-bold text-dark" style="font-size: 0.85rem;">{{ $post->author_name }}</span>
                                    <small class="text-muted" style="font-size: 0.7rem;">ID: #{{ $post->author_user_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="thumb-preview">
                                {{-- Dùng isset() để kiểm tra xem cột có tồn tại trong Database không trước khi gọi --}}
                                @if(isset($post->image_url) && $post->image_url)
                                    <img src="{{ $post->image_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @elseif(isset($post->video_url) && $post->video_url)
                                    <i class="fa-solid fa-circle-play fa-xl text-primary"></i>
                                @else
                                    <i class="fa-solid fa-quote-left text-muted opacity-25"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-dark fw-medium" style="font-size: 0.85rem; max-width: 250px;">
                                {{ Str::limit($post->content, 60) }}
                                <br>
                                <span class="badge {{ $post->visibility == 'public' ? 'bg-soft-green' : 'bg-soft-yellow' }} rounded-pill mt-1" style="font-size: 0.65rem;">
                                    {{ ucfirst($post->visibility) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="stat-badge"><i class="fa-solid fa-heart text-danger me-1"></i> {{ $post->like_count }} Likes</span>
                                <span class="stat-badge"><i class="fa-solid fa-comment text-primary me-1"></i> {{ $post->comment_count }} Cmt</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($post->created_at)->format('H:i') }}</span><br>
                            <span class="text-dark fw-bold" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}</span>
                        </td>
                        <td class="text-end px-4">
                            <a href="#" class="btn btn-light btn-sm rounded-pill me-1"><i class="fa-solid fa-eye text-primary"></i></a>
                            <form action="{{ route('admin.posts.delete', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa bài viết này?');">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm rounded-pill"><i class="fa-solid fa-trash text-danger"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Không tìm thấy bài viết nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center p-3">
            {{ $admin_posts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection