@extends('layouts.admin')

@section('admin_title', 'Tòa Án Hệ Thống (Báo Cáo)')

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

    .filter-pill, .filter-select, .search-soft {
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        font-weight: 500;
    }
    .filter-pill:focus, .filter-select:focus, .search-soft:focus { box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2); }
    
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
    .table-soft tbody tr:first-child td { font-size: 0.95rem; }

    .btn-grad { background: var(--grad-primary); border: none; color: white; border-radius: 2rem; padding: 8px 20px; font-weight: 600; transition: 0.3s; box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3); }
    .btn-grad:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(67, 233, 123, 0.4); color: white; }
    .btn-grad-soft { background: rgba(79, 172, 254, 0.12); color: #1d4ed8; border: 1px solid rgba(79, 172, 254, 0.25); box-shadow: none; }
    .btn-grad-soft:hover { background: rgba(79, 172, 254, 0.18); }

    .badge-soft-pending { background-color: #fffbeb; color: #d97706; border: 1px solid var(--accent-amber); }
    .badge-soft-resolved { background-color: #f0fdfa; color: #0f766e; border: 1px solid var(--accent-teal); }
    .badge-soft-dismissed { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }

    .media-preview {
        width: 46px;
        height: 46px;
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

    .modal-content {
        border-radius: 1.35rem;
        border: none;
        overflow: hidden;
        box-shadow: 0 26px 70px rgba(45, 90, 138, 0.18);
    }

    .reporter-card {
        background: #f8fbff;
        border: 1px solid #e7f2ff;
        border-radius: 1rem;
        padding: 0.85rem 0.95rem;
    }
</style>

<div class="container-fluid px-0">

    <div class="card card-soft mb-4 p-3">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text search-soft border-0 text-muted rounded-start-pill ps-4"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control search-soft border-0 rounded-end-pill" placeholder="Tìm nội dung, người bị tố cáo hoặc lý do...">
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
                <select name="sort" class="form-select filter-select w-100">
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
            <table class="table table-soft mb-0">
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
                        <tr>
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
                                    <div class="fw-semibold text-dark">"{{ $report->display_name }}"</div>
                                    <small class="text-muted text-end">{{ strtoupper($report->reported_entity_type) }} #{{ $report->reported_entity_id }}</small>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            @if($report->status == 'pending')
                                <span class="badge badge-soft-pending px-3 py-2 rounded-pill">Chờ xử lý</span>
                            @elseif($report->status == 'resolved')
                                <span class="badge badge-soft-resolved px-3 py-2 rounded-pill">Đã xử lý</span>
                            @else
                                <span class="badge badge-soft-dismissed px-3 py-2 rounded-pill">Bác bỏ</span>
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
                            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-white" style="background: #38bdf8; border: none;" data-bs-toggle="modal" data-bs-target="#modalReport{{ $report->reported_entity_type }}{{ $report->reported_entity_id }}">
                                Xem xét <i class="fa-solid fa-gavel ms-1"></i>
                            </button>
                        </td>
                    </tr>

                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">Không có báo cáo nào khớp điều kiện.</td></tr>
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
    <div class="modal fade" id="modalReport{{ $report->reported_entity_type }}{{ $report->reported_entity_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 1.25rem; border: none; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                <div class="modal-header bg-light border-0 p-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-scale-balanced me-2" style="color: var(--hlink-blue);"></i> Xét xử Báo cáo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-7 p-4 border-end">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-muted mb-0">NỘI DUNG VI PHẠM</h6>
                                <a href="{{ $report->deep_link }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> Xem bối cảnh gốc
                                </a>
                            </div>
                            <div class="p-3 bg-light rounded-3" style="min-height: 200px;">
                                @if($report->reported_entity_type == 'post' && $report->thumbnail)
                                    <img src="{{ $report->thumbnail }}" class="img-fluid rounded mb-3" style="max-height: 200px;">
                                @endif
                                <p class="text-dark mb-0">"{{ $report->full_content }}"</p>
                            </div>
                        </div>

                        <div class="col-md-5 p-4 bg-white" style="max-height: 400px; overflow-y: auto;">
                            <h6 class="fw-bold text-muted mb-3">DANH SÁCH TỐ CÁO ({{ $report->total_reports }} ĐƠN)</h6>
                            @foreach($report->reporters as $reporter)
                                <div class="reporter-card mb-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fa-solid fa-user-ninja text-muted me-2"></i>
                                        <span class="fw-bold" style="font-size: 0.85rem;">{{ $reporter->display_name ?? 'Hệ thống tự động' }}</span>
                                        <small class="text-muted ms-auto" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($reporter->created_at)->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-dark fst-italic" style="font-size: 0.85rem;">
                                        "{{ $reporter->additional_notes ?? 'Không có lời nhắn bổ sung.' }}"
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-light d-flex justify-content-between">
                    <form action="{{ route('admin.reports.process') }}" method="POST" class="w-100 d-flex justify-content-between">
                        @csrf
                        <input type="hidden" name="entity_type" value="{{ $report->reported_entity_type }}">
                        <input type="hidden" name="entity_id" value="{{ $report->reported_entity_id }}">
                        <input type="hidden" name="reason" value="{{ $report->reason }}">
                        <input type="hidden" name="author_id" value="{{ $report->author_id }}">

                        <button type="submit" name="action" value="dismiss" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">Bác bỏ</button>
                        
                        <div class="d-flex gap-2">
                            @if($report->reported_entity_type != 'user')
                            <button type="submit" name="action" value="hide" class="btn rounded-pill px-4 fw-bold text-white" style="background: #fbbf24;" onclick="return confirm('Bạn muốn ẩn nội dung này?');">Ẩn nội dung</button>
                            <button type="submit" name="action" value="delete" class="btn rounded-pill px-4 fw-bold text-white" style="background: #f87171;" onclick="return confirm('Bạn muốn xoá vĩnh viễn nội dung này?');">Xóa nội dung</button>
                            @endif
                            <button type="submit" name="action" value="ban" class="btn rounded-pill px-4 fw-bold text-white" style="background: #ef4444;" onclick="return confirm('Khóa tài khoản người bị tố cáo trong 24h?');">Khóa tài khoản 24h</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsectionzy