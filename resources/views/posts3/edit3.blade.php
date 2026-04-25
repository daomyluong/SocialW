@extends('layouts.app')

@section('title', 'Chỉnh sửa bài viết - TV3 Thanh')

@section('content')
<div class="post-create-container">
    <div class="post-header mb-4">
        <h4 class="fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Chỉnh sửa bài viết</h4>
        <p class="text-muted small">Cập nhật lại nội dung hoặc hình ảnh cho bài viết của bạn.</p>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-4">
            <form action="{{ route('posts3.update', $post->id) }}" method="POST" enctype="multipart/form-data" id="editForm">
                @csrf 
                @method('PUT')

                {{-- Nơi lưu trữ ID của các ảnh cũ muốn xóa --}}
                <div id="deletePhotosContainer"></div>
                
                {{-- Khu vực nhập nội dung --}}
                <div class="mb-4">
                    <textarea name="content" class="form-control border-0 bg-light" rows="5" style="border-radius: 12px; resize: none;" required>{{ $post->content }}</textarea>
                </div>

                {{-- Khu vực quản lý hình ảnh --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Hình ảnh bài viết</label>
                    <div class="image-upload-wrapper">
                        
                        {{-- HIỂN THỊ ẢNH CŨ CÓ NÚT XÓA --}}
                        @if($post->media->count() > 0)
                            <div class="mb-3">
                                <p class="small text-muted mb-2">Ảnh hiện có (Nhấn X để xóa):</p>
                                <div class="d-flex flex-wrap gap-3 mb-3">
                                
                                                                @foreach($post->media as $m)
                            <div class="preview-item old-photo" data-id="{{ $m->id }}">
                                @php
                                    // Chỉ lấy tên file, loại bỏ mọi tiền tố 'public/' hay 'storage/' nếu lỡ có
                                    $fileName = basename($m->url); 
                                @endphp
                                
                                {{-- Trỏ thẳng vào thư mục posts nằm trong public --}}
                                <img src="{{ asset('posts/' . $fileName) }}" 
                                    class="rounded border shadow-sm"
                                    style="width: 85px; height: 85px; object-fit: cover;"
                                    onerror="this.onerror=null;this.src='https://placehold.co/100x100?text=Loi+Path';">
                                    
                                <div class="btn-remove btn-remove-old">&times;</div>
                            </div>
                        @endforeach
                                </div>
                                <hr>
                            </div>
                        @endif

                        {{-- CHỌN ẢNH MỚI --}}
                        <p class="small text-muted mb-2">Thêm ảnh mới:</p>
                        <input type="file" name="image[]" id="postImage" multiple class="form-control" accept="image/*">
                        <div id="imagePreview" class="d-flex flex-wrap gap-3 mt-3"></div>

                        <div class="mt-2 small text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i> Định dạng: JPG, PNG.
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <select name="visibility" class="form-select" style="border-radius: 10px;">
                            <option value="public" {{ $post->visibility == 'public' ? 'selected' : '' }}>Công khai</option>
                            <option value="follower" {{ $post->visibility == 'follower' ? 'selected' : '' }}>Người theo dõi</option>
                            <option value="private" {{ $post->visibility == 'private' ? 'selected' : '' }}>Riêng tư</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('posts3.myPosts') }}" class="btn btn-light px-4 me-2" style="border-radius: 10px;">Hủy</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="border-radius: 10px;">Lưu thay đổi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .post-create-container { max-width: 100%; margin: 0 auto; }
    .preview-item { position: relative; width: 85px; height: 85px; }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; border: 2px solid #eee; }
    .btn-remove {
        position: absolute; top: -8px; right: -8px; background: #ff4757; color: white;
        width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; font-size: 14px; cursor: pointer; border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 10;
    }
    .old-photo img { border-color: #ffc107; } /* Đánh dấu ảnh cũ màu vàng nhẹ */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. XỬ LÝ XÓA ẢNH CŨ
    const deletePhotosContainer = document.getElementById('deletePhotosContainer');
    document.querySelectorAll('.btn-remove-old').forEach(button => {
        button.addEventListener('click', function() {
            const parent = this.parentElement;
            const photoId = parent.getAttribute('data-id');

            // Thêm ID vào input ẩn để gửi lên server
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'delete_images[]';
            hiddenInput.value = photoId;
            deletePhotosContainer.appendChild(hiddenInput);

            // Ẩn ảnh trên giao diện
            parent.remove();
        });
    });

    // 2. XỬ LÝ XEM TRƯỚC VÀ XÓA ẢNH MỚI CHỌN (Giống trang create)
    const postImage = document.getElementById('postImage');
    const previewContainer = document.getElementById('imagePreview');
    let dt = new DataTransfer();

    postImage.addEventListener('change', function() {
        const files = this.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!file.type.match('image.*')) continue;
            dt.items.add(file);
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.setAttribute('data-name', file.name);
                div.innerHTML = `<img src="${e.target.result}"><div class="btn-remove">&times;</div>`;
                div.querySelector('.btn-remove').addEventListener('click', function() {
                    const name = div.getAttribute('data-name');
                    div.remove();
                    const newDt = new DataTransfer();
                    for (let j = 0; j < dt.files.length; j++) {
                        if (dt.files[j].name !== name) newDt.items.add(dt.files[j]);
                    }
                    dt = newDt;
                    postImage.files = dt.files;
                });
                previewContainer.appendChild(div);
            }
            reader.readAsDataURL(file);
        }
        postImage.files = dt.files;
    });
});
</script>
@endsection