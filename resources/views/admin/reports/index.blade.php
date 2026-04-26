@extends('layouts.admin')

@section('admin_title', 'Quản Lý Báo Cáo')

@section('content')
<style>
    :root {
        --hlink-blue: #4facfe;
        --hlink-green: #43e97b;
        --hlink-bg: #f0f4f8;
        --grad-primary: linear-gradient(135deg, var(--hlink-green) 0%, var(--hlink-blue) 100%);
        --accent-teal: #00f2fe;
        --accent-amber: #fddb92;
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

    .badge-status-pending { background-color: #fffbeb; color: #d97706; border: 1px solid var(--accent-amber); }
    .badge-status-resolved { background-color: #f0fdfa; color: #0f766e; border: 1px solid var(--accent-teal); }
    .badge-status-dismissed { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }

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

    .admin-modal-title { color: #1f2937; font-weight: 800; letter-spacing: 0.01em; }

    .admin-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #94a3b8;
        transition: 0.2s;
    }
    .admin-modal-close:hover { color: #f87171; border-color: rgba(248, 113, 113, 0.4); background: #fff1f2; }

    .admin-modal-body { padding: 1.35rem; min-height: 560px; }

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
    .review-media-box img, .review-media-box video { width: 100%; max-height: 520px; object-fit: contain; }

    .report-item {
        border-radius: 0.9rem;
        border: 1px solid #e5efff;
        background: #f8fbff;
        padding: 0.8rem 0.9rem;
    }
    .report-item + .report-item { margin-top: 0.65rem; }

    .admin-modal-footer { border: none; padding: 1rem 1.5rem 1.35rem; background: transparent; }

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
    .btn-confirm-grad:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(79, 172, 254, 0.28); color: #fff; }

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
                        'Trẻ em', 'Quấy rối', 'Tự tử', 'Bạo lực/Thù ghét', 
                        'Hàng cấm', 'Nhạy cảm', 'Sai sự thật', 'Sở hữu trí tuệ', 'Spam', 'Khác'
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
                    @php
                        $reasonMap = [
                            'Trẻ em'           => 'Vấn đề liên quan đến người dưới 18 tuổi',
                            'Quấy rối'         => 'Bắt nạt, quấy rối hoặc lăng mạ/lạm dụng/ngược đãi',
                            'Tự tử'            => 'Tự tử hoặc tự hại bản thân',
                            'Bạo lực/Thù ghét' => 'Nội dung mang tính bạo lực, thù ghét',
                            'Hàng cấm'         => 'Bán hoặc quảng bá mặt hàng bị hạn chế',
                            'Nhạy cảm'         => 'Nội dung người lớn',
                            'Sai sự thật'      => 'Thông tin sai sự thật, lừa đảo',
                            'Sở hữu trí tuệ'   => 'Quyền sở hữu trí tuệ',
                            'Spam'             => 'Spam, quấy rối hoặc lừa đảo',
                            'Khác'             => 'Lý do khác...'
                        ];
                    @endphp

                    @forelse($admin_reports as $report)
                        <tr id="report-row-{{ $report->id ?? $report->report_id ?? '' }}">
                            <td class="px-4 text-start">
                                <div class="d-flex align-items-center">
                                    <div class="media-preview me-3">
                                        @if($report->reported_entity_type == 'post' && $report->thumbnail)
                                            <img src="{{ asset('storage/' . $report->thumbnail) }}" onerror="this.src='{{ $report->thumbnail }}'" class="w-100 h-100 rounded-3" style="object-fit: cover;">
                                        @elseif($report->reported_entity_type == 'user')
                                            <img src="{{ $report->thumbnail }}" class="rounded-circle" width="35">
                                        @elseif(isset($report->is_video) && $report->is_video)
                                            <i class="fa-solid fa-play"></i>
                                        @else
                                            <i class="fa-solid {{ $report->reported_entity_type == 'post' ? 'fa-file-lines' : ($report->reported_entity_type == 'user' ? 'fa-user' : 'fa-comment-dots') }}"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">
                                            {{ $report->display_name }}
                                            @if((int) ($report->author_status ?? 1) === 0)
                                                <i class="fa-solid fa-lock text-warning ms-1" title="Tài khoản bị khóa"></i>
                                            @endif
                                        </div>
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
                                <div style="max-width: 250px;">{{ $reasonMap[$report->reason] ?? $report->reason }}</div>
                            </td>

                            <td class="text-center">
                                <span class="fw-bold" style="color: #ff9a9e; font-size: 1rem;">{{ $report->total_reports }}</span>
                            </td>

                            <td class="text-center">
                                <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($report->latest_report_time)->format('H:i') }}</div>
                                <div class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($report->latest_report_time)->format('d/m/Y') }}</div>
                            </td>

                            <td class="text-center px-4">
                                <form action="{{ route('admin.reports.process') }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận đổi trạng thái khóa của người bị tố cáo?');">
                                    @csrf
                                    <input type="hidden" name="entity_type" value="{{ $report->reported_entity_type }}">
                                    <input type="hidden" name="entity_id" value="{{ $report->reported_entity_id }}">
                                    <input type="hidden" name="reason" value="{{ $report->reason }}">
                                    <input type="hidden" name="author_id" value="{{ $report->author_id }}">
                                    <button type="submit" name="action" value="toggle_ban" class="btn btn-grad-soft rounded-pill px-3" title="{{ (int) ($report->author_status ?? 1) === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}" {{ empty($report->author_id) ? 'disabled' : '' }}>
                                        <i class="fa-solid {{ (int) ($report->author_status ?? 1) === 1 ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-review" data-bs-toggle="modal" data-bs-target="#modalReport{{ $report->reported_entity_type }}{{ $report->reported_entity_id }}">
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
            {{ $admin_reports->links('vendor.pagination.admin-soft') }}
        </div>
    </div>
