@extends('layouts.admin')

@section('admin_title', 'Quản Lý Người Dùng')

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
    .table-soft tbody tr:first-child td { font-size: 0.95rem; }

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

    .badge-admin { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .badge-user { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
    .badge-active { background-color: #f0fdfa; color: #0f766e; border: 1px solid var(--accent-teal); }
    .badge-locked { background-color: #fffbeb; color: #d97706; border: 1px solid var(--accent-amber); }

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

    .violation-card {
        background: #f8fbff;
        border: 1px solid #e7f2ff;
        border-radius: 1rem;
        padding: 0.85rem 0.95rem;
    }

    .stat-chip {
        border-radius: 999px;
        padding: 5px 12px;
        background: rgba(79, 172, 254, 0.12);
        color: #1e40af;
        border: 1px solid rgba(79, 172, 254, 0.2);
        font-weight: 600;
        font-size: 0.8rem;
    }

    .btn-status-lock { background: #ef4444; border: none; }
    .btn-status-unlock { background: #22c55e; border: none; }

    .admin-modal .modal-dialog {
        max-width: 1180px;
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
        min-height: 520px;
    }

    .modal-panel-soft {
        border-radius: 1rem;
        border: 1px solid rgba(79, 172, 254, 0.14);
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        height: 100%;
    }

    .field-label {
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        color: #94a3b8;
        margin-bottom: 0.4rem;
    }

    .input-soft {
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #f5f9ff;
        color: #334155;
        padding: 0.65rem 1rem;
    }

    .input-soft:focus {
        border-color: var(--hlink-blue);
        box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.22);
        background: #ffffff;
    }

    .avatar-preview-wrap {
        width: 148px;
        height: 148px;
        margin: 0 auto;
        border-radius: 999px;
        border: 3px solid rgba(79, 172, 254, 0.24);
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.2) 0%, rgba(67, 233, 123, 0.18) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1d4ed8;
        font-size: 3.1rem;
        font-weight: 800;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 999px;
        border: 1px solid rgba(34, 197, 94, 0.28);
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
        padding: 0.35rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .admin-modal-footer {
        border: none;
        padding: 1rem 1.5rem 1.35rem;
        background: transparent;
    }

    .btn-cancel-soft {
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #94a3b8;
        padding: 0.58rem 1.2rem;
        font-weight: 600;
    }

    .btn-cancel-soft:hover { background: #eef2f7; color: #64748b; }

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

    .btn-role-save {
        border-radius: 999px;
        border: 1px solid rgba(79, 172, 254, 0.24);
        background: rgba(79, 172, 254, 0.12);
        color: #1d4ed8;
        font-weight: 700;
        padding: 0.42rem 0.9rem;
        font-size: 0.82rem;
    }

    .btn-role-save:hover {
        background: rgba(79, 172, 254, 0.2);
    }
</style>

<div class="container-fluid px-0">

    <div class="card card-soft mb-4 p-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-lg-3 col-md-6">
                <div class="input-group">
                    <span class="input-group-text search-soft border-0 text-muted rounded-start-pill ps-4"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control search-soft border-0 rounded-end-pill" placeholder="Tìm tên hoặc email..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <select name="role" class="form-select filter-pill w-100">
                    <option value="">Tất cả Vai trò</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <select name="status" class="form-select filter-pill w-100">
                    <option value="">Tất cả Trạng thái</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <select name="sort" class="form-select filter-pill w-100">
                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="followers" {{ request('sort') == 'followers' ? 'selected' : '' }}>Nhiều người theo dõi nhất</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-grad w-100 fw-bold"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                <button type="button" class="btn btn-grad-soft rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalAddUser" title="Thêm user">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-grad-soft rounded-pill px-3" title="Thiết lập lại"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <div class="card card-soft overflow-hidden">
        <div class="table-responsive">
            <table class="table table-soft mb-0">
                <thead>
                    <tr>
                        <th class="px-4 text-center" width="28%">Thông tin tài khoản</th>
                        <th class="text-center">Vai trò</th>
                        <th class="text-center">Theo dõi & Bài đăng</th>
                        <th class="text-center">Hoạt động gần nhất</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center px-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admin_users as $user)
                    @php
                        $isSelf = Auth::id() && (int) Auth::id() === (int) $user->id;
                        $lastActiveAt = $user->updated_at ?? $user->created_at;
                    @endphp
                    <tr>
                        <td class="px-4 text-start">
                            <div class="d-flex align-items-center">
                                <div class="media-preview me-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->display_name ?? $user->name ?? 'User') }}&background=4facfe&color=fff" alt="Avatar" class="w-100 h-100 rounded-circle">
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $user->display_name ?? $user->name ?? 'N/A' }}</div>
                                    <small class="text-muted d-block">{{ $user->email }}</small>
                                    <small class="text-muted">USER #{{ $user->id }}</small>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            @if(($user->role ?? 'user') === 'admin')
                                <span class="badge badge-admin px-3 py-2 rounded-pill">Quản trị viên</span>
                            @else
                                <span class="badge badge-user px-3 py-2 rounded-pill">Người dùng</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <span class="stat-chip"><i class="fa-solid fa-user-group me-1"></i> {{ $user->follower_count ?? 0 }} theo dõi</span>
                                <span class="stat-chip"><i class="fa-solid fa-newspaper me-1"></i> {{ $user->post_count ?? 0 }} bài</span>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="text-dark fw-bold" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($lastActiveAt)->format('H:i') }}</div>
                            <div class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($lastActiveAt)->format('d/m/Y') }}</div>
                        </td>

                        <td class="text-center">
                            @if((int) ($user->is_active ?? 1) === 1)
                                <span class="badge badge-active px-3 py-2 rounded-pill">Hoạt động</span>
                            @else
                                <span class="badge badge-locked px-3 py-2 rounded-pill">Đã khóa</span>
                            @endif
                        </td>

                        <td class="text-center px-4">
                            <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold text-white" style="background: #38bdf8; border: none;" data-bs-toggle="modal" data-bs-target="#modalUserReview{{ $user->id }}">
                                Xem xét <i class="fa-solid fa-gavel ms-1"></i>
                            </button>
                            @if($isSelf)
                                <div class="small text-warning mt-2">Đây là tài khoản của bạn</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Không tìm thấy người dùng nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 d-flex justify-content-center">
            {{ $admin_users->links('vendor.pagination.admin-soft') }}
        </div>
    </div>
