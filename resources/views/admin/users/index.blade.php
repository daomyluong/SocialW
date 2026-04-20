@extends('layouts.admin')

@section('admin_title', 'Quản Lý Người Dùng')

@section('content')
<style>
    .card-pastel { border-radius: 1rem; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .table-custom th { border-bottom: none; color: #a4b0be; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; background-color: #f8fafc; padding: 15px 20px;}
    .table-custom td { vertical-align: middle; border-bottom: 1px solid #f1f2f6; padding: 15px 20px; transition: 0.2s;}
    .table-custom tbody tr:hover td { background-color: #f0f4fd; }
    .bg-soft-blue { background-color: #eef6ff; color: #0062ff; }
    .bg-soft-red { background-color: #fcebeb; color: #dc3545; }
    .action-btn { width: 35px; height: 35px; border-radius: 0.5rem; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; }
    .action-btn:hover { opacity: 0.8; transform: translateY(-2px); }
</style>

<div class="container-fluid px-0">

    <div class="card card-pastel mb-4 p-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tìm tên hoặc email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select text-muted">
                    <option value="">Tất cả Vai trò</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select text-muted">
                    <option value="">Tất cả Trạng thái</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 0.5rem;">Lọc dữ liệu</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="#" class="btn btn-success fw-bold w-100" style="border-radius: 0.5rem;"><i class="fa-solid fa-plus me-2"></i>Thêm User</a>
            </div>
        </form>
    </div>

    <div class="card card-pastel">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thông tin tài khoản</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tham gia</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admin_users as $user)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->display_name) }}&background=random&color=fff" alt="Avatar" class="rounded-circle me-3" width="45" height="45">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $user->display_name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role == 'admin')
                                    <span class="badge bg-soft-blue px-3 py-2 rounded-pill">Quản trị viên</span>
                                @else
                                    <span class="badge bg-light text-secondary px-3 py-2 rounded-pill">Người dùng</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-circle me-1" style="font-size: 0.5rem;"></i> Hoạt động</span>
                                @else
                                    <span class="badge bg-soft-red px-3 py-2 rounded-pill"><i class="fa-solid fa-lock me-1" style="font-size: 0.5rem;"></i> Đã khóa</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="#" class="action-btn bg-light text-primary me-1" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                                
                                <a href="#" class="action-btn bg-light text-warning me-1" title="Chỉnh sửa"><i class="fa-solid fa-pen"></i></a>

                                <form action="{{ route('admin.users.toggle_status', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="action-btn {{ $user->is_active == 1 ? 'bg-soft-red' : 'bg-success bg-opacity-10 text-success' }} me-1" title="{{ $user->is_active == 1 ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                        <i class="fa-solid {{ $user->is_active == 1 ? 'fa-lock' : 'fa-unlock' }}"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
                                    @csrf
                                    <button type="submit" class="action-btn bg-danger text-white" title="Xóa tài khoản"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
                                <h5>Không tìm thấy người dùng nào!</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4 pb-3">
                {{ $admin_users->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection