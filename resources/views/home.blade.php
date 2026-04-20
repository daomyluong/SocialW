@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    <div class="card mb-4 border-0 border-bottom">
        <div class="card-body d-flex">
            <div class="avatar bg-light rounded-circle me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                <i class="fa-solid fa-user fa-xl text-secondary" style="line-height: 50px; margin-left: 15px;"></i>
            </div>
            <div class="w-100">
                <input type="text" class="form-control border-0 bg-light" style="border-radius: 20px;" placeholder="Bạn đang nghĩ gì, {{ auth()->user()?->display_name ?? 'Đào' }}?" disabled>
                <div class="mt-2 d-flex gap-3 text-primary">
                    <small><i class="fa-regular fa-image me-1"></i> Ảnh/Video</small>
                    <small><i class="fa-solid fa-at me-1"></i> Nhắc tên</small>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Bảng tin</h5>
    <div id="feedStatus" class="small text-muted mb-2">Đang tải bảng tin...</div>
    <div id="feedList"></div>
</div>
@endsection

@section('suggestions')
    <p class="px-2 text-muted small">Danh sách gợi ý sẽ do Quỳnh (TV4) phụ trách.</p>
@endsection

@section('scripts')
<script>
    (() => {
        const feedUrl = "{{ route('feed.latest') }}";
        const feedList = document.getElementById('feedList');
        const feedStatus = document.getElementById('feedStatus');
        let latestCreatedAt = null;

        const escapeHtml = (unsafe) => {
            const node = document.createElement('div');
            node.textContent = unsafe ?? '';
            return node.innerHTML;
        };

        const renderAvatar = (post) => {
            if (post.author.avatar_url) {
                return `<img src="${escapeHtml(post.author.avatar_url)}" class="rounded-circle me-2" width="40" height="40" alt="avatar">`;
            }

            const name = encodeURIComponent(post.author.display_name || 'User');
            return `<img src="https://ui-avatars.com/api/?name=${name}&background=0D8ABC&color=fff" class="rounded-circle me-2" width="40" height="40" alt="avatar">`;
        };

        const renderPost = (post) => {
            const content = renderContent(post.content || 'Bài viết chưa có nội dung.');
            const displayName = escapeHtml(post.author.display_name || 'Người dùng');
            const username = escapeHtml(post.author.username || 'guest');
            const timeLabel = escapeHtml(post.created_at_human || 'Vừa xong');
            const mediaBlock = post.media_url
                ? `<div class="rounded-3 overflow-hidden border mb-2"><img src="${escapeHtml(post.media_url)}" class="img-fluid w-100" alt="media"></div>`
                : '';

            return `
                <div class="post-item mb-4 border-bottom pb-3" data-post-id="${post.id}">
                    <div class="d-flex align-items-center mb-2">
                        ${renderAvatar(post)}
                        <div>
                            <span class="fw-bold">${username}</span>
                            <small class="text-muted d-block">${displayName} · ${timeLabel}</small>
                        </div>
                    </div>
                    <div class="post-content ps-5">
                        <p class="mb-2">${content}</p>
                        ${mediaBlock}
                        <div class="post-actions d-flex gap-4 text-secondary">
                            <span><i class="fa-regular fa-heart me-1"></i> ${post.like_count}</span>
                            <span><i class="fa-regular fa-comment me-1"></i> ${post.comment_count}</span>
                        </div>
                    </div>
                </div>
            `;
        };

        const renderContent = (value) => {
            const escaped = escapeHtml(value || '');
            return escaped.replace(/(^|\s)@([a-zA-Z0-9_\.]+)/g, '$1<span class="text-primary fw-semibold">@$2</span>');
        };

        const renderEmpty = () => {
            feedList.innerHTML = `
                <div class="alert alert-light border text-muted">
                    Chưa có bài viết nào trên bảng tin. Khi bạn bè hoặc tài khoản theo dõi đăng bài mới, dữ liệu sẽ tự cập nhật.
                </div>
            `;
        };

        const updateFeed = async (incremental = false) => {
            try {
                const url = new URL(feedUrl, window.location.origin);
                if (incremental && latestCreatedAt) {
                    url.searchParams.set('since', latestCreatedAt);
                }

                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Không lấy được dữ liệu bảng tin');
                }

                const payload = await response.json();
                const posts = payload.data || [];

                if (!incremental) {
                    if (!posts.length) {
                        renderEmpty();
                    } else {
                        feedList.innerHTML = posts.map(renderPost).join('');
                    }
                } else if (posts.length) {
                    const html = posts.map(renderPost).join('');
                    feedList.insertAdjacentHTML('afterbegin', html);
                }

                if (posts.length && posts[0].created_at) {
                    latestCreatedAt = posts[0].created_at;
                }

                const countLabel = posts.length ? `Đã cập nhật ${posts.length} bài viết mới.` : 'Không có bài viết mới.';
                feedStatus.textContent = `${countLabel} Tự làm mới mỗi 10 giây.`;
            } catch (error) {
                feedStatus.textContent = 'Không thể tải bảng tin. Vui lòng thử lại.';
            }
        };

        updateFeed(false);
        setInterval(() => updateFeed(true), 10000);
    })();
</script>
@endsection