@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    {{-- ======================================================= --}}
    {{-- HIỂN THỊ THÔNG BÁO THÀNH CÔNG (NẾU CÓ)                  --}}
    {{-- ======================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 15px;" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- PHẦN 1: KHUNG ĐĂNG BÀI NHANH (ĐÃ SỬA LỖI MẤT HÌNH)      --}}
    {{-- ======================================================= --}}
    <div class="card mb-4 border-0 border-bottom">
        <div class="card-body">
            <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="d-flex">
                    <div class="avatar bg-light rounded-circle me-3" style="width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-user fa-xl text-secondary"></i>
                    </div>
                    <div class="w-100">
                        {{-- Ô nhập nội dung --}}
                        <input type="text" name="content" class="form-control border-0 bg-light" style="border-radius: 20px;" placeholder="Bạn đang nghĩ gì, {{ Auth::user()->display_name ?? 'Thanh' }}?" required>
                        
                        <input type="hidden" name="visibility" value="public">

                        {{-- KHU VỰC XEM TRƯỚC ẢNH KHI CHỌN --}}
                        <div id="homeImagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3 text-primary">
                                {{-- Nút tải ảnh - Quan trọng: Đã đổi tên thành image[] và thêm multiple --}}
                                <label for="homePostImage" class="mb-0" style="cursor: pointer;">
                                    <small><i class="fa-regular fa-image me-1"></i> Ảnh/Video</small>
                                </label>
                                <input type="file" name="image[]" id="homePostImage" class="d-none" accept="image/*" multiple>
                                
                                <small style="cursor: pointer;"><i class="fa-solid fa-at me-1"></i> Nhắc tên</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Đăng</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h5 class="fw-bold mb-4">Dành cho bạn</h5>

    {{-- ======================================================= --}}
    {{-- PHẦN 2: DANH SÁCH BÀI VIẾT LẤY TỪ DATABASE              --}}
    {{-- ======================================================= --}}
    @forelse($posts as $post)
        <div class="post-item mb-4 border-bottom pb-3" id="post-{{ $post->id }}">
        <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center">
                    {{-- Avatar người đăng --}}
                    <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle me-2" width="40" height="40">
                    <div>
                        <span class="fw-bold">User #{{ $post->author_user_id }}</span>
                        <small class="text-muted d-block">{{ $post->created_at->diffForHumans() }}</small>
                    </div>
                </div>

                {{-- Nút menu sửa/xóa cho chủ bài viết --}}
                @if(Auth::id() == $post->author_user_id)
                <div class="dropdown">
                    <button class="btn btn-link text-secondary p-0" data-bs-toggle="dropdown" style="text-decoration: none;">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><a class="dropdown-item" href="{{ route('posts3.edit', $post->id) }}"><i class="fa-solid fa-pen me-2"></i>Chỉnh sửa</a></li>
                        <li>
                            <form action="{{ route('posts3.destroy', $post->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Bạn có chắc muốn xóa?')"><i class="fa-solid fa-trash me-2"></i>Xóa bài</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            <div class="post-content ps-5">
                <p>{{ $post->content }}</p>

                {{-- HIỂN THỊ HÌNH ẢNH THẬT --}}
                @if($post->media && $post->media->count() > 0)
                    <div class="rounded-4 overflow-hidden border mb-3">
                        <div class="row g-1">
                            @foreach($post->media as $m)
                                <div class="{{ $post->media->count() == 1 ? 'col-12' : 'col-6' }}">
                                    <img src="{{ asset($m->url) }}" class="img-fluid w-100" style="height: 250px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="post-actions d-flex align-items-center gap-4 text-secondary">
                    {{-- Nút Like --}}
                    <form action="{{ route('posts.like', $post->id) }}" method="POST" class="d-inline-flex m-0 align-items-center">
                        @csrf
                        <button type="submit" class="btn btn-link text-decoration-none p-0 d-flex align-items-center {{ $post->is_liked_by_me ? 'text-danger' : 'text-secondary' }}">
                            <i class="fa-{{ $post->is_liked_by_me ? 'solid' : 'regular' }} fa-heart me-1"></i> 
                            <span>{{ $post->like_count ?? 0 }} Thích</span>
                        </button>
                    </form>

                    {{-- NÚT BÌNH LUẬN --}}
                    <button class="btn btn-link text-decoration-none text-secondary p-0 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#commentForm{{ $post->id }}">
                        <i class="fa-regular fa-comment me-1"></i> 
                        <span>{{ $post->comment_count ?? 0 }} Bình luận</span>
                    </button>

                    {{-- NÚT CHIA SẺ --}}
                    <button class="btn btn-link text-decoration-none text-secondary p-0 d-flex align-items-center shadow-none" 
                            data-bs-toggle="modal" data-bs-target="#shareModal{{ $post->id }}">
                        <i class="fa-regular fa-share-from-square me-1"></i> 
                        <span id="share-count-{{ $post->id }}">{{ $post->share_count ?? 0 }}</span>&nbsp;Chia sẻ
                    </button>
                </div>
                 
                {{-- MODAL CHIA SẺ --}}
                <div class="modal fade" id="shareModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Chia sẻ bài viết</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('posts.share', $post->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Lời nhắn của bạn:</label>
                                        <textarea name="comment" class="form-control form-control-sm" rows="3" placeholder="Viết lời nhắn..."></textarea>
                                    </div>
                                    
                                    <p class="small text-muted mb-2">Gợi ý người dùng:</p>
                                    <div class="user-suggestions d-flex flex-wrap gap-2">
                                        @foreach($allUsers as $user)
                                            <span class="badge rounded-pill bg-light text-dark border p-2" style="cursor:pointer;" onclick="addMention('{{ $user->username }}', {{ $post->id }})">
                                                @ {{ $user->username }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Chia sẻ ngay</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- KHU VỰC HIỂN THỊ VÀ NHẬP BÌNH LUẬN --}}
                <div class="collapse mt-3" id="commentForm{{ $post->id }}">
                    {{-- Gọi Component danh sách bình luận --}}
                    <x-comment :post="$post" />

                    {{-- Form nhập bình luận mới --}}
                    <form action="{{ route('comments.store', $post->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="content" class="form-control form-control-sm rounded-pill" placeholder="Viết bình luận..." required>
                            <button class="btn btn-primary btn-sm rounded-pill ms-2" type="submit">Gửi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-muted py-5">Chưa có bài viết nào được đăng.</p>
    @endforelse
</div>

{{-- CSS hỗ trợ phần xem trước ảnh --}}
<style>
    .preview-box { position: relative; width: 60px; height: 60px; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .remove-btn { 
        position: absolute; top: -5px; right: -5px; background: red; color: white; 
        border-radius: 50%; width: 18px; height: 18px; font-size: 10px; 
        display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid white;
    }
</style>

{{-- SCRIPT xử lý chọn và xem trước ảnh (giúp input file luôn đúng định dạng) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inp = document.getElementById('homePostImage');
    const pre = document.getElementById('homeImagePreview');
    let dt = new DataTransfer();

    if(inp) {
        inp.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                if(!file.type.match('image.*')) return;
                dt.items.add(file);
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-box';
                    div.innerHTML = `<img src="${e.target.result}"><span class="remove-btn">&times;</span>`;
                    div.querySelector('.remove-btn').onclick = () => {
                        div.remove();
                        let ndt = new DataTransfer();
                        Array.from(dt.files).filter(f => f !== file).forEach(f => ndt.items.add(f));
                        dt = ndt;
                        inp.files = dt.files;
                    };
                    pre.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
            inp.files = dt.files;
        });
    }
});
</script>
@endsection

