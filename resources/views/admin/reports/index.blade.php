@extends('layouts.admin')


@section('admin_title', 'Báo Cáo')


@section('content')
<style>
    :root {
        --hlink-blue: #4facfe;
        --hlink-green: #43e97b;
        --hlink-bg: #f0f4f8;
        --grad-primary: linear-gradient(135deg, var(--hlink-green) 0%, var(--hlink-blue) 100%);
        --accent-teal: #00f2fe;
        --accent-amber: #fddb92;
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


    .filter-pill:focus, .search-soft:focus {
        box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
    }


    .table-soft {
        font-size: 0.92rem;
        color: #334155;
    }


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


    .table-soft tbody tr {
        transition: 0.2s;
    }


    .table-soft tbody tr:hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
    }


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


    .btn-grad-soft:hover {
        background: rgba(79, 172, 254, 0.18);
        color: #1d4ed8;
    }


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


    .badge-status-pending {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid var(--accent-amber);
    }


    .badge-status-resolved {
        background-color: #f0fdfa;
        color: #0f766e;
        border: 1px solid var(--accent-teal);
    }


    .badge-status-dismissed {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
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


    .btn-hide-soft {
        border-radius: 999px;
        border: 1px solid rgba(249, 115, 22, 0.35);
        background: rgba(249, 115, 22, 0.1);
        color: #c2410c;
        font-weight: 700;
    }


    .btn-hide-soft:hover {
        background: rgba(249, 115, 22, 0.18);
        color: #9a3412;
    }


    .btn-delete-soft {
        border-radius: 999px;
        border: 1px solid rgba(248, 113, 113, 0.35);
        background: rgba(248, 113, 113, 0.1);
        color: #b91c1c;
        font-weight: 700;
    }


    .btn-delete-soft:hover {
        background: rgba(248, 113, 113, 0.18);
        color: #991b1b;
    }


    .btn-ban-soft {
        border-radius: 999px;
        border: 1px solid rgba(239, 68, 68, 0.35);
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        font-weight: 700;
    }


    .btn-ban-soft:hover {
        background: rgba(239, 68, 68, 0.18);
        color: #991b1b;
    }
</style>


<div class="container-fluid px-0">
    <div class="card card-soft mb-4 p-3">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text search-soft border-0 text-muted rounded-start-pill ps-4"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control search-soft border-0 rounded-end-pill" placeholder="Tìm lý do tố cáo...">
                </div>
            </div>


            <div class="col-lg-2 col-md-6">
                <select name="type" class="form-select filter-pill w-100">
                    <option value="">Tất cả đối tượng</option>
                    <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Bài viết</option>
                    <option value="comment" {{ request('type') == 'comment' ? 'selected' : '' }}>Bình luận</option>
                    <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>Tài khoản</option>
                </select>
            </div>


            <div class="col-lg-2 col-md-6">
                <select name="status" class="form-select filter-pill w-100">
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Đã giải quyết</option>
                    <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Đã bác bỏ</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tất cả</option>
                </select>
            </div>


            <div class="col-lg-2 col-md-6">
                <select name="sort" class="form-select filter-pill w-100">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="most" {{ request('sort') == 'most' ? 'selected' : '' }}>Báo cáo cao nhất</option>
                </select>
            </div>


            <div class="col-lg-2 col-md-6 d-flex justify-content-lg-end gap-2">
                <button type="submit" class="btn btn-grad w-100"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-grad-soft rounded-pill px-3"><i class="fa-solid fa-rotate-left"></i></a>
            </div>


            <div class="col-12">
                <select name="reason" class="form-select filter-pill w-100">
                    <option value="">Mọi lý do</option>
                    @foreach([
                        'Vấn đề liên quan đến người dưới 18 tuổi',
                        'Bắt nạt, quấy rối hoặc lăng mạ/lạm dụng/ngược đãi',
                        'Tự tử hoặc tự hại bản thân',
                        'Nội dung mang tính bạo lực, thù ghét hoặc gây phiền toái',
                        'Bán hoặc quảng bá mặt hàng bị hạn chế',
                        'Nội dung người lớn',
                        'Thông tin sai sự thật, lừa đảo hoặc gian lận',
                        'Quyền sở hữu trí tuệ',
                        'Tôi không muốn xem nội dung này'
                    ] as $r)
                        <option value="{{ $r }}" {{ request('reason') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>


    <div class="card card-soft overflow-hidden">
        <div class="table-responsive">
            <table class="table table-soft mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="px-4 text-center" width="30%">Đối tượng bị tố cáo</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Lý do chính</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-center">Cập nhật</th>
                        <th class="text-center px-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admin_reports as $report)
                        <tr id="report-row-{{ $report->id ?? $report->report_id ?? '' }}">
                            <td class="px-4 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="media-preview me-3">
                                        @if($report->reported_entity_type == 'post' && $report->thumbnail)
                                            <img src="{{ $report->thumbnail }}" class="w-100 h-100 rounded-3" style="object-fit: cover;">
                                        @elseif($report->reported_entity_type == 'user')
                                            <img src="{{ $report->thumbnail }}" class="rounded-circle" width="35">
                                        @elseif(isset($report->is_video) && $report->is_video)
                                            <i class="fa-solid fa-play"></i>
                                        @else
                                            <i class="fa-solid {{ $report->reported_entity_type == 'post' ? 'fa-file-lines' : ($report->reported_entity_type == 'user' ? 'fa-user' : 'fa-comment-dots') }}"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $report->display_name }}</div>
                                        <small class="text-muted">{{ strtoupper($report->reported_entity_type) }} #{{ $report->reported_entity_id }}</small>
                                    </div>
                                </div>
                            </td>


                            <td class="text-center">
                                @if($report->status == 'pending')
                                    <span class="badge badge-status-pending px-3 py-2 rounded-pill">Chờ xử lý</span>
                                @elseif($report->status == 'resolved')
                                    <span class="badge badge-status-resolved px-3 py-2 rounded-pill">Đã xử lý</span>
                                @else
                                    <span class="badge badge-status-dismissed px-3 py-2 rounded-pill">Bác bỏ</span>
                                @endif
                            </td>


                            <td class="text-start text-dark">
                                <div style="max-width: 330px;">{{ $report->reason }}</div>
                            </td>


                            <td class="text-center">
                                <span class="fw-bold" style="color: #ff9a9e; font-size: 1rem;">{{ $report->total_reports }}</span>
                            </td>


                            <td class="text-center">
                                <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($report->latest_report_time)->format('H:i') }}</div>
                                <div class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($report->latest_report_time)->format('d/m/Y') }}</div>
                            </td>


                            <td class="text-center px-4">
                                <form action="{{ route('admin.reports.process') }}" method="POST" class="d-inline" onsubmit="return confirm('Khóa tạm thời tài khoản liên quan tới đối tượng này?');">
                                    @csrf
                                    <input type="hidden" name="entity_type" value="{{ $report->reported_entity_type }}">
                                    <input type="hidden" name="entity_id" value="{{ $report->reported_entity_id }}">
                                    <input type="hidden" name="reason" value="{{ $report->reason }}">
                                    <input type="hidden" name="author_id" value="{{ $report->author_id }}">
                                    <button type="submit" name="action" value="ban" class="btn btn-grad-soft rounded-pill px-3" title="Khóa tài khoản" {{ empty($report->author_id) ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-lock"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-white" style="background: #38bdf8; border: none;" data-bs-toggle="modal" data-bs-target="#modalReport{{ $report->reported_entity_type }}{{ $report->reported_entity_id }}">
                                    Xem xét <i class="fa-solid fa-gavel ms-1"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Không có báo cáo nào khớp điều kiện.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <div class="p-4 d-flex justify-content-center">
            {{ $admin_reports->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>


@foreach($admin_reports as $report)
    <div class="modal fade admin-modal" id="modalReport{{ $report->reported_entity_type }}{{ $report->reported_entity_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-modal-shell">
                <div class="modal-header admin-modal-header">
                    <h5 class="modal-title admin-modal-title mb-0"><i class="fa-solid fa-scale-balanced me-2" style="color: var(--hlink-blue);"></i>Xét xử Báo cáo</h5>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>


                <div class="modal-body admin-modal-body p-0">
                    <div class="row g-0 h-100">
                        <div class="col-md-7 p-4 border-end">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-muted mb-0">NỘI DUNG VI PHẠM</h6>
                                <a href="{{ $report->deep_link }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> Xem bối cảnh gốc
                                </a>
                            </div>


                            <div class="modal-panel-soft p-3">
                                @if($report->reported_entity_type == 'post' && $report->thumbnail)
                                    <div class="review-media-box mb-3">
                                        <img src="{{ $report->thumbnail }}" class="img-fluid">
                                    </div>
                                @elseif($report->reported_entity_type == 'post' && isset($report->is_video) && $report->is_video)
                                    <div class="review-media-box mb-3">
                                        <video controls>
                                            <source src="{{ $report->thumbnail }}">
                                        </video>
                                    </div>
                                @endif


                                <div class="review-content-box">{{ $report->full_content }}</div>
                            </div>
                        </div>


                        <div class="col-md-5 p-4 bg-white" style="max-height: 400px; overflow-y: auto;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-muted mb-0">DANH SÁCH TỐ CÁO</h6>
                                <span class="stat-chip"><i class="fa-solid fa-bell"></i> {{ $report->total_reports }} đơn</span>
                            </div>


                            @foreach($report->reporters as $reporter)
                                <div class="report-item">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-user-ninja text-muted me-2"></i>
                                        <span class="fw-bold" style="font-size: 0.85rem;">{{ $reporter->display_name ?? 'Hệ thống tự động' }}</span>
                                        <small class="text-muted ms-auto" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($reporter->created_at)->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-dark fst-italic" style="font-size: 0.85rem;">
                                        {{ $reporter->additional_notes ?? 'Không có lời nhắn bổ sung.' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>


                <div class="modal-footer admin-modal-footer bg-light d-flex justify-content-between">
                    <form action="{{ route('admin.reports.process') }}" method="POST" class="w-100 d-flex justify-content-between">
                        @csrf
                        <input type="hidden" name="entity_type" value="{{ $report->reported_entity_type }}">
                        <input type="hidden" name="entity_id" value="{{ $report->reported_entity_id }}">
                        <input type="hidden" name="reason" value="{{ $report->reason }}">
                        <input type="hidden" name="author_id" value="{{ $report->author_id }}">


                        <button type="submit" name="action" value="dismiss" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Bác bỏ</button>


                        <div class="d-flex gap-2">
                            @if($report->reported_entity_type != 'user')
                                <button type="submit" name="action" value="hide" class="btn btn-hide-soft rounded-pill px-4 fw-bold" onclick="return confirm('Bạn muốn ẩn nội dung này?');">Ẩn nội dung</button>
                                <button type="submit" name="action" value="delete" class="btn btn-delete-soft rounded-pill px-4 fw-bold" onclick="return confirm('Bạn muốn xoá vĩnh viễn nội dung này?');">Xóa nội dung</button>
                            @endif
                            <button type="submit" name="action" value="ban" class="btn btn-ban-soft rounded-pill px-4 fw-bold" onclick="return confirm('Khóa tài khoản người bị tố cáo trong 24h?');">Khóa tài khoản 24h</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const highlightId = urlParams.get('highlight_id');
        
        if (highlightId) {
            const targetRow = document.getElementById('report-row-' + highlightId);
            
            if (targetRow) {
                // 1. Tự động cuộn màn hình tới đúng vị trí hàng đó
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // 2. Nháy nền màu vàng nhạt trong 3 giây
                targetRow.style.backgroundColor = '#fff3cd';
                targetRow.style.transition = 'background-color 0.5s ease';
                
                setTimeout(() => {
                    targetRow.style.backgroundColor = 'transparent';
                }, 3000);
            }
        }
    });
</script>


