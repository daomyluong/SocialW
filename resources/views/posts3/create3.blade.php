@extends('layouts.app') {{-- Thừa hưởng khung xương từ TV1 --}}

@section('title', 'Tạo bài viết mới - TV3 Thanh')

@section('content')
<div class="post-create-container">
    <div class="post-header mb-4">
        <h4 class="fw-bold"><i class="fa-regular fa-square-plus me-2"></i>Tạo bài viết mới</h4>
        <p class="text-muted small">Chia sẻ những khoảnh khắc thú vị của bạn với cộng đồng W-Social.</p>
    </div>

    {{-- Hiển thị thông báo thành công nếu có --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-4">
            {{-- Form gửi dữ liệu đến PostController3 --}}
            <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf {{-- Token bảo mật --}}
                
                {{-- Khu vực nhập nội dung --}}
                <div class="mb-4">
                    <textarea name="content" 
                              class="form-control border-0 bg-light" 
                              rows="5" 
                              placeholder="Bạn đang nghĩ gì thế?" 
                              style="border-radius: 12px; resize: none;" 
                              required></textarea>
                </div>

                {{-- Khu vực tải ảnh --}}
                <div class="mb-4">
                    <label class="form-label fw-bold small text-uppercase text-muted">Thêm hình ảnh</label>
                    <div class="image-upload-wrapper">
                        <input type="file" name="image[]" id="postImage" multiple class="form-control" accept="image/*">
                        
                        {{-- Khung chứa ảnh xem trước --}}
                        <div id="imagePreview" class="d-flex flex-wrap gap-3 mt-3"></div>

                        <div class="mt-2 small text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i> Định dạng hỗ trợ: JPG, PNG (Tối đa 2MB)
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    {{-- Quyền riêng tư --}}
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                                <i class="fa-solid fa-earth-americas text-muted"></i>
                            </span>
                            <select name="visibility" class="form-select border-start-0" style="border-radius: 0 10px 10px 0;">
                                <option value="public">Công khai</option>
                                <option value="follower">Người theo dõi</option>
                                <option value="private">Riêng tư</option>
                            </select>
                        </div>
                    </div>

                    {{-- Nút đăng bài --}}
                    <div class="col-md-6 text-md-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="border-radius: 10px;">
                            Đăng bài <i class="fa-solid fa-paper-plane ms-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .post-create-container {
        max-width: 100%;
        margin: 0 auto;
    }

    .form-control:focus, .form-select:focus {
        box-shadow: none;
        border-color: var(--hlink-blue);
    }

    .image-upload-wrapper input::file-selector-button {
        background-color: var(--threads-gray);
        border: none;
        padding: 8px 20px;
        border-radius: 8px;
        margin-right: 15px;
        cursor: pointer;
        transition: 0.3s;
    }

    .image-upload-wrapper input::file-selector-button:hover {
        background-color: #e2e8f0;
    }

    /* Hiệu ứng cho thông báo */
    .alert {
        border-radius: 12px;
        animation: slideDown 0.5s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Style cho khung preview ảnh */
    .preview-item {
        position: relative;
        width: 85px;
        height: 85px;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #eee;
    }

    /* Nút xóa ảnh nhỏ màu đỏ */
    .btn-remove {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff4757;
        color: white;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }

    .btn-remove:hover {
        background: #ff6b81;
        transform: scale(1.1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const postImage = document.getElementById('postImage');
    const previewContainer = document.getElementById('imagePreview');
    
    // Mảng lưu trữ các file thực tế sẽ được gửi đi
    let dt = new DataTransfer();

    postImage.addEventListener('change', function() {
        const files = this.files;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Chỉ nhận file ảnh
            if (!file.type.match('image.*')) continue;

            // Thêm file vào DataTransfer object
            dt.items.add(file);

            const reader = new FileReader();
            reader.onload = function(e) {
                // Tạo element hiển thị
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.setAttribute('data-name', file.name); // Lưu tên để tìm và xóa sau này
                
                div.innerHTML = `
                    <img src="${e.target.result}">
                    <div class="btn-remove">&times;</div>
                `;

                // Xử lý sự kiện xóa
                div.querySelector('.btn-remove').addEventListener('click', function() {
                    const name = div.getAttribute('data-name');
                    div.remove();
                    
                    // Tạo lại DataTransfer mới, bỏ đi file vừa xóa
                    const newDt = new DataTransfer();
                    for (let j = 0; j < dt.files.length; j++) {
                        if (dt.files[j].name !== name) {
                            newDt.items.add(dt.files[j]);
                        }
                    }
                    dt = newDt;
                    postImage.files = dt.files; // Cập nhật lại input file
                });

                previewContainer.appendChild(div);
            }
            reader.readAsDataURL(file);
        }
        
        // Cập nhật lại input file bằng danh sách trong dt
        postImage.files = dt.files;
    });
});
</script>
@endsection