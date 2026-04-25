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

    @if(session('ccess'))
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
        {{-- Form Đăng Bài Nhanh --}}
        <form action="{{ route('posts3.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-3">
            @csrf
            <input type="text" name="content" class="form-control border-0" style="border-radius: 14px; background: #fff;" placeholder="Bạn đang nghĩ gì, {{ auth()->user()?->display_name ?? 'bạn' }}?">
            
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex gap-2 align-items-center">
                    <label for="homePostImage" class="btn btn-outline-primary btn-sm rounded-pill mb-0"><i class="fa-regular fa-image me-1"></i> Ảnh</label>
                    <input type="file" id="homePostImage" name="image[]" class="d-none" accept="image/*" multiple>
                    <label for="homePostVideo" class="btn btn-outline-danger btn-sm rounded-pill mb-0"><i class="fa-solid fa-video me-1"></i> Video</label>
                    <input type="file" id="homePostVideo" name="video[]" class="d-none" accept="video/*" multiple>
                    
                    {{-- THÊM PHẦN CHỌN QUYỀN RIÊNG TƯ --}}
                    <select name="visibility" class="form-select form-select-sm border-0 bg-white" style="width: auto; border-radius: 10px;">
                        <option value="public" selected>🌍 Công khai</option>
                        <option value="follower">👥 Người theo dõi</option>
                        <option value="private">🔒 Chỉ mình tôi</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Đăng bài</button>
            </div>
            <div id="imagePreviewContainer" class="d-flex gap-2 mb-2"></div>
        </form>

        <hr>
        
        {{-- Form Đăng Story --}}
        <form action="{{ route('stories.store') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
            @csrf
            {{-- Mặc định Story là public, nếu muốn sau này có thể thêm select y hệt ở trên --}}
            <input type="hidden" name="visibility" value="public">
            <input type="file" name="media" class="form-control form-control-sm" accept="image/*,video/*" required>
            <button type="submit" class="btn btn-warning btn-sm rounded-pill text-dark fw-bold">Đăng story</button>
        </form>
    </div>
    @endauth