</div>

@foreach($admin_users as $user)
    @php
        $isSelf = Auth::id() && (int) Auth::id() === (int) $user->id;
    @endphp
    <div class="modal fade admin-modal" id="modalUserReview{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-modal-shell">
                <div class="modal-header admin-modal-header">
                    <h5 class="modal-title admin-modal-title"><i class="fa-solid fa-user-shield me-2" style="color: var(--hlink-blue);"></i> Xem xét tài khoản #{{ $user->id }}</h5>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="modal-body admin-modal-body">
                    <div class="row g-0">
                        <div class="col-md-7 pe-md-3 mb-3 mb-md-0">
                            <div class="modal-panel-soft p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-muted mb-0">THÔNG TIN CHI TIẾT</h6>
                                <a href="{{ url('/profile/' . $user->id) }}" target="_blank" class="btn-action-soft">
                                    <i class="fa-solid fa-up-right-from-square me-1"></i> Link Profile gốc
                                </a>
                            </div>

                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->display_name ?? $user->name ?? 'User') }}&background=4facfe&color=fff" alt="Avatar" class="rounded-circle me-3" width="54" height="54">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->display_name ?? $user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Bio</small>
                                        <div class="text-dark">{{ $user->bio ?? 'Chưa cập nhật' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Ngày sinh</small>
                                        <div class="text-dark">{{ $user->birth_date ?? 'Chưa cập nhật' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Giới tính</small>
                                        <div class="text-dark">{{ $user->gender ?? 'Chưa cập nhật' }}</div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="col-md-5 ps-md-3">
                            <div class="modal-panel-soft p-4" style="max-height: 100%; min-height: 420px; overflow-y: auto;">
                            <h6 class="fw-bold text-muted mb-3">VI PHẠM GẦN ĐÂY</h6>
                            @forelse($user->recent_violations as $violation)
                                <div class="violation-card mb-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="fw-bold" style="font-size: 0.85rem;">{{ $violation->reason }}</span>
                                        <small class="text-muted ms-auto" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($violation->created_at)->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        @if($violation->status === 'pending')
                                            <span class="badge badge-locked rounded-pill">Chờ xử lý</span>
                                        @elseif($violation->status === 'resolved')
                                            <span class="badge badge-active rounded-pill">Đã xử lý</span>
                                        @else
                                            <span class="badge badge-user rounded-pill">Đã bác bỏ</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">Chưa có hành vi vi phạm nào được ghi nhận.</div>
                            @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer admin-modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <form action="{{ route('admin.users.update_role', $user->id) }}" method="POST" class="d-flex align-items-center gap-2 flex-wrap">
                        @csrf
                        <select name="role" class="form-select input-soft" style="min-width: 135px; max-width: 140px; font-size: 0.82rem; padding: 0.4rem 0.7rem;">
                            <option value="user" {{ ($user->role ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ ($user->role ?? 'user') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-role-save">Đổi vai trò</button>
                    </form>

                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-confirm-grad" {{ $isSelf && (int)($user->is_active ?? 1) === 1 ? 'disabled' : '' }}>
                                {{ (int)($user->is_active ?? 1) === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mềm tài khoản này không?');">
                            @csrf
                            <button type="submit" class="btn btn-action-soft">Xóa mềm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade admin-modal" id="modalAddUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal-shell">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title admin-modal-title"><i class="fa-solid fa-user-plus me-2" style="color: #00c2ff;"></i> Tạo Tài Khoản Mới</h5>
                <button type="button" class="admin-modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
            <div class="modal-body admin-modal-body">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="modal-panel-soft p-4 d-flex flex-column justify-content-center h-100 text-center">
                            <div id="newUserAvatarPreview" class="avatar-preview-wrap mb-3">U</div>
                            <div class="status-pill mx-auto">
                                <i class="fa-solid fa-circle-check"></i>
                                Trạng thái: Hoạt động
                            </div>
                            <p class="text-muted mt-3 mb-0" style="font-size: 0.86rem; line-height: 1.6;">Avatar sẽ lấy chữ cái đầu từ Tên hiển thị để Admin dễ nhận diện ngay khi tạo tài khoản.</p>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="modal-panel-soft p-4 h-100">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="displayNameInput" class="field-label">Tên hiển thị</label>
                                    <input id="displayNameInput" type="text" name="display_name" class="form-control input-soft" placeholder="Ví dụ: Linh Nguyễn" required>
                                </div>

                                <div class="col-12">
                                    <label for="emailInput" class="field-label">Email</label>
                                    <input id="emailInput" type="email" name="email" class="form-control input-soft" placeholder="example@socialw.local" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="tempPasswordInput" class="field-label">Mật khẩu tạm</label>
                                    <input id="tempPasswordInput" type="text" name="temp_password" class="form-control input-soft" placeholder="Tối thiểu 6 ký tự" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="roleInput" class="field-label">Vai trò</label>
                                    <select id="roleInput" name="role" class="form-select input-soft" required>
                                        <option value="user" selected>Thành viên</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer admin-modal-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-cancel-soft" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-confirm-grad">Xác nhận tạo tài khoản</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const displayNameInput = document.getElementById('displayNameInput');
        const avatarPreview = document.getElementById('newUserAvatarPreview');

        if (!displayNameInput || !avatarPreview) {
            return;
        }

        const updateAvatarPreview = function () {
            const value = displayNameInput.value.trim();
            avatarPreview.textContent = value ? value.charAt(0).toUpperCase() : 'U';
        };

        displayNameInput.addEventListener('input', updateAvatarPreview);
    })();
</script>
@endsection