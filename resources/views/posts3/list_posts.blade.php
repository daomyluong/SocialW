{{-- File: resources/views/posts3/list_posts.blade.php --}}
<h5 class="fw-bold mb-4">Dành cho bạn</h5>

@forelse($posts as $post)
    <div class="post-item mb-4 border-bottom pb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle me-2" width="40" height="40">
                <div>
                    <span class="fw-bold">Người dùng #{{ $post->author_user_id }}</span>
                    <small class="text-muted d-block">{{ $post->created_at->diffForHumans() }}</small>
                </div>
            </div>

            {{-- Nút menu sửa/xóa (Chỉ hiện nếu là chủ bài viết) --}}
            @if(auth()->check() && auth()->id() == $post->author_user_id)
            <div class="dropdown">
                <button class="btn btn-link text-secondary p-0 border-0" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-ellipsis"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                    <li><a class="dropdown-item" href="{{ route('posts3.edit', $post->id) }}">Sửa bài</a></li>
                    <li>
                        <form action="{{ route('posts3.destroy', $post->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Xóa bài này?')">Xóa bài</button>
                        </form>
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <div class="post-content ps-5">
            <p>{{ $post->content }}</p>

            {{-- Hiển thị ảnh thật từ Database --}}
            @if($post->media && $post->media->count() > 0)I
                <div class="rounded-4 overflow-hidden border mb-3">
                    <div class="row g-1">
                        @foreach($post->media as $media)
                            <div class="{{ $post->media->count() == 1 ? 'col-12' : 'col-6' }}">
                                <img src="{{ asset($media->url) }}" class="img-fluid w-100" style="max-height: 400px; object-fit: cover;">
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