@section('suggestions')
    <div class="px-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-secondary m-0" style="font-size: 0.9rem;">Gợi ý cho bạn</h6>
            <a href="{{ route('users.suggestions') }}" class="text-dark fw-bold text-decoration-none" style="font-size: 0.75rem;">Xem tất cả</a>
        </div>

        @if(isset($suggestedUsers) && $suggestedUsers->count() > 0)
            <div class="d-flex flex-column gap-3" id="main-suggestion-list">
                @foreach($suggestedUsers as $user)
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=random" 
                                 class="rounded-circle me-2" width="32" height="32">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark" style="font-size: 0.85rem; line-height: 1;">{{ $user->username }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">Gợi ý cho bạn</span>
                            </div>
                        </div>
                        <button type="button" class="btn-follow-ig border-0 bg-transparent text-primary fw-bold p-0" 
                                style="font-size: 0.75rem;" data-user-id="{{ $user->id }}">
                            Theo dõi
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

{{-- Script này dùng chung cho cả trang Home và trang Suggestions --}}
<script>
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-follow-ig')) {
        const btn = e.target;
        const userId = btn.getAttribute('data-user-id');

        fetch(`/users/${userId}/follow`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'followed') {
                btn.innerText = 'Đang theo dõi';
                btn.classList.replace('text-primary', 'text-dark');
            } else {
                btn.innerText = 'Theo dõi';
                btn.classList.replace('text-dark', 'text-primary');
            }
        });
    }
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- PHẦN 1: GIỮ VỊ TRÍ CUỘN ---
        const scrollPos = localStorage.getItem('social_app_scrollpos');
        if (scrollPos) {
            setTimeout(() => {
                window.scrollTo({ top: parseInt(scrollPos), behavior: 'instant' });
                localStorage.removeItem('social_app_scrollpos');
            }, 100); 
        }

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                localStorage.setItem('social_app_scrollpos', window.scrollY);
            });
        });

        // --- PHẦN 2: XỬ LÝ AJAX XEM THÊM BÌNH LUẬN ---
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.load-more-btn');
            if (!btn) return;

            e.preventDefault();
            const postId = btn.getAttribute('data-post-id');
            const extraContainer = document.getElementById(`extra-comments-${postId}`);

            if (extraContainer.innerHTML.trim() !== "") {
                if (extraContainer.style.display === "none") {
                    extraContainer.style.display = "block";
                    btn.innerHTML = '<i class="fa-solid fa-angle-up me-1"></i> Thu gọn bình luận';
                } else {
                    extraContainer.style.display = "none";
                    btn.innerHTML = `<i class="fa-solid fa-comments me-1"></i> Xem thêm bình luận khác...`;
                }
                return;
            }

            const originalText = btn.innerHTML;
            btn.innerText = "Đang tải...";
            
            // LƯU Ý: Đường dẫn ở đây đã được cập nhật
            fetch(`/posts/${postId}/load-more-comments`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                extraContainer.innerHTML = data.html;
                extraContainer.style.display = "block";
                btn.innerHTML = '<i class="fa-solid fa-angle-up me-1"></i> Thu gọn bình luận';
            })
            .catch(error => {
                console.error('Error:', error);
                btn.innerHTML = originalText;
                alert("Không thể tải thêm bình luận lúc này.");
            });
        });

        // --- PHẦN 3: XỬ LÝ MENTION ---
        window.addMention = function(username, postId) {
            const textarea = document.querySelector(`#shareModal${postId} textarea`);
            if(textarea) {
                textarea.value += `@${username} `;
                textarea.focus();
            }
        }
    });
</script>