@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    <h4 class="fw-bold mb-4">Thông báo</h4>

    <div class="list-group shadow-sm border-0">
        @forelse($notifications as $noti)
            {{-- CHỈNH SỬA: Chuyển div thành thẻ a và thêm href dẫn đến bài viết --}}
                <a href="{{ $noti->post_id ? route('posts3.show', $noti->post_id) : route('notifications.index') }}" 
               class="list-group-item list-group-item-action border-0 border-bottom py-3 {{ $noti->is_read == 0 ? 'bg-light' : '' }}" 
               style="border-radius: 10px; text-decoration: none; color: inherit;">
                
                <div class="d-flex align-items-center">
                    {{-- Avatar: Lấy theo actor_user_id --}}
                    <div class="avatar me-3">
                        <img src="https://ui-avatars.com/api/?name=User{{ $noti->sender_id }}&background=random" class="rounded-circle" width="45">
                    </div>
                    
                    <div class="flex-grow-1">
                        <p class="mb-0 text-dark">
                            <span class="fw-bold">User #{{ $noti->sender_id }}</span> 
                            @if($noti->type == 'like')
                                đã thích bài viết của bạn.
                            @elseif($noti->type == 'comment')
                                đã bình luận về bài viết của bạn.
                            @elseif($noti->type == 'follow')
                                đã bắt đầu theo dõi bạn.
                            @else
                                đã tương tác với bạn.
                            @endif
                        </p>
                        <small class="text-muted">
                            <i class="fa-regular fa-clock me-1"></i>{{ $noti->created_at->diffForHumans() }}
                        </small>
                    </div>

                    {{-- Icon hiển thị loại tương tác --}}
                    <div>
                        @if($noti->type == 'like')
                            <i class="fa-solid fa-heart text-danger"></i>
                        @elseif($noti->type == 'comment')
                            <i class="fa-solid fa-comment text-primary"></i>
                        @elseif($noti->type == 'follow')
                            <i class="fa-solid fa-user-plus text-success"></i>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                <i class="fa-regular fa-bell-slash fa-3x text-light mb-3"></i>
                <p class="text-muted">Bạn chưa có thông báo nào.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- CSS bổ sung để hiệu ứng di chuột đẹp hơn --}}
<style>
    .list-group-item-action:hover {
        background-color: #f8f9fa !important;
        transition: 0.3s;
    }
</style>
@endsection