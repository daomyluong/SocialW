@extends('layouts.app')

@section('content')
<style>
    .container-fluid {
        padding-right: 15px;
        padding-left: 15px;
    }

    .feed-hero {
        background: linear-gradient(135deg, #e8f1ff 0%, #f8fbff 62%, #fff6ea 100%);
        border: 1px solid #e6eef9;
        border-radius: 20px;
        padding: 18px;
    }

    .post-card {
        border: 1px solid #e8edf5;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(20, 40, 70, 0.05);
    }

    .post-toolbar button {
        border: 0;
        background: transparent;
        color: #4f5b6b;
        font-weight: 600;
    }

    .post-toolbar button:hover {
        color: #0d6efd;
    }

    .story-badge {
        min-width: 86px;
    }

    .bookmark-btn .fa-bookmark {
        font-size: 1.05rem;
    }
</style>

<div class="container feed-shell">
    <div id="feedFlashMessage"></div>

    @if(session('success'))
    <div id="successAlert" class="alert alert-success">
        {{ session('success') }}
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('successAlert').style.display = 'none';
        }, 5000); // 5000ms = 5 giây
    </script>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @auth
    <div class="feed-hero mb-4">
        <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3">
            @csrf
            <input type="text" name="content" class="form-control border-0" style="border-radius: 14px; background: #fff;" placeholder="Bạn đang nghĩ gì, {{ auth()->user()?->display_name ?? 'bạn' }}?">
            <input type="hidden" name="visibility" value="public">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <label for="homePostImage" class="btn btn-outline-primary btn-sm rounded-pill mb-0"><i class="fa-regular fa-image me-1"></i> Ảnh</label>
                <input type="file" id="homePostImage" name="image[]" class="d-none" accept="image/*" multiple>
                <label for="homePostVideo" class="btn btn-outline-danger btn-sm rounded-pill mb-0"><i class="fa-solid fa-video me-1"></i> Video</label>
                <input type="file" id="homePostVideo" name="video[]" class="d-none" accept="video/*" multiple>
                <div id="imagePreviewContainer" class="d-flex gap-2 mb-2"></div>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Đăng bài</button>
            </div>
        </form>
        <hr>
        <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
            @csrf
            <input type="file" name="media" class="form-control form-control-sm" accept="image/*,video/*" required>
            <button type="submit" class="btn btn-warning btn-sm rounded-pill">Đăng story</button>
        </form>
    </div>
    @endauth

    @if(isset($stories) && $stories->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex gap-3 overflow-auto">
            @foreach($stories as $userId => $userStories)
            @php
            $storyOwner = $userStories->first()?->user;
            @endphp
            <div class="text-center story-badge">
                <div class="rounded-circle p-1 mx-auto mb-1" style="width: 58px; height: 58px; background: linear-gradient(45deg, #f59e0b, #ef4444, #db2777);">
                    <img src="{{ $storyOwner?->avatar_url ? asset($storyOwner->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($storyOwner?->display_name ?? ('User '.$userId)).'&background=random' }}" class="rounded-circle border border-2 border-white w-100 h-100" style="object-fit: cover;" alt="story">
                </div>
                <small class="text-muted d-block text-truncate">{{ $storyOwner?->display_name ?? $storyOwner?->username ?? ('User #'.$userId) }}</small>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @php
    $stories = \App\Models\Story3::latest()->get();
@endphp

<div class="mb-4">
    <h5>Story</h5>

    <div class="d-flex gap-2">
        @foreach($stories as $story)
            @if($story->type == 'image')
                <img src="{{ asset($story->media_url) }}" width="100" style="border-radius:10px;">
            @else
                <video width="100" controls>
                    <source src="{{ asset($story->media_url) }}">
                </video>
            @endif
        @endforeach
    </div>
</div>

    <h5 class="fw-bold mb-3">Bảng tin</h5>

    <div id="feed-container">
        @forelse($posts as $post)
    @include('components.post-card', ['post' => $post])
@empty
    <div class="alert alert-light border">Chưa có bài viết nào.</div>
@endforelse
    </div>

    <div class="modal fade" id="saveToFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Lưu bài viết vào thư mục</h5>
                </div>
                <div class="modal-body">
                    <select id="folderSelect" class="form-select mb-3">
                        <option value="Tất cả">Tất cả</option>
                    </select>
                    <input type="text" id="newFolderName" class="form-control" placeholder="Hoặc nhập tên thư mục mới...">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="submitBookmark()">Xác nhận lưu</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (() => {
        const csrf = "{{ csrf_token() }}";
        const imageInput = document.getElementById('homePostImage');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');

        if (imageInput && imagePreviewContainer) {
            imageInput.addEventListener('change', function() {
                imagePreviewContainer.innerHTML = '';

                Array.from(this.files).forEach((file) => {
                    const reader = new FileReader();

                    reader.onload = (event) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'position-relative';
                        wrapper.style.display = 'inline-block';

                        wrapper.innerHTML = `
                            <img src="${event.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0">X</button>
                        `;

                        wrapper.querySelector('button').addEventListener('click', () => wrapper.remove());
                        imagePreviewContainer.appendChild(wrapper);
                    };

                    reader.readAsDataURL(file);
                });
            });
        }

        document.body.addEventListener('submit', async (event) => {
            const likeForm = event.target.closest('.ajax-like-form');
            const shareForm = event.target.closest('.ajax-share-form');

            if (!likeForm && !shareForm) {
                return;
            }

            event.preventDefault();

            const form = likeForm || shareForm;
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                console.error('API Error:', response.status, await response.text());
                alert('Có lỗi: ' + response.status);
                return;
            }

            const data = await response.json();

            if (likeForm) {
                const countEl = likeForm.querySelector('.like-count');
                if (countEl && data.likeCount !== undefined) {
                    countEl.textContent = data.likeCount;
                }

                const icon = likeForm.querySelector('.like-icon');
                if (icon && data.liked !== undefined) {
                    icon.classList.toggle('fa-solid', data.liked);
                    icon.classList.toggle('fa-regular', !data.liked);
                    icon.classList.toggle('text-danger', data.liked);
                }
                return;
            }

            if (shareForm) {
                const countEl = shareForm.closest('.post-card')?.querySelector('.share-count');
                if (countEl && data.shareCount !== undefined) {
                    countEl.textContent = data.shareCount;
                }

                const input = shareForm.querySelector('input[name="comment"]');
                if (input) {
                    input.value = '';
                }

                const collapseEl = shareForm.closest('.collapse');
                if (collapseEl && window.bootstrap) {
                    const instance = bootstrap.Collapse.getOrCreateInstance(collapseEl, {
                        toggle: false
                    });
                    instance.hide();
                }

                const flash = document.getElementById('feedFlashMessage');
                if (flash) {
                    flash.innerHTML = `<div class="alert alert-success mb-3">${data.message ?? 'Bạn đã chia sẻ bài viết.'}</div>`;
                }
            }
        });

        document.body.addEventListener('click', async (event) => {
            const bookmarkBtn = event.target.closest('.bookmark-btn');
            if (bookmarkBtn) {
                const postId = bookmarkBtn.getAttribute('data-post-id');
                try {
                    const response = await fetch(`/bookmarks/toggle/${postId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                        },
                        body: JSON.stringify({
                            folder_name: 'Tất cả',
                        }),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    const icon = bookmarkBtn.querySelector('i');
                    const added = payload.status === 'added';
                    icon.classList.toggle('fa-solid', added);
                    icon.classList.toggle('fa-regular', !added);
                    icon.classList.toggle('text-primary', added);
                    icon.classList.toggle('text-secondary', !added);
                } catch (error) {
                    // ignore
                }
                return;
            }

            const followBtn = event.target.closest('.follow-btn');
            if (followBtn) {
                const userId = followBtn.getAttribute('data-user-id');
                try {
                    const response = await fetch(`/users/${userId}/follow`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    if (data.status) {
                        const followed = data.status === 'followed';
                        document.querySelectorAll(`.follow-btn[data-user-id="${userId}"]`).forEach((btn) => {
                            btn.classList.toggle('btn-primary', followed);
                            btn.classList.toggle('btn-outline-primary', !followed);
                            btn.classList.toggle('text-primary', !followed);
                            btn.classList.toggle('text-secondary', followed);

                            const textNode = btn.querySelector('.follow-text');
                            if (textNode) {
                                textNode.textContent = followed ? 'Đang theo dõi' : 'Theo dõi';
                            } else {
                                btn.textContent = followed ? 'Đang theo dõi' : 'Theo dõi';
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
                return;
            }

            const loadMoreBtn = event.target.closest('.load-more-btn');
            if (!loadMoreBtn) {
                return;
            }

            const postId = loadMoreBtn.getAttribute('data-post-id');
            const container = document.getElementById(`extra-comments-${postId}`);
            if (!container) {
                return;
            }

            if (container.innerHTML.trim() !== '') {
                container.classList.toggle('d-none');
                return;
            }

            try {
                const response = await fetch(`/posts/${postId}/load-more-comments`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                container.innerHTML = payload.html || '';
            } catch (error) {
                // ignore
            }
        });
    })();
    let currentPostId = null;
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.bookmark-btn');
        if (btn) {
            currentPostId = btn.dataset.postId;
            const res = await fetch('{{ route("bookmarks.folders") }}');
            const folders = await res.json();
            const select = document.getElementById('folderSelect');
            select.innerHTML = '<option value="Tất cả">Tất cả</option>';
            folders.forEach(f => {
                if (f !== 'Tất cả') select.innerHTML += `<option value="${f}">${f}</option>`;
            });
            new bootstrap.Modal(document.getElementById('saveToFolderModal')).show();
        }
    });

    async function submitBookmark() {
        const newFolder = document.getElementById('newFolderName').value;
        const selectedFolder = document.getElementById('folderSelect').value;
        const folderName = newFolder || selectedFolder;

        const res = await fetch(`/bookmarks/toggle/${currentPostId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                folder_name: folderName
            })
        });
        if (res.ok) location.reload();
    }
</script>

@endsection