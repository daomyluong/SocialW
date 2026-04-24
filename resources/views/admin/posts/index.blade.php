@extends('layouts.admin')


@section('admin_title', 'Quản Lý Bài Viết')


@section('content')
<style>
    :root {
        --hlink-blue: #4facfe;
        --hlink-green: #43e97b;
        --hlink-bg: #f0f4f8;
        --grad-primary: linear-gradient(135deg, var(--hlink-green) 0%, var(--hlink-blue) 100%);
        --accent-teal: #00f2fe;
        --accent-coral: #ff9a9e;
        --soft-radius: 1.25rem;
        --soft-shadow: 0 10px 30px rgba(79, 172, 254, 0.12);
        --soft-shadow-hover: 0 15px 35px rgba(79, 172, 254, 0.22);
    }


    body { background-color: var(--hlink-bg); }


    .card-soft {
        background: #ffffff;
        border: none;
        border-radius: var(--soft-radius);
        box-shadow: var(--soft-shadow);
        transition: 0.3s;
    }


    .card-soft:hover { box-shadow: var(--soft-shadow-hover); }


    .filter-pill, .search-soft {
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-weight: 500;
    }


    .filter-pill:focus, .search-soft:focus { box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2); }


    .table-soft { font-size: 0.92rem; color: #334155; }


    .table-soft th {
        border-bottom: 2px solid #f1f5f9;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem;
    }


    .table-soft td {
        vertical-align: middle;
        padding: 1rem;
        border-bottom: 1px solid #f8fafc;
        color: #334155;
    }


    .table-soft tbody tr { transition: 0.2s; }
    .table-soft tbody tr:hover { background-color: #f8fafc; transform: translateY(-1px); }


    .btn-grad {
        background: var(--grad-primary);
        border: none;
        color: white;
        border-radius: 2rem;
        padding: 8px 20px;
        font-weight: 600;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3);
    }


    .btn-grad:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(67, 233, 123, 0.4);
        color: white;
    }


    .btn-grad-soft {
        background: rgba(79, 172, 254, 0.12);
        color: #1d4ed8;
        border: 1px solid rgba(79, 172, 254, 0.25);
        box-shadow: none;
    }


    .btn-grad-soft:hover { background: rgba(79, 172, 254, 0.18); }


    .media-preview {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(79, 172, 254, 0.12);
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
        border: 1px solid rgba(79, 172, 254, 0.2);
    }


    .stat-chip {
        border-radius: 999px;
        padding: 5px 12px;
        background: rgba(79, 172, 254, 0.12);
        color: #1e40af;
        border: 1px solid rgba(79, 172, 254, 0.2);
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }


    .badge-status-clean {
        background-color: #f0fdfa;
        color: #0f766e;
        border: 1px solid var(--accent-teal);
    }


    .badge-status-intervened {
        background-color: #fff1f2;
        color: #be123c;
        border: 1px solid var(--accent-coral);
    }


    .admin-modal .modal-dialog {
        max-width: 1220px;
    }


    .admin-modal-shell {
        border-radius: 1.25rem;
        border: 1px solid rgba(79, 172, 254, 0.18);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(16px);
        box-shadow: 0 28px 80px rgba(79, 172, 254, 0.2);
        overflow: hidden;
    }


    .admin-modal-header {
        padding: 1.15rem 1.5rem;
        background: linear-gradient(180deg, rgba(79, 172, 254, 0.1) 0%, rgba(255, 255, 255, 0.9) 100%);
        border: none;
    }


    .admin-modal-title {
        color: #1f2937;
        font-weight: 800;
        letter-spacing: 0.01em;
    }


    .admin-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #94a3b8;
        transition: 0.2s;
    }


    .admin-modal-close:hover {
        color: #f87171;
        border-color: rgba(248, 113, 113, 0.4);
        background: #fff1f2;
    }


    .admin-modal-body {
        padding: 1.35rem;
        min-height: 560px;
    }


    .modal-panel-soft {
        border-radius: 1rem;
        border: 1px solid rgba(79, 172, 254, 0.14);
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        height: 100%;
    }


    .review-content-box {
        border-radius: 0.9rem;
        border: 1px solid #dbeafe;
        background: #ffffff;
        padding: 0.95rem 1rem;
        line-height: 1.6;
        color: #1f2937;
        white-space: pre-wrap;
    }


    .review-media-box {
        border-radius: 1rem;
        border: 1px solid #dbeafe;
        background: #0f172a;
        min-height: 320px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .review-media-box img,
    .review-media-box video {
        width: 100%;
        max-height: 520px;
        object-fit: contain;
    }


    .report-item {
        border-radius: 0.9rem;
        border: 1px solid #e5efff;
        background: #f8fbff;
        padding: 0.8rem 0.9rem;
    }


    .report-item + .report-item { margin-top: 0.65rem; }


    .admin-modal-footer {
        border: none;
        padding: 1rem 1.5rem 1.35rem;
        background: transparent;
    }


    .btn-confirm-grad {
        border-radius: 999px;
        border: none;
        background: var(--grad-primary);
        color: #fff;
        padding: 0.65rem 1.6rem;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.22);
        transition: 0.25s;
    }


    .btn-confirm-grad:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(79, 172, 254, 0.28);
        color: #fff;
    }


    .btn-action-soft {
        border-radius: 999px;
        border: 1px solid rgba(79, 172, 254, 0.25);
        background: rgba(79, 172, 254, 0.1);
        color: #2563eb;
        font-weight: 600;
        padding: 0.48rem 1rem;
        font-size: 0.86rem;
    }


    .btn-action-soft:hover {
        background: rgba(79, 172, 254, 0.18);
        color: #1d4ed8;
    }


    .btn-hide-soft {
        border-radius: 999px;
        border: 1px solid rgba(249, 115, 22, 0.35);
        background: rgba(249, 115, 22, 0.1);
        color: #c2410c;
        font-weight: 700;
        padding: 0.56rem 1.1rem;
    }


    .btn-hide-soft:hover {
        background: rgba(249, 115, 22, 0.16);
        color: #9a3412;
    }


    .btn-delete-soft {
        border-radius: 999px;
        border: 1px solid rgba(244, 63, 94, 0.3);
        background: rgba(244, 63, 94, 0.1);
        color: #be123c;
        font-weight: 700;
        padding: 0.56rem 1.1rem;
    }


    .btn-delete-soft:hover {
        background: rgba(244, 63, 94, 0.16);
        color: #9f1239;
    }


    .btn-review {
        border-radius: 999px;
        padding: 0.48rem 1rem;
        font-weight: 700;
        font-size: 0.86rem;
        color: #fff;
        background: #38bdf8;
        border: none;
    }


    .btn-review:hover { color: #fff; background: #0ea5e9; }


    .badge-visibility-public {
        background: #f0fdfa;
        color: #0f766e;
        border: 1px solid var(--accent-teal);
    }


    .badge-visibility-private {
        background: #fef9e7;
        color: #b45309;
        border: 1px solid #facc15;
    }
</style>


<div class="container-fluid px-0">


    <div class="card card-soft mb-4 p-3">
        <form action="{{ route('admin.posts.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-lg-3 col-md-6">
                <div class="input-group">
                    <span class="input-group-text search-soft border-0 text-muted rounded-start-pill ps-4"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control search-soft border-0 rounded-end-pill" placeholder="Tìm nội dung hoặc người đăng..." value="{{ request('search') }}">
                </div>
            </div>


            <div class="col-lg-2 col-md-6">
                <select name="visibility" class="form-select filter-pill w-100">
                    <option value="">Chế độ</option>
                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Công khai</option>
                    <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Riêng tư</option>
                </select>
            </div>


            <div class="col-lg-2 col-md-6">
                <select name="sort" class="form-select filter-pill w-100">
                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="hot" {{ request('sort') == 'hot' ? 'selected' : '' }}>Tương tác nhất</option>
                </select>
            </div>


            <div class="col-lg-3 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-grad w-100 fw-bold"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-grad-soft rounded-pill px-3" title="Thiết lập lại"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>


    <div class="card card-soft overflow-hidden">
        <div class="table-responsive">
            <table class="table table-soft mb-0">
                <thead>
                    <tr>
                        <th class="px-4 text-center">Tác giả</th>
                        <th class="text-center">Preview</th>
                        <th class="text-center" width="28%">Nội dung</th>
                        <th class="text-center">Tương tác</th>
                        <th class="text-center">Ngày đăng</th>
                        <th class="text-center px-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admin_posts as $post)
                    <tr>
                        <td class="px-4 text-start">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($post->author_name ?? 'User') }}&background=4facfe&color=fff" alt="Avatar" class="rounded-circle me-2" width="36" height="36">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $post->author_name }}</div>
                                    <small class="text-muted">USER #{{ $post->user_id }}</small>
                                </div>
                            </div>
                        </td>


                        <td class="text-center">
                            <div class="media-preview mx-auto">
                                @if(($post->media_type ?? null) === 'image' && ($post->media_url ?? null))
                                    <img src="{{ $post->media_url }}" alt="Preview" class="w-100 h-100" style="object-fit: cover;">
                                @elseif(($post->media_type ?? null) === 'video')
                                    <i class="fa-solid fa-circle-play fa-lg"></i>
                                @else
                                    <i class="fa-solid fa-align-left"></i>
                                @endif
                            </div>
                        </td>


                        <td class="text-start">
                            <div class="fw-semibold text-dark" style="font-size: 0.86rem;">{{ Str::limit($post->content, 70) }}</div>
                            <small class="text-muted d-block">POST #{{ $post->id }}</small>
                            <span class="badge {{ ($post->visibility ?? 'public') === 'public' ? 'badge-visibility-public' : 'badge-visibility-private' }} rounded-pill mt-1">
                                {{ ($post->visibility ?? 'public') === 'public' ? 'Công khai' : 'Riêng tư' }}
                            </span>
                        </td>


                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <span class="stat-chip"><i class="fa-solid fa-heart text-danger"></i> {{ $post->like_count }}</span>
                                <span class="stat-chip"><i class="fa-solid fa-comment text-primary"></i> {{ $post->comment_count }}</span>
                            </div>
                        </td>


                        <td class="text-center">
                            <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($post->created_at)->format('H:i') }}</div>
                            <div class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}</div>
                        </td>


                        <td class="text-center px-4">
                            <form action="{{ route('admin.posts.toggle_visibility', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thay đổi trạng thái bài viết?');">
                                @csrf
                                <button type="submit" class="btn btn-grad-soft rounded-pill px-3" title="{{ ($post->content_status ?? 'visible') === 'hidden' ? 'Hiện bài viết' : 'Ẩn bài viết' }}">
                                    <i class="fa-solid {{ ($post->content_status ?? 'visible') === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="btn btn-review" data-bs-toggle="modal" data-bs-target="#modalPostReview{{ $post->id }}">
                                Xem xét <i class="fa-solid fa-gavel ms-1"></i>
                            </button>
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


        <div class="d-flex justify-content-center p-4">
            {{ $admin_posts->links('vendor.pagination.admin-soft') }}
        </div>
    </div>