</div>

@foreach($admin_reports as $report)
    <div class="modal fade admin-modal" id="modalReport{{ $report->reported_entity_type }}{{ $report->reported_entity_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-modal-shell">
                <div class="modal-header admin-modal-header">
                    <h5 class="modal-title admin-modal-title mb-0"><i class="fa-solid fa-scale-balanced me-2" style="color: var(--hlink-blue);"></i> Quản lý Báo cáo {{ strtoupper($report->reported_entity_type) }} #{{ $report->reported_entity_id }}</h5>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="modal-body admin-modal-body">
                    <div class="row g-0 h-100">
                        {{-- Cột trái: Nội dung vi phạm --}}
                        <div class="col-lg-8 pe-lg-3 mb-3 mb-lg-0">
                            <div class="modal-panel-soft p-4 h-100">
                                
                                @if($report->reported_entity_type == 'user')
                                    <h6 class="fw-bold text-muted mb-3">NỘI DUNG BỊ TỐ CÁO (TÀI KHOẢN)</h6>
                                    <div class="review-content-box mb-3 text-start">{{ $report->full_content }}</div>
                                    {{-- Đã xóa dòng hiển thị ảnh Avatar phóng to ở đây --}}

                                @elseif($report->reported_entity_type == 'post')
                                    <h6 class="fw-bold text-muted mb-3">NỘI DUNG BỊ TỐ CÁO (BÀI VIẾT)</h6>
                                    <div class="review-content-box mb-3 text-start">{{ $report->full_content ?: 'Bài viết không có nội dung chữ.' }}</div>
                                    
                                    @if($report->thumbnail && !($report->is_video ?? false))
                                        <div class="review-media-box mb-4">
                                            <img src="{{ asset('storage/' . $report->thumbnail) }}" onerror="this.src='{{ $report->thumbnail }}'" class="img-fluid" alt="Ảnh vi phạm">
                                        </div>
                                    @elseif($report->thumbnail && ($report->is_video ?? false))
                                        <div class="review-media-box mb-4">
                                            <video controls style="width: 100%; max-height: 520px;">
                                                <source src="{{ asset('storage/' . $report->thumbnail) }}" onerror="this.src='{{ $report->thumbnail }}'">
                                            </video>
                                        </div>
                                    @endif

                                @elseif($report->reported_entity_type == 'comment')
                                    <h6 class="fw-bold text-muted mb-3">NỘI DUNG BÀI VIẾT GỐC (POST #{{ $report->post_id ?? 'N/A' }})</h6>
                                    <div class="review-content-box mb-3 text-start">{{ $report->post_content ?: 'Bài viết không có nội dung chữ.' }}</div>
                                    
                                    @if(($report->post_media_type ?? null) === 'image' && ($report->post_media_url ?? null))
                                        <div class="review-media-box mb-4">
                                            <img src="{{ asset('storage/' . $report->post_media_url) }}" onerror="this.src='{{ asset($report->post_media_url) }}'" class="img-fluid" alt="Ảnh gốc bài viết">
                                        </div>
                                    @elseif(($report->post_media_type ?? null) === 'video' && ($report->post_media_url ?? null))
                                        <div class="review-media-box mb-4">
                                            <video controls style="width: 100%; max-height: 520px;">
                                                <source src="{{ asset('storage/' . $report->post_media_url) }}" onerror="this.src='{{ asset($report->post_media_url) }}'">
                                            </video>
                                        </div>
                                    @endif

                                    <h6 class="fw-bold text-muted mb-3 mt-4">BÌNH LUẬN ĐANG XÉT</h6>
                                    <div class="comment-focus">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                <i class="fa-solid fa-reply me-1 text-primary"></i> {{ $report->author_name }}
                                            </span>
                                        </div>
                                        <div class="text-dark text-start" style="font-size: 1.05rem;">{{ $report->full_content }}</div>
                                        @if(($report->content_status ?? 'visible') === 'hidden')
                                            <span class="badge bg-danger rounded-pill px-3 py-1 mt-2"><i class="fa-solid fa-eye-slash me-1"></i> Bình luận đang bị ẩn</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Cột phải: Thông tin & Danh sách --}}
                        <div class="col-lg-4 ps-lg-3">
                            <div class="modal-panel-soft p-4 h-100" style="max-height: 100%; min-height: 520px; overflow-y: auto;">
                                <h6 class="fw-bold text-muted mb-3">THÔNG TIN ĐỐI TƯỢNG BỊ BÁO CÁO</h6>
                                
                                <div class="report-item mb-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($report->author_name ?? 'User') }}&background=4facfe&color=fff" class="rounded-circle me-2" width="40" height="40">
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                {{ $report->author_name }}
                                                @if((int) ($report->author_status ?? 1) === 0)
                                                    <i class="fa-solid fa-lock text-warning ms-1" title="Tài khoản đã bị khóa"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted">USER #{{ $report->author_id ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                    <h6 class="fw-bold text-muted mb-0">DANH SÁCH BÁO CÁO</h6>
                                    <span class="badge bg-danger ms-1 rounded-pill">{{ $report->total_reports }} đơn</span>
                                </div>

                                @foreach($report->reporters as $reporter)
                                    <div class="report-item">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="fw-bold" style="font-size: 0.85rem;"><i class="fa-solid fa-user-ninja text-muted me-1"></i> {{ $reporter->display_name ?? 'Hệ thống tự động' }}</span>
                                            <small class="text-muted ms-auto" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($reporter->created_at)->diffForHumans() }}</small>
                                        </div>
                                        <div class="text-dark mt-1" style="font-size: 0.85rem;">
                                            {{ $reporter->additional_notes ?? 'Không có ghi chú.' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="modal-footer admin-modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <a href="{{ $report->reported_entity_type === 'user' ? route('profile.show', $report->reported_entity_id) : $report->deep_link }}" target="_blank" class="btn btn-action-soft">
                        <i class="fa-solid fa-up-right-from-square me-1"></i> Xem bối cảnh gốc
                    </a>

                    <form action="{{ route('admin.reports.process') }}" method="POST" class="d-flex gap-2 flex-wrap justify-content-end m-0">
                        @csrf
                        <input type="hidden" name="entity_type" value="{{ $report->reported_entity_type }}">
                        <input type="hidden" name="entity_id" value="{{ $report->reported_entity_id }}">
                        <input type="hidden" name="reason" value="{{ $report->reason }}">
                        <input type="hidden" name="author_id" value="{{ $report->author_id }}">

                        <button type="submit" name="action" value="dismiss" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Bác bỏ</button>

                        <button type="submit" name="action" value="toggle_ban" class="btn btn-action-soft" onclick="return confirm('Xác nhận thay đổi trạng thái tài khoản?');">
                            <i class="fa-solid {{ (int) ($report->author_status ?? 1) === 1 ? 'fa-user-lock' : 'fa-user-check' }} me-1"></i> 
                            {{ (int) ($report->author_status ?? 1) === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}
                        </button>

                        @if($report->reported_entity_type != 'user')
                            <button type="submit" name="action" value="toggle_hide" class="btn btn-confirm-grad" onclick="return confirm('Xác nhận thay đổi trạng thái hiển thị của nội dung này?');">
                                <i class="fa-solid {{ ($report->content_status ?? 'visible') === 'hidden' ? 'fa-eye' : 'fa-eye-slash' }} me-1"></i>
                                {{ ($report->content_status ?? 'visible') === 'hidden' ? 'Hiện nội dung' : 'Ẩn nội dung' }}
                            </button>
                            <button type="submit" name="action" value="delete" class="btn btn-delete-soft" onclick="return confirm('Bạn muốn xoá mềm nội dung này?');">Xóa nội dung</button>
                        @endif
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
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetRow.style.backgroundColor = '#fff3cd';
                targetRow.style.transition = 'background-color 0.5s ease';
                
                setTimeout(() => {
                    targetRow.style.backgroundColor = 'transparent';
                }, 3000);
            }
        }
    });
</script>