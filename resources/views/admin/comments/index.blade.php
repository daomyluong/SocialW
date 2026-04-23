@extends('layouts.admin')


@section('admin_title', 'Quản Lý Bình Luận')


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


    .comment-chip {
        border-radius: 999px;
        padding: 5px 12px;
        background: rgba(79, 172, 254, 0.12);
        color: #1e40af;
        border: 1px solid rgba(79, 172, 254, 0.2);
        font-weight: 600;
        font-size: 0.8rem;
    }


    .admin-modal .modal-dialog { max-width: 1220px; }


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


    .comment-focus {
        border-radius: 0.9rem;
        border: 1px solid rgba(79, 172, 254, 0.3);
        background: rgba(79, 172, 254, 0.08);
        padding: 0.75rem 0.85rem;
    }


    .comment-focus + .comment-focus { margin-top: 0.65rem; }


    .report-item {
        border-radius: 0.9rem;
        border: 1px solid #e5efff;
        background: #f8fbff;
        padding: 0.8rem 0.9rem;
    }


    .report-item + .report-item { margin-top: 0.65rem; }


    .badge-soft-pending {
        background-color: #fff1f2;
        color: #be123c;
        border: 1px solid var(--accent-coral);
    }


    .badge-soft-resolved {
        background-color: #f0fdfa;
        color: #0f766e;
        border: 1px solid var(--accent-teal);
    }


    .admin-modal-footer {
        border: none;
        padding: 1rem 1.5rem 1.35rem;
        background: transparent;
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


    .btn-action-soft:hover { background: rgba(79, 172, 254, 0.18); color: #1d4ed8; }


    .btn-confirm-grad {
        border-radius: 999px;
        border: none;
        background: var(--grad-primary);
        color: #fff;
        padding: 0.65rem 1.4rem;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.22);
        transition: 0.25s;
    }


    .btn-confirm-grad:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(79, 172, 254, 0.28);
        color: #fff;
    }


    .btn-delete-soft {
        border-radius: 999px;
        border: 1px solid rgba(244, 63, 94, 0.3);
        background: rgba(244, 63, 94, 0.1);
        color: #be123c;
        font-weight: 700;
        padding: 0.56rem 1.1rem;
    }


    .btn-delete-soft:hover { background: rgba(244, 63, 94, 0.16); color: #9f1239; }
</style>


<div class="container-fluid px-0">


    <div class="card card-soft mb-4 p-3">
        <form action="{{ route('admin.comments.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text search-soft border-0 text-muted rounded-start-pill ps-4"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control search-soft border-0 rounded-end-pill" placeholder="Tìm nội dung bình luận hoặc người đăng..." value="{{ request('search') }}">
                </div>
            </div>


            <div class="col-lg-3 col-md-6">
                <select name="sort" class="form-select filter-pill w-100">
                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="hot" {{ request('sort') == 'hot' ? 'selected' : '' }}>Nhiều tương tác nhất</option>
                </select>
            </div>


            <div class="col-lg-3 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-grad w-100 fw-bold"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                <a href="{{ route('admin.comments.index') }}" class="btn btn-grad-soft rounded-pill px-3" title="Thiết lập lại"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>


    <div class="card card-soft overflow-hidden">
        <div class="table-responsive">
            <table class="table table-soft mb-0">
                <thead>
                    <tr>
                        <th class="px-4 text-center">Người bình luận</th>
                        <th class="text-center" width="30%">Nội dung</th>
                        <th class="text-center">Bài viết gốc</th>
                        <th class="text-center">Thời gian</th>
                        <th class="text-center px-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admin_comments as $comment)
                    <tr>
                        <td class="px-4 text-start">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name ?? 'User') }}&background=4facfe&color=fff" alt="Avatar" class="rounded-circle me-2" width="36" height="36">
                                <div>
                                    <div class="fw-semibold text-dark">
                                        {{ $comment->author_name }}
                                        @if((int) ($comment->user_status ?? 1) === 0)
                                            <i class="fa-solid fa-lock text-warning ms-1" title="Tài khoản đã bị khóa"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">USER #{{ $comment->commenter_user_id }}</small>
                                </div>
                            </div>
                        </td>


                        <td class="text-start">
                            <div class="fw-semibold text-dark" style="font-size: 0.86rem;">{{ Str::limit($comment->content, 90) }}</div>
                            <small class="text-muted d-block">COMMENT #{{ $comment->id }}</small>
                        </td>


                        <td class="text-center">
                            <a href="{{ route('post.show', ['id' => $comment->post_id, 'focus_comment' => $comment->id]) }}" target="_blank" class="comment-chip text-decoration-none">
                                <i class="fa-solid fa-up-right-from-square me-1"></i> POST #{{ $comment->post_id }}
                            </a>
                        </td>


                        <td class="text-center">
                            <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($comment->created_at)->format('H:i') }}</div>
                            <div class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($comment->created_at)->format('d/m/Y') }}</div>
                        </td>


                        <td class="text-center px-4">
                            <form action="{{ route('admin.comments.toggle_visibility', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thay đổi trạng thái bình luận?');">
                                @csrf
                                <button type="submit" class="btn btn-grad-soft rounded-pill px-3" title="{{ ($comment->content_status ?? 'visible') === 'hidden' ? 'Hiện bình luận' : 'Ẩn bình luận' }}">
                                    <i class="fa-solid {{ ($comment->content_status ?? 'visible') === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="btn btn-review" data-bs-toggle="modal" data-bs-target="#modalCommentReview{{ $comment->id }}">
                                Xem xét <i class="fa-solid fa-gavel ms-1"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Không có bình luận nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <div class="d-flex justify-content-center p-4">
            {{ $admin_comments->links('vendor.pagination.admin-soft') }}
        </div>
    </div>