</div>


@foreach($admin_posts as $post)
    <div class="modal fade admin-modal" id="modalPostReview{{ $post->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-modal-shell">
                <div class="modal-header admin-modal-header">
                    <h5 class="modal-title admin-modal-title"><i class="fa-solid fa-file-shield me-2" style="color: var(--hlink-blue);"></i> Xem xét bài viết #{{ $post->id }}</h5>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>


                <div class="modal-body admin-modal-body">
                    <div class="row g-0">
                        <div class="col-lg-8 pe-lg-3 mb-3 mb-lg-0">
                            <div class="modal-panel-soft p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <h6 class="fw-bold text-muted mb-0">NỘI DUNG GỐC (70%)</h6>
                                    <span class="badge {{ ($post->visibility ?? 'public') === 'public' ? 'badge-visibility-public' : 'badge-visibility-private' }} rounded-pill px-3 py-2">
                                        {{ ($post->visibility ?? 'public') === 'public' ? 'Công khai' : 'Riêng tư' }}
                                    </span>
                                </div>


                                <div class="review-content-box mb-3">{{ $post->content ?: 'Bài viết không có nội dung chữ.' }}</div>


                                <div class="review-media-box">
                                    @if(($post->media_type ?? null) === 'image' && ($post->media_url ?? null))
                                        <img src="{{ $post->media_url }}" alt="Ảnh gốc bài viết">
                                    @elseif(($post->media_type ?? null) === 'video' && ($post->media_url ?? null))
                                        <video src="{{ $post->media_url }}" controls preload="metadata"></video>
                                    @else
                                        <div class="text-center text-white-50 px-4">
                                            <i class="fa-solid fa-photo-film fa-2x mb-2"></i>
                                            <div>Bài viết không có media đính kèm.</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-4 ps-lg-3">
                            <div class="modal-panel-soft p-4 h-100" style="max-height: 100%; min-height: 520px; overflow-y: auto;">
                                <h6 class="fw-bold text-muted mb-3">NHÂN THÂN NGƯỜI ĐĂNG (30%)</h6>


                                <div class="report-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($post->author_name ?? 'User') }}&background=4facfe&color=fff" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $post->author_name }}</div>
                                            <small class="text-muted">USER #{{ $post->user_id }}</small>
                                        </div>
                                    </div>
                                    <div class="small text-muted">Tham gia: {{ \Carbon\Carbon::parse($post->author_created_at)->diffForHumans() }}</div>
                                    <div class="small text-muted">Bài vi phạm trước đó: <span class="fw-bold text-dark">{{ $post->previous_violation_count }}</span></div>
                                    <div class="small text-muted">Báo cáo đang chờ xử lý: <span class="fw-bold text-dark">{{ $post->open_report_count }}</span></div>
                                </div>


                                <h6 class="fw-bold text-muted mb-2">DANH SÁCH BÁO CÁO</h6>
                                @forelse($post->report_entries as $entry)
                                    <div class="report-item">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-semibold" style="font-size: 0.85rem;">{{ $entry->reason }}</span>
                                            @if($entry->status === 'pending')
                                                <span class="badge badge-status-intervened rounded-pill">Chờ xử lý</span>
                                            @elseif($entry->status === 'resolved')
                                                <span class="badge badge-status-clean rounded-pill">Đã xử lý</span>
                                            @else
                                                <span class="badge border rounded-pill" style="border-color: #cbd5e1 !important; color: #64748b;">Đã bác bỏ</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">Reporter: {{ $entry->reporter_name }}</div>
                                        @if(!empty($entry->additional_notes))
                                            <div class="small text-dark mt-1">{{ $entry->additional_notes }}</div>
                                        @endif
                                        <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($entry->created_at)->diffForHumans() }}</div>
                                    </div>
                                @empty
                                    <div class="text-muted">Hiện chưa có báo cáo nào cho bài viết này.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer admin-modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <a href="{{ route('post.show', $post->id) }}" target="_blank" class="btn btn-action-soft">
                        <i class="fa-solid fa-eye me-1"></i> Xem bài gốc
                    </a>


                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <form action="{{ route('admin.users.toggle_status', $post->user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thay đổi trạng thái tài khoản người đăng?');">
                            @csrf
                            <button type="submit" class="btn btn-action-soft"><i class="fa-solid {{ (int) ($post->author_status ?? 1) === 1 ? 'fa-user-lock' : 'fa-user-check' }} me-1"></i> {{ (int) ($post->author_status ?? 1) === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}</button>
                        </form>


                        <form action="{{ route('admin.posts.toggle_visibility', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thay đổi trạng thái bài viết?');">
                            @csrf
                            <button type="submit" class="btn btn-confirm-grad"><i class="fa-solid {{ ($post->content_status ?? 'visible') === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i> {{ ($post->content_status ?? 'visible') === 'hidden' ? 'Hiện bài' : 'Ẩn bài' }}</button>
                        </form>


                        <form action="{{ route('admin.posts.moderate', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa mềm bài viết này?');">
                            @csrf
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-action-soft">Xóa mềm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection



