@extends('layouts.admin')

@section('admin_title', 'Bảng Điều Khiển')

@section('content')
<style>
    /* CSS làm mịn giao diện theo phong cách Pastel / Glass */
    .card-pastel { border-radius: 1.25rem; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: transform 0.2s; }
    .card-pastel:hover { transform: translateY(-3px); }
    .icon-box { width: 50px; height: 50px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    
    /* Bộ mã màu Thanh nhã */
    .bg-soft-blue { background-color: #f0f4fd; color: #4b7bec; }
    .bg-soft-green { background-color: #eafaf1; color: #20bf6b; }
    .bg-soft-purple { background-color: #f6f0fb; color: #8854d0; }
    .bg-soft-peach { background-color: #fdf4ec; color: #fa8231; }
    
    .table-custom th { border-bottom: none; color: #a4b0be; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }
    .table-custom td { vertical-align: middle; border-bottom: 1px solid #f1f2f6; }
</style>

<div class="container-fluid px-0">
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card card-pastel h-100 p-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1" style="font-size: 0.85rem;">NGƯỜI DÙNG</p>
                        <h3 class="mb-0 fw-bolder text-dark">{{ $admin_total_users }}</h3>
                    </div>
                    <div class="icon-box bg-soft-blue"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-pastel h-100 p-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1" style="font-size: 0.85rem;">BÀI VIẾT</p>
                        <h3 class="mb-0 fw-bolder text-dark">{{ $admin_total_posts }}</h3>
                    </div>
                    <div class="icon-box bg-soft-green"><i class="fa-solid fa-layer-group"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-pastel h-100 p-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1" style="font-size: 0.85rem;">LƯỢT THÍCH</p>
                        <h3 class="mb-0 fw-bolder text-dark">{{ $admin_total_likes }}</h3>
                    </div>
                    <div class="icon-box bg-soft-purple"><i class="fa-solid fa-heart"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-pastel h-100 p-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted fw-bold mb-1" style="font-size: 0.85rem;">BỊ KHÓA</p>
                        <h3 class="mb-0 fw-bolder text-dark">{{ $admin_banned_users }}</h3>
                    </div>
                    <div class="icon-box bg-soft-peach"><i class="fa-solid fa-shield-halved"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-lg-8">
            <div class="card card-pastel h-100 p-3">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Tăng trưởng bài viết</h6>
                    <span class="badge bg-soft-blue px-3 py-2 rounded-pill">7 ngày qua</span>
                </div>
                <div class="card-body">
                    <canvas id="postsChart" height="90"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-pastel h-100 p-3">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold text-dark mb-0">Hoạt động gần đây</h6>
                </div>
                <div class="card-body px-2">
                    @if($admin_recent_actions->isEmpty())
                        <p class="text-muted text-center mt-4">Chưa có hoạt động.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($admin_recent_actions as $action)
                            <li class="list-group-item px-0 py-2 border-0 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-soft-purple me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fa-solid fa-bolt"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-dark fw-bold" style="font-size: 0.85rem;">{{ $action->note }}</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($action->created_at)->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-pastel p-3 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold text-dark mb-0">Top Thành Viên (Nhiều Follow nhất)</h6>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead><tr><th>Người dùng</th><th>Vai trò</th><th class="text-end">Followers</th></tr></thead>
                            <tbody>
                                @foreach($top_users as $user)
                                @php
                                    $userRole = strtolower((string) data_get($user, 'role', 'member'));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box bg-soft-blue me-2 rounded-circle" style="width: 35px; height: 35px; font-size: 1rem;"><i class="fa-solid fa-user"></i></div>
                                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $user->display_name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge {{ $userRole == 'admin' ? 'bg-soft-purple' : 'bg-light text-secondary' }} rounded-pill">{{ ucfirst($userRole) }}</span></td>
                                    <td class="text-end fw-bold text-dark">{{ $user->follower_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-pastel p-3 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="fw-bold text-dark mb-0">Bài Viết Nổi Bật (Nhiều Like nhất)</h6>
                </div>
                <div class="card-body p-0 mt-2">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead><tr><th>Nội dung</th><th>Chế độ</th><th class="text-end">Lượt Thích</th></tr></thead>
                            <tbody>
                                @foreach($top_posts as $post)
                                <tr>
                                    <td>
                                        <div class="text-truncate text-dark fw-bold" style="max-width: 250px; font-size: 0.9rem;">
                                            {{ $post->content }}
                                        </div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y') }}</small>
                                    </td>
                                    <td><span class="badge bg-soft-green rounded-pill">{{ ucfirst($post->visibility) }}</span></td>
                                    <td class="text-end fw-bold text-danger"><i class="fa-solid fa-heart me-1"></i>{{ $post->like_count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var chartLabels = {!! json_encode($chart_labels) !!};
    var chartData = {!! json_encode($chart_data) !!};

    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('postsChart').getContext('2d');
        
        // Tạo gradient màu tím/xanh cho biểu đồ cho điệu đà
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(136, 84, 208, 0.2)'); // Tím nhạt
        gradient.addColorStop(1, 'rgba(136, 84, 208, 0)');

        const postsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Số bài viết mới',
                    data: chartData,
                    backgroundColor: gradient,
                    borderColor: '#8854d0', // Màu tím chủ đạo
                    borderWidth: 3,
                    tension: 0.4, // Đường cong mềm
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#8854d0',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } }, // Ẩn lưới dọc cho mượt
                    y: { beginAtZero: true, ticks: { precision: 0 }, border: { dash: [5, 5] } } 
                }
            }
        });
    });
</script>
@endsection