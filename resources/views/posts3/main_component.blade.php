{{-- 1. HIỂN THỊ THÔNG BÁO --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <i class="fa-solid fa-circle-check me-2"></i> {!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- 2. KHUNG ĐĂNG BÀI NHANH (Thanh tự quản lý logic này) --}}
<div class="card mb-4 border-0 border-bottom shadow-sm" style="border-radius: 15px;">
    <div class="card-body">
        <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data" id="homeUploadForm">
            @csrf
            <div class="d-flex">
                <div class="avatar bg-light rounded-circle me-3" style="width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-user fa-xl text-secondary"></i>
                </div>
                <div class="w-100">
                    <input type="text" name="content" class="form-control border-0 bg-light" style="border-radius: 20px;" placeholder="Bạn đang nghĩ gì thế?" required>
                    <input type="hidden" name="visibility" value="public">
                    
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-3 text-primary">
                            <label for="homePostImage" class="mb-0" style="cursor: pointer;">
                                <small><i class="fa-regular fa-image me-1"></i> Ảnh/Video</small>
                            </label>
                            {{-- QUAN TRỌNG: Phải có [] và multiple --}}
                            <input type="file" name="image[]" id="homePostImage" class="d-none" accept="image/*" multiple>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Đăng bài</button>
                    </div>

                    {{-- Khung xem trước ảnh có nút xóa --}}
                    <div id="homeImagePreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>
            </div>
        </form>
    </div>
</div>

<hr>

{{-- 3. DANH SÁCH BÀI VIẾT LẤY TỪ BIẾN $posts --}}
<h5 class="fw-bold mb-4">Dành cho bạn</h5>
@forelse($posts as $post)
    <div class="post-item mb-4 border-bottom pb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle me-2" width="40" height="40">
                <div>
                    <span class="fw-bold">User #{{ $post->author_user_id }}</span>
                    <small class="text-muted d-block">{{ $post->created_at->diffForHumans() }}</small>
                </div>
            </div>

            {{-- Chỉ hiện menu Sửa/Xóa nếu là chủ bài viết --}}
            @if(auth()->id() == $post->author_user_id)
            <div class="dropdown">
                <button class="btn btn-link text-secondary p-0 border-0" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-ellipsis"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('posts3.edit', $post->id) }}"><i class="fa-solid fa-pen me-2"></i>Sửa</a></li>
                    <li>
                        <form action="{{ route('posts3.destroy', $post->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Xóa bài này?')">
                                <i class="fa-solid fa-trash me-2"></i>Xóa
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <div class="post-content ps-5">
            <p>{{ $post->content }}</p>

            {{-- HIỂN THỊ LƯỚI ẢNH THẬT --}}
            @if($post->media->count() > 0)
                <div class="rounded-4 overflow-hidden border mb-3">
                    <div class="row g-1">
                        @foreach($post->media as $m)
                            <div class="{{ $post->media->count() == 1 ? 'col-12' : 'col-6' }}">
                                <img src="{{ asset($m->url) }}" class="w-100" style="height: 250px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="post-actions d-flex gap-4 text-secondary">
                <span><i class="fa-regular fa-heart me-1"></i> Thích</span>
                <span><i class="fa-regular fa-comment me-1"></i> Bình luận</span>
            </div>
        </div>
    </div>
@empty
    <p class="text-center text-muted">Chưa có bài viết nào.</p>
@endforelse

{{-- 4. CSS VÀ JS (Viết luôn vào đây để không cần đụng file khác) --}}
<style>
    .preview-item-home { position: relative; width: 70px; height: 70px; }
    .preview-item-home img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    .remove-home-img { 
        position: absolute; top: -5px; right: -5px; background: red; color: white; 
        border-radius: 50%; width: 18px; height: 18px; font-size: 12px; 
        display: flex; align-items: center; justify-content: center; cursor: pointer; 
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('homePostImage');
    const previewContainer = document.getElementById('homeImagePreview');
    let dtHome = new DataTransfer();

    if(fileInput) {
        fileInput.addEventListener('change', function() {
            const files = this.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (!file.type.match('image.*')) continue;
                dtHome.items.add(file);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item-home';
                    div.setAttribute('data-name', file.name);
                    div.innerHTML = `<img src="${e.target.result}"><div class="remove-home-img">&times;</div>`;
                    
                    div.querySelector('.remove-home-img').addEventListener('click', function() {
                        const name = div.getAttribute('data-name');
                        div.remove();
                        const newDt = new DataTransfer();
                        for (let j = 0; j < dtHome.files.length; j++) {
                            if (dtHome.files[j].name !== name) newDt.items.add(dtHome.files[j]);
                        }
                        dtHome = newDt;
                        fileInput.files = dtHome.files;
                    });
                    previewContainer.appendChild(div);
                }
                reader.readAsDataURL(file);
            }
            fileInput.files = dtHome.files;
        });
    }
});
</script>