</div>


@foreach($admin_comments as $comment)
    <div class="modal fade admin-modal" id="modalCommentReview{{ $comment->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-modal-shell">
                <div class="modal-header admin-modal-header">
                    <h5 class="modal-title admin-modal-title"><i class="fa-solid fa-comment-medical me-2" style="color: var(--hlink-blue);"></i> Xem xét bình luận #{{ $comment->id }}</h5>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>


                <div class="modal-body admin-modal-body">
                    <div class="row g-0">
                        <div class="col-lg-8 pe-lg-3 mb-3 mb-lg-0">
                            <div class="modal-panel-soft p-4 h-100">
                                <h6 class="fw-bold text-muted mb-3">BÀI VIẾT + BÌNH LUẬN ĐÍCH (70%)</h6>


                                <div class="review-content-box mb-3">
                                    <div class="small text-muted mb-1">Nội dung bài viết gốc</div>
                                    {{ $comment->post_content ?: 'Bài viết không có nội dung chữ.' }}
                                </div>


                                @forelse($comment->thread_comments as $threadComment)
                                    <div class="comment-focus {{ (int) $threadComment->id === (int) $comment->id ? 'border-primary' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                                {{ $threadComment->author_name }}
                                                @if((int) $threadComment->id === (int) $comment->id)
                                                    <span class="badge badge-soft-pending rounded-pill ms-1">Bình luận đang xét</span>
                                                @endif
                                            </span>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($threadComment->created_at)->diffForHumans() }}</small>
                                        </div>
                                        <div class="text-dark">{{ $threadComment->content }}</div>
                                    </div>
                                @empty
                                    <div class="text-muted">Không còn bình luận nào khả dụng trong bài viết này.</div>
                                @endforelse
                            </div>
                        </div>


                        <div class="col-lg-4 ps-lg-3">
                            <div class="modal-panel-soft p-4 h-100" style="max-height: 100%; min-height: 520px; overflow-y: auto;">
                                <h6 class="fw-bold text-muted mb-3">THÔNG TIN BỔ SUNG (30%)</h6>


                                <div class="report-item mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name ?? 'User') }}&background=4facfe&color=fff" alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                {{ $comment->author_name }}
                                                @if((int) ($comment->user_status ?? 1) === 0)
                                                    <i class="fa-solid fa-lock text-warning ms-1" title="Tài khoản đã bị khóa"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted">USER #{{ $comment->commenter_user_id }}</small>
                                        </div>
                                    </div>
                                    <div class="small text-muted">Tham gia: {{ \Carbon\Carbon::parse($comment->author_created_at)->diffForHumans() }}</div>
                                    <div class="small text-muted">Bình luận vi phạm trước đó: <span class="fw-bold text-dark">{{ $comment->previous_violation_count }}</span></div>
                                </div>


                                <h6 class="fw-bold text-muted mb-2">BÁO CÁO LIÊN QUAN</h6>
                                @forelse($comment->report_entries as $entry)
                                    <div class="report-item">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-semibold" style="font-size: 0.85rem;">{{ $entry->reason }}</span>
                                            @if($entry->status === 'pending')
                                                <span class="badge badge-soft-pending rounded-pill">Chờ xử lý</span>
                                            @elseif($entry->status === 'resolved')
                                                <span class="badge badge-soft-resolved rounded-pill">Đã xử lý</span>
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
                                    <div class="text-muted">Bình luận này chưa có báo cáo.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer admin-modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <a href="{{ route('post.show', ['id' => $comment->post_id, 'focus_comment' => $comment->id]) }}" target="_blank" class="btn btn-action-soft">
                        <i class="fa-solid fa-eye me-1"></i> Xem bài gốc
                    </a>


                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <form action="{{ route('admin.users.toggle_status', $comment->commenter_user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thay đổi trạng thái tài khoản?');">
                            @csrf
                            <button type="submit" class="btn btn-confirm-grad"><i class="fa-solid {{ (int) ($comment->user_status ?? 1) === 1 ? 'fa-user-lock' : 'fa-user-check' }} me-1"></i> {{ (int) ($comment->user_status ?? 1) === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}</button>
                        </form>


                        <form action="{{ route('admin.comments.toggle_visibility', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận thay đổi trạng thái bình luận?');">
                            @csrf
                            <button type="submit" class="btn btn-action-soft"><i class="fa-solid {{ ($comment->content_status ?? 'visible') === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i> {{ ($comment->content_status ?? 'visible') === 'hidden' ? 'Hiện bình luận' : 'Ẩn bình luận' }}</button>
                        </form>


                        <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bình luận này?');">
                            @csrf
                            <button type="submit" class="btn btn-delete-soft">Xóa bình luận</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection



