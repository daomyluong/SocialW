@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    {{-- ======================================================= --}}
    {{-- HIỂN THỊ THÔNG BÁO THÀNH CÔNG                           --}}
    {{-- ======================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 15px;" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{-- PHẦN STORY (THANH TRƯỢT NGANG)                          --}}
    {{-- ======================================================= --}}
    <div class="d-flex overflow-auto py-3 mb-4" style="gap: 15px; scrollbar-width: none; -ms-overflow-style: none;">
        <style>.d-flex::-webkit-scrollbar { display: none; }</style>
        
        <div class="text-center" style="min-width: 75px;">
            <form action="{{ route('stories3.store') }}" method="POST" enctype="multipart/form-data" id="storyForm3">
                @csrf
                <label for="storyInput3" style="cursor: pointer;">
                    <div class="rounded-circle border border-2 border-primary d-flex align-items-center justify-content-center mb-1" style="width: 65px; height: 65px; border-style: dashed !important;">
                        <i class="fa-solid fa-plus text-primary fa-lg"></i>
                    </div>
                </label>
                <input type="file" name="media" id="storyInput3" hidden onchange="document.getElementById('storyForm3').submit()">
            </form>
            <small class="fw-bold" style="font-size: 0.75rem;">Tin của bạn</small>
        </div>

        @if(isset($stories))
            @foreach($stories as $userId => $userStories)
            <div class="text-center" style="min-width: 75px; cursor: pointer;" onclick="openStoryModal3({{ json_encode($userStories) }})">
                <div class="rounded-circle p-1 mb-1" style="background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); width: 65px; height: 65px;">
                    <img src="https://ui-avatars.com/api/?name=User{{ $userId }}&background=random" class="rounded-circle border border-2 border-white w-100 h-100" style="object-fit: cover;">
                </div>
                <small class="d-block text-truncate fw-bold" style="font-size: 0.75rem;">User #{{ $userId }}</small>
            </div>
            @endforeach
        @endif
    </div>

    {{-- ======================================================= --}}
    {{-- PHẦN 1: KHUNG ĐĂNG BÀI NHANH                            --}}
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
                        <input type="text" name="content" class="form-control border-0 bg-light" style="border-radius: 20px;" placeholder="Bạn đang nghĩ gì, {{ Auth::user()->display_name ?? 'Thanh' }}?" required>
                        <input type="hidden" name="visibility" value="public">
                        <div id="homeImagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3 text-primary">
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
    {{-- PHẦN 2: DANH SÁCH BÀI VIẾT                              --}}
    {{-- ======================================================= --}}
    @forelse($posts as $post)
        {{-- THÊM ID ĐỂ TRANG BOOKMARKS CÓ THỂ TRỎ ĐẾN ĐÚNG BÀI VIẾT NÀY --}}
        <div class="post-item mb-4 border-bottom pb-3" id="post-{{ $post->id }}">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle me-2" width="40" height="40">
                    <div>
                        <span class="fw-bold">User #{{ $post->author_user_id }}</span>
                        <small class="text-muted d-block">{{ $post->created_at->diffForHumans() }}</small>
                    </div>
                </div>
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
                <div class="post-actions d-flex justify-content-between text-secondary">
                    <div class="d-flex gap-4">
                        <span><i class="fa-regular fa-heart me-1"></i> Thích</span>
                        <span><i class="fa-regular fa-comment me-1"></i> Bình luận</span>
                        <span><i class="fa-solid fa-share me-1"></i></span>
                    </div>
                    
                    {{-- NÚT BOOKMARK: ĐÃ FIX ĐỂ TỰ ĐỘNG CẬP NHẬT KHI BỎ LƯU --}}
                    <div onclick="toggleBookmark3({{ $post->id }}, this)" style="cursor: pointer;" title="Lưu bài viết">
                        @php
                            $isBookmarked = \App\Models\Bookmark3::where('user_id', Auth::id() ?? 1)
                                            ->where('post_id', $post->id)
                                            ->where('is_deleted', 0) 
                                            ->exists();
                        @endphp
                        <i class="{{ $isBookmarked ? 'fa-solid text-dark' : 'fa-regular' }} fa-bookmark" style="font-size: 1.1rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-muted py-5">Chưa có bài viết nào được đăng.</p>
    @endforelse
</div>

{{-- ======================================================= --}}
{{-- MODAL XEM STORY                                         --}}
{{-- ======================================================= --}}
<div class="modal fade" id="storyModal3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-body p-0 position-relative text-center">
                <div class="progress position-absolute top-0 start-0 w-100" style="height: 4px; z-index: 1010; border-radius: 0;">
                    <div id="storyProgressBar3" class="progress-bar bg-white" style="width: 0%"></div>
                </div>

                <div class="position-absolute top-0 start-0 w-100 d-flex align-items-center p-3 mt-2" style="z-index: 1008;">
                    <img id="storyUserAvatar3" src="" class="rounded-circle border border-1 border-white me-2" width="40" height="40" style="object-fit: cover;">
                    <div class="text-start text-white">
                        <div id="storyUserName3" class="fw-bold shadow-sm" style="font-size: 0.9rem; text-shadow: 1px 1px 2px black;"></div>
                        <small id="storyTime3" class="text-white-50 shadow-sm" style="font-size: 0.75rem; text-shadow: 1px 1px 2px black;"></small>
                    </div>
                </div>

                <div onclick="prevStory3(event)" class="position-absolute start-0 top-0 h-100" style="width: 30%; z-index: 1005; cursor: pointer;"></div>
                <div onclick="nextStory3(event)" class="position-absolute end-0 top-0 h-100" style="width: 70%; z-index: 1005; cursor: pointer;"></div>

                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 1009;"></button>
                
                <div id="storyMediaContainer3" class="d-flex align-items-center justify-content-center bg-black" style="min-height: 500px; height: 100vh;"></div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CHỌN THƯ MỤC BOOKMARK --}}
<div class="modal fade" id="bookmarkFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Lưu vào thư mục...</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="folderList" class="list-group list-group-flush mb-3" style="max-height: 200px; overflow-y: auto;"></div>
                <div class="input-group input-group-sm">
                    <input type="text" id="newFolderName" class="form-control" placeholder="Tên thư mục mới...">
                    <button class="btn btn-primary" onclick="confirmBookmarkWithNewFolder()">Lưu</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let storyInterval3;
let currentStoryIdx = 0;
let currentUserStories = [];

// --- STORY LOGIC ---
function openStoryModal3(userStories) {
    currentUserStories = userStories;
    currentStoryIdx = 0;
    renderSingleStory();
    const modal = new bootstrap.Modal(document.getElementById('storyModal3'));
    modal.show();
}

function renderSingleStory() {
    const story = currentUserStories[currentStoryIdx];
    const container = document.getElementById('storyMediaContainer3');
    const progressBar = document.getElementById('storyProgressBar3');
    
    document.getElementById('storyUserName3').innerText = story.user ? (story.user.username || story.user.name) : 'User #' + story.user_id;
    document.getElementById('storyUserAvatar3').src = `https://ui-avatars.com/api/?name=User${story.user_id}&background=random`;
    
    const postDate = new Date(story.created_at);
    document.getElementById('storyTime3').innerText = postDate.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit' });

    container.innerHTML = ''; 
    progressBar.style.width = '0%';
    clearInterval(storyInterval3);

    if (story.type === 'video') {
        container.innerHTML = `<video src="${story.media_url}" autoplay muted playsinline style="max-width: 100%; max-height: 100vh;"></video>`;
    } else {
        container.innerHTML = `<img src="${story.media_url}" style="max-width: 100%; max-height: 100vh; object-fit: contain;">`;
    }

    let percent = 0;
    storyInterval3 = setInterval(() => {
        percent += 1;
        progressBar.style.width = percent + '%';
        if (percent >= 100) {
            clearInterval(storyInterval3);
            nextStory3();
        }
    }, 100); 
}

function nextStory3(event) {
    if(event) event.stopPropagation();
    clearInterval(storyInterval3);
    currentStoryIdx++;
    if (currentStoryIdx < currentUserStories.length) renderSingleStory();
    else bootstrap.Modal.getInstance(document.getElementById('storyModal3')).hide();
}

function prevStory3(event) {
    if(event) event.stopPropagation();
    clearInterval(storyInterval3);
    currentStoryIdx--;
    if (currentStoryIdx < 0) currentStoryIdx = 0; 
    renderSingleStory();
}

// --- BOOKMARK LOGIC ---
let pendingPostId = null;
let pendingElement = null;

function toggleBookmark3(postId, element) {
    const icon = element.querySelector('i');
    
    // Nếu icon đang đậm -> Gửi yêu cầu xóa mềm (toggle)
    if (icon.classList.contains('fa-solid')) {
        saveBookmarkAction(postId, 'Tất cả', element);
        return;
    }

    pendingPostId = postId;
    pendingElement = element;
    
    fetch('/bookmarks/folders')
        .then(res => res.json())
        .then(folders => {
            let html = `<button onclick="confirmBookmark('Tất cả')" class="list-group-item list-group-item-action border-0 px-2 py-2 mb-1 rounded bg-light">📁 Tất cả</button>`;
            folders.forEach(f => {
                if(f !== 'Tất cả') html += `<button onclick="confirmBookmark('${f}')" class="list-group-item list-group-item-action border-0 px-2 py-2 mb-1 rounded bg-light">📁 ${f}</button>`;
            });
            document.getElementById('folderList').innerHTML = html;
            new bootstrap.Modal(document.getElementById('bookmarkFolderModal')).show();
        });
}

function confirmBookmark(folderName) {
    saveBookmarkAction(pendingPostId, folderName, pendingElement);
    bootstrap.Modal.getInstance(document.getElementById('bookmarkFolderModal')).hide();
}

function confirmBookmarkWithNewFolder() {
    const input = document.getElementById('newFolderName');
    const folderName = input.value.trim();
    if (folderName) {
        confirmBookmark(folderName);
        input.value = '';
    }
}

function saveBookmarkAction(postId, folderName, element) {
    const icon = element.querySelector('i');
    fetch(`/bookmarks/toggle/${postId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ folder_name: folderName })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'added') {
            icon.classList.replace('fa-regular', 'fa-solid');
            icon.classList.add('text-dark');
        } else {
            icon.classList.replace('fa-solid', 'fa-regular');
            icon.classList.remove('text-dark');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('storyModal3').addEventListener('hidden.bs.modal', () => {
        clearInterval(storyInterval3);
        document.getElementById('storyMediaContainer3').innerHTML = '';
    });

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
        <h6 class="fw-bold text-secondary mb-3">Gợi ý cho bạn</h6>
        @if(isset($suggestedUsers) && $suggestedUsers->count() > 0)
            <div class="d-flex flex-column gap-3">
                @foreach($suggestedUsers as $user)
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=random&color=fff" class="rounded-circle me-2" width="40" height="40">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $user->username }}</span>
                                <span class="text-muted" style="font-size: 0.8rem;">{{ $user->display_name }}</span>
                            </div>
                        </div>
                        <div>
                            <form action="{{ route('users.follow', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-dark btn-sm rounded-pill fw-bold px-3">Theo dõi</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted small">Hiện chưa có gợi ý mới nào.</p>
        @endif
    </div>
@endsection