@php
        // Xử lý dữ liệu Story an toàn
        $storyData = [];
        if(isset($stories) && $stories->isNotEmpty()) {
            foreach($stories as $userId => $userStories) {
                $storyOwner = $userStories->first()?->user;
                $authorName = $storyOwner?->display_name ?? $storyOwner?->username ?? ('User #'.$userId);
                $authorAvatar = $storyOwner?->avatar_url ? asset($storyOwner->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($authorName).'&background=random';
                
                foreach($userStories as $s) {
                    $storyData[] = [
                        'user_id' => (string) $userId,
                        'url' => asset($s->media_url),
                        'type' => $s->type,
                        'author_name' => $authorName,
                        'author_avatar' => $authorAvatar
                    ];
                }
            }
        }
    @endphp

    {{-- KHUNG STORY HÌNH CHỮ NHẬT --}}
    @if(isset($stories) && $stories->isNotEmpty())
    <div class="mb-4">
        <h5 class="fw-bold mb-3">Story</h5>
        <div class="d-flex gap-2 overflow-auto pb-2 px-1">
            @foreach($stories as $userId => $userStories)
            @php
                $firstStory = $userStories->first();
                $storyOwner = $firstStory?->user;
                $authorName = $storyOwner?->display_name ?? $storyOwner?->username ?? ('User #'.$userId);
                $authorAvatar = $storyOwner?->avatar_url ? asset($storyOwner->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($authorName).'&background=random';
            @endphp
            
            <div class="position-relative rounded-4 shadow-sm flex-shrink-0"
                 style="width: 115px; height: 170px; cursor: pointer; background: #000; overflow: hidden; border: 1px solid #e8edf5;"
                 onclick="openStoryViewer('{{ $userId }}')">
                 
                @if($firstStory->type == 'image')
                    <img src="{{ asset($firstStory->media_url) }}" class="w-100 h-100" style="object-fit: cover; opacity: 0.85;">
                @else
                    <video class="w-100 h-100" style="object-fit: cover; opacity: 0.85;" muted>
                        <source src="{{ asset($firstStory->media_url) }}">
                    </video>
                @endif
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);"></div>
                <div class="position-absolute top-0 start-0 p-2">
                    <div class="rounded-circle p-1" style="background: #0062ff;">
                        <img src="{{ $authorAvatar }}" class="rounded-circle border border-white" width="34" height="34" style="object-fit: cover;" alt="avatar">
                    </div>
                </div>
                <div class="position-absolute bottom-0 start-0 w-100 p-2 text-white text-truncate text-center" style="font-size: 12px; font-weight: 600;">
                    {{ $authorName }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- MODAL XEM STORY --}}
    <div class="modal fade" id="storyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content bg-dark border-0 position-relative">
                <div class="position-absolute top-0 start-0 w-100 p-3 z-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <img id="storyAuthorAvatar" src="" class="rounded-circle" width="38" height="38" style="object-fit: cover; border: 2px solid white;">
                        <span id="storyAuthorName" class="text-white fw-bold shadow-sm"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <button class="btn btn-link text-white position-absolute top-50 start-0 translate-middle-y z-3" onclick="changeStory(-1)"><i class="fa-solid fa-chevron-left fa-2x"></i></button>
                <button class="btn btn-link text-white position-absolute top-50 end-0 translate-middle-y z-3" onclick="changeStory(1)"><i class="fa-solid fa-chevron-right fa-2x"></i></button>
                <div class="modal-body p-0 text-center bg-black rounded-3 overflow-hidden" style="height: 75vh; display: flex; align-items: center;">
                    <img id="viewImg" class="w-100 d-none" style="max-height: 100%; object-fit: contain;">
                    <video id="viewVid" controls autoplay class="w-100 d-none" style="max-height: 100%;"></video>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Bảng tin</h5>

    <div id="posts-container">
        @forelse($posts as $post)
            @include('components.post-card', ['post' => $post])
        @empty
            <div class="alert alert-light border text-center">Chưa có bài viết nào trên bảng tin.</div>
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

        document.addEventListener('click', async (event) => {

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

    
const allStories = @json($storyData ?? []);
    let currentStoryIdx = 0;
    let storyModalInstance = null;

    function openStoryViewer(userId) {
        const startIdx = allStories.findIndex(s => s.user_id === userId);
        if (startIdx !== -1) {
            currentStoryIdx = startIdx;
            if (!storyModalInstance) {
                storyModalInstance = new bootstrap.Modal(document.getElementById('storyModal'));
            }
            showStoryContent();
            storyModalInstance.show();
        }
    }

    function showStoryContent() {
        const story = allStories[currentStoryIdx];
        const img = document.getElementById('viewImg');
        const vid = document.getElementById('viewVid');
        
        document.getElementById('storyAuthorName').textContent = story.author_name;
        document.getElementById('storyAuthorAvatar').src = story.author_avatar;

        img.classList.add('d-none');
        vid.classList.add('d-none');
        vid.pause();
        vid.src = "";

        if (story.type === 'image') {
            img.src = story.url;
            img.classList.remove('d-none');
        } else {
            vid.src = story.url;
            vid.classList.remove('d-none');
            vid.load();
            vid.play();
        }
    }

    function changeStory(step) {
        let newIdx = currentStoryIdx + step;
        if (newIdx >= 0 && newIdx < allStories.length) {
            currentStoryIdx = newIdx;
            showStoryContent();
        } else {
            storyModalInstance.hide();
        }
    }

    document.addEventListener('keydown', (e) => {
        const modalEl = document.getElementById('storyModal');
        if (modalEl && modalEl.classList.contains('show')) {
            if (e.key === "ArrowLeft") changeStory(-1);
            if (e.key === "ArrowRight") changeStory(1);
        }
    });
</script>

@endsection