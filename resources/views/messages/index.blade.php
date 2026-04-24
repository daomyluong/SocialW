@extends('layouts.app')

@section('title', 'Nhắn tin')

@section('content')
<style>
    .messenger-shell {
        height: 100%;
        min-height: 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .messenger-frame {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        grid-template-columns: 340px minmax(0, 1fr);
        border: 1px solid #e5e7eb;
        border-radius: 0;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.06);
    }

    .messenger-frame.list-only {
        grid-template-columns: minmax(0, 1fr);
    }

    .messenger-frame.chat-open {
        grid-template-columns: 320px minmax(0, 1fr);
    }

    .messenger-frame.chat-open.sidebar-collapsed {
        grid-template-columns: 0 minmax(0, 1fr);
    }

    .messenger-frame.chat-open.sidebar-collapsed .conversation-sidebar {
        width: 0;
        min-width: 0;
        overflow: hidden;
        border-right: 0;
        opacity: 0;
        pointer-events: none;
    }

    .conversation-sidebar,
    .chat-panel,
    .empty-panel {
        min-width: 0;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .conversation-sidebar {
        border-right: 1px solid #e5e7eb;
        background: #fff;
    }

    .panel-header {
        flex-shrink: 0;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }

    .sidebar-body,
    .message-board,
    .recent-list {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .sidebar-body {
        padding: 0.75rem;
        background: #fbfcff;
    }

    .sidebar-section-title {
        padding: 0.25rem 0.25rem 0.75rem;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        color: #94a3b8;
    }

    .conversation-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        text-decoration: none;
        color: inherit;
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 0.75rem;
        margin-bottom: 0.55rem;
        background: #fff;
        transition: 0.18s ease;
    }

    .conversation-item:hover,
    .conversation-item.active {
        border-color: #dbeafe;
        background: #eef6ff;
    }

    .conversation-avatar,
    .header-avatar {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        background: #dbeafe;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .header-avatar {
        width: 34px;
        height: 34px;
    }

    .conversation-avatar img,
    .header-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .conversation-name {
        font-size: 0.94rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.25;
    }

    .conversation-meta {
        font-size: 0.8rem;
        color: #6b7280;
        line-height: 1.3;
    }

    .conversation-time {
        font-size: 0.72rem;
        color: #6b7280;
        white-space: nowrap;
    }

    .chat-panel {
        background: linear-gradient(180deg, #f8fafc 0%, #eef6ff 100%);
    }

    .chat-header {
        flex-shrink: 0;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(10px);
    }

    .sidebar-toggle {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar-toggle:hover {
        background: #eef6ff;
        color: #2563eb;
    }

    .chat-title {
        font-size: 1rem;
        font-weight: 800;
        color: #111827;
        line-height: 1.2;
    }

    .chat-subtitle {
        font-size: 0.8rem;
        color: #6b7280;
        line-height: 1.3;
    }

    .message-board {
        padding: 1rem;
        background-image: radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 30%), radial-gradient(circle at bottom right, rgba(40, 167, 69, 0.08), transparent 26%);
    }

    .bubble {
        max-width: 78%;
        border-radius: 18px;
        padding: 0.8rem 0.95rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        word-break: break-word;
    }

    .bubble.me {
        margin-left: auto;
        background: linear-gradient(135deg, var(--hlink-blue), #1d4ed8);
        color: #fff;
        border-bottom-right-radius: 6px;
    }

    .bubble.them {
        background: #fff;
        color: #111827;
        border-bottom-left-radius: 6px;
    }

    .attachment-preview {
        border: 1px dashed rgba(255, 255, 255, 0.45);
        border-radius: 12px;
        padding: 0.4rem;
        margin-top: 0.65rem;
    }

    .bubble.them .attachment-preview {
        border-color: #d1d5db;
        background: #f8fafc;
    }

    .attachment-preview img {
        max-width: 260px;
        border-radius: 10px;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .composer-bar {
        flex-shrink: 0;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: 0.7rem 0.8rem;
    }

    .composer-shell {
        border: 1px solid #d1d5db;
        border-radius: 14px;
        background: #fff;
        padding: 0.45rem;
        display: flex;
        min-width: 0;
        gap: 0.5rem;
    }

    .composer-input {
        border: 0;
        resize: none;
        min-height: 52px;
        box-shadow: none !important;
        font-size: 0.95rem;
        line-height: 1.35;
    }

    .composer-tools {
        width: 132px;
        flex-shrink: 0;
    }

    .tool-button {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #475569;
        border-radius: 8px;
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .tool-button:hover {
        background: #eef6ff;
        color: #2563eb;
    }

    .emoji-menu {
        width: 220px;
        padding: 0.55rem;
    }

    .emoji-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 0.35rem;
    }

    .emoji-item {
        border: 0;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 1.1rem;
        line-height: 1;
        padding: 0.35rem;
    }

    .emoji-item:hover {
        background: #dbeafe;
    }

    .empty-panel {
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: linear-gradient(180deg, #f8fafc 0%, #eef6ff 100%);
        text-align: center;
    }

    .empty-card {
        max-width: 520px;
        width: 100%;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 1.5rem;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.05);
    }

    html,
    body {
        height: 100%;
    }

    .container-fluid,
    .row,
    .col-lg-8 {
        height: 100%;
    }

    .messenger-frame {
        overflow: hidden;
    }

    .message-board {
        overflow-y: auto;
    }

    @media (max-width: 1200px) {
        .messenger-frame {
            grid-template-columns: 300px minmax(0, 1fr);
        }

        .messenger-frame.chat-open.sidebar-collapsed {
            grid-template-columns: 0 minmax(0, 1fr);
        }
    }

    @media (max-width: 992px) {
        .messenger-shell {
            padding: 0;
        }

        .messenger-frame,
        .messenger-frame.list-only {
            grid-template-columns: minmax(0, 1fr);
            border-radius: 0;
        }

        .messenger-frame.chat-open,
        .messenger-frame.chat-open.sidebar-collapsed {
            grid-template-columns: minmax(0, 1fr);
        }

        .conversation-sidebar {
            border-right: 0;
            border-bottom: 1px solid #e5e7eb;
            max-height: 42vh;
        }

        .messenger-frame.chat-open.sidebar-collapsed .conversation-sidebar {
            display: none;
        }

        .chat-panel {
            height: 100%;
        }
    }
</style>

<div class="container-fluid h-100">
    <div class="row h-100 g-0">

        <div class="col-12 h-100">
            <div class="messenger-shell">
                <div class="messenger-frame {{ $activeConversation ? 'chat-open sidebar-collapsed' : 'list-only' }}" id="messengerFrame">
                    <aside class="conversation-sidebar">
                        <div class="panel-header">
                            <div class="fw-bold">Tin nhắn</div>
                            <small class="text-muted">Nhắn tin riêng: người nhắn tin gần đây và bắt đầu cuộc trò chuyện mới</small>
                        </div>

                        <div class="sidebar-body">
                            <div class="sidebar-section-title">Người nhắn tin gần đây</div>

                            @forelse($recentPrivateConversations as $recent)
                            @php
                            $conversation = $recent['conversation'];
                            $other = $recent['other_user'];
                            $lastMessage = $recent['last_message'];
                            $isActive = $activeConversation && $activeConversation->id === $conversation->id;
                            @endphp
                            <a href="{{ route('messages.show', $conversation) }}" class="conversation-item {{ $isActive ? 'active' : '' }}">
                                <div class="conversation-avatar">
                                    @if($other?->avatar_url)
                                    <img src="{{ $other->avatar_url }}" alt="avatar">
                                    @else
                                    <i class="fa-solid fa-user"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="conversation-name text-truncate">{{ $other?->display_name ?? $other?->name ?? $other?->username ?? 'Người dùng' }}</div>
                                        <div class="conversation-time">{{ $recent['last_time_human'] ?? '' }}</div>
                                    </div>
                                    <div class="conversation-meta text-truncate">{{ $lastMessage?->body ?? 'Chưa có tin nhắn' }}</div>
                                </div>
                            </a>
                            @empty
                            <div class="text-muted small border rounded-3 bg-white p-3">Chưa có cuộc trò chuyện riêng.</div>
                            @endforelse

                            @if($followingUsers->isNotEmpty())
                            <div class="sidebar-section-title mt-3 mb-0">Bắt đầu cuộc trò chuyện mới</div>
                            @foreach($followingUsers as $user)
                            <a href="{{ route('messages.private', $user) }}" class="conversation-item">
                                <div class="conversation-avatar">
                                    @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="avatar">
                                    @else
                                    <i class="fa-solid fa-user"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="conversation-name text-truncate">{{ $user->display_name ?? $user->name ?? 'Người dùng' }}</div>
                                    <div class="conversation-meta text-truncate">@{{ $user->username }}</div>
                                </div>
                            </a>
                            @endforeach
                            @endif
                        </div>
                    </aside>

                    @if($activeConversation)
                    <section class="chat-panel">
                        <div class="chat-header">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <button class="sidebar-toggle" type="button" id="sidebarToggleBtn" aria-label="Hiện danh sách tin nhắn" title="Hiện danh sách tin nhắn">
                                    <i class="fa-solid fa-bars"></i>
                                </button>
                                <div class="header-avatar">
                                    @if(!empty($activeConversationAvatar))
                                    <img src="{{ $activeConversationAvatar }}" alt="avatar">
                                    @else
                                    <i class="fa-solid fa-user"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="chat-title text-truncate">{{ $activeConversationTitle }}</div>
                                    <div class="chat-subtitle text-truncate">{{ $activeConversationSubtitle }}</div>
                                </div>
                            </div>
                        </div>

                        <div id="messageBoard" class="message-board">
                            <div id="messageList" class="d-flex flex-column gap-3">
                                @forelse($messages as $message)
                                @php
                                $isMine = $message->sender_user_id === $viewer->id;
                                $attachmentUrl = $message->attachment_path ? Storage::disk('public')->url($message->attachment_path) : null;
                                @endphp
                                <div class="bubble {{ $isMine ? 'me' : 'them' }}">
                                    <div class="small mb-1 text-end {{ $isMine ? 'text-white-50' : 'text-muted' }}">
                                        {{ optional($message->created_at)->diffForHumans() }}
                                    </div>

                                    @if(!empty($message->body))
                                    <div class="message-text">{!! nl2br(e($message->body)) !!}</div>
                                    @endif

                                    @if($attachmentUrl)
                                    <div class="attachment-preview">
                                        @if($message->attachment_type === 'image')
                                        <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener">
                                            <img src="{{ $attachmentUrl }}" alt="Ảnh đính kèm">
                                        </a>
                                        @else
                                        <a class="text-decoration-none {{ $isMine ? 'text-white' : 'text-primary' }}" href="{{ $attachmentUrl }}" target="_blank" rel="noopener">
                                            <i class="fa-solid fa-file-arrow-down me-2"></i>{{ $message->attachment_name ?? 'Tải tệp đính kèm' }}
                                        </a>
                                        @if($message->attachment_size)
                                        <div class="small mt-1 {{ $isMine ? 'text-white-50' : 'text-muted' }}">{{ number_format($message->attachment_size / 1024, 1) }} KB</div>
                                        @endif
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="alert alert-light border text-muted mb-0">Chưa có tin nhắn nào. Hãy gửi tin nhắn đầu tiên để bắt đầu cuộc trò chuyện.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="composer-bar">
                            @if($errors->any())
                            <div class="alert alert-danger py-2 px-3 small mb-3">{{ $errors->first() }}</div>
                            @endif

                            <form action="{{ route('messages.store', $activeConversation) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-2">
                                @csrf
                                <div class="composer-shell">
                                    <div class="flex-grow-1">
                                        <textarea
                                            id="messageBody"
                                            name="body"
                                            rows="1"
                                            class="form-control composer-input"
                                            placeholder="Nhập tin nhắn..."
                                            maxlength="2000">{{ old('body') }}</textarea>
                                    </div>

                                    <div class="composer-tools d-flex flex-column justify-content-between gap-2">
                                        <div class="d-flex justify-content-end gap-2">
                                            <div class="dropup">
                                                <button class="tool-button" type="button" id="emojiDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Chèn cảm xúc">
                                                    <i class="fa-regular fa-face-smile"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end emoji-menu" aria-labelledby="emojiDropdownBtn">
                                                    <div class="emoji-grid">
                                                        @foreach(['😀','😄','😁','😂','😊','😍','😘','🤝','👍','👏','🔥','💯','🎉','❤️','😎','🤔','😭','😅'] as $emoji)
                                                        <button class="emoji-item" type="button" data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <button class="tool-button" type="button" id="imagePickerBtn" title="Gửi ảnh"><i class="fa-regular fa-image"></i></button>
                                            <button class="tool-button" type="button" id="filePickerBtn" title="Gửi tài liệu"><i class="fa-solid fa-paperclip"></i></button>
                                        </div>

                                        <button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-paper-plane me-2"></i>Gửi</button>
                                    </div>
                                </div>

                                <input type="file" id="imageInput" name="image" accept="image/*" class="d-none">
                                <input type="file" id="fileInput" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar" class="d-none">

                                <div id="attachmentHint" class="small text-muted px-1">Chưa chọn tệp đính kèm.</div>
                            </form>
                        </div>
                    </section>
                    @else
                    <section class="empty-panel">
                        <div class="empty-card">
                            <div class="fw-bold fs-5 mb-2">Chọn một người để nhắn tin</div>
                            <div class="text-muted mb-3">Giao diện này giữ các chức năng cơ bản: xem hội thoại gần đây, mở hội thoại, gửi tin nhắn, gửi ảnh hoặc file, và gửi nhanh bằng phím Enter.</div>
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                <span class="suggestion-chip"><i class="fa-regular fa-comments"></i> Danh sách gần đây</span>
                                <span class="suggestion-chip"><i class="fa-regular fa-image"></i> Ảnh</span>
                                <span class="suggestion-chip"><i class="fa-solid fa-paperclip"></i> File</span>
                            </div>
                        </div>
                    </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (() => {
        const historyUrl = <?php echo json_encode($activeConversation ? route('messages.history', $activeConversation) : null); ?>;
        const viewerId = <?php echo json_encode($viewer->id); ?>;
        const messengerFrame = document.getElementById('messengerFrame');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const messageList = document.getElementById('messageList');
        const messageBoard = document.getElementById('messageBoard');
        const messageBody = document.getElementById('messageBody');
        const imagePickerBtn = document.getElementById('imagePickerBtn');
        const filePickerBtn = document.getElementById('filePickerBtn');
        const imageInput = document.getElementById('imageInput');
        const fileInput = document.getElementById('fileInput');
        const attachmentHint = document.getElementById('attachmentHint');

        const escapeHtml = (value) => {
            const node = document.createElement('div');
            node.textContent = value ?? '';
            return node.innerHTML;
        };

        const formatAttachmentSize = (size) => {
            if (!size || Number(size) <= 0) {
                return '';
            }

            return `${(Number(size) / 1024).toFixed(1)} KB`;
        };

        const renderAvatar = (sender) => {
            if (sender.avatar_url) {
                return `<img src="${escapeHtml(sender.avatar_url)}" alt="avatar">`;
            }

            const name = encodeURIComponent(sender.display_name || sender.username || 'User');
            return `<img src="https://ui-avatars.com/api/?name=${name}&background=2563eb&color=fff" alt="avatar">`;
        };

        const renderAttachment = (message, isMine) => {
            if (!message.attachment_url) {
                return '';
            }

            if (message.attachment_type === 'image') {
                return `
                    <div class="attachment-preview">
                        <a href="${escapeHtml(message.attachment_url)}" target="_blank" rel="noopener">
                            <img src="${escapeHtml(message.attachment_url)}" alt="Ảnh đính kèm">
                        </a>
                    </div>
                `;
            }

            const fileName = escapeHtml(message.attachment_name || 'Tệp đính kèm');
            const sizeLabel = formatAttachmentSize(message.attachment_size);
            const colorClass = isMine ? 'text-white' : 'text-primary';
            const mutedClass = isMine ? 'text-white-50' : 'text-muted';

            return `
                <div class="attachment-preview">
                    <a class="text-decoration-none ${colorClass}" href="${escapeHtml(message.attachment_url)}" target="_blank" rel="noopener">
                        <i class="fa-solid fa-file-arrow-down me-2"></i>${fileName}
                    </a>
                    ${sizeLabel ? `<div class="small mt-1 ${mutedClass}">${sizeLabel}</div>` : ''}
                </div>
            `;
        };

        const renderMessage = (message) => {
            const isMine = Number(message.sender?.id) === Number(viewerId);
            const senderName = escapeHtml(message.sender?.display_name || message.sender?.username || 'Người dùng');
            const timeLabel = escapeHtml(message.created_at_human || 'Vừa xong');
            const body = escapeHtml(message.body || '').replace(/\n/g, '<br>');
            const bodyBlock = body ? `<div class="message-text">${body}</div>` : '';

            return `
                <div class="bubble ${isMine ? 'me' : 'them'}">
                    <div class="small mb-2 text-end ${isMine ? 'text-white-50' : 'text-muted'}">${timeLabel}</div>
                    ${bodyBlock}
                    ${renderAttachment(message, isMine)}
                </div>
            `;
        };

        const renderEmpty = () => {
            if (!messageList) {
                return;
            }

            messageList.innerHTML = `
                <div class="alert alert-light border text-muted mb-0">
                    Chưa có tin nhắn nào. Hãy gửi tin nhắn đầu tiên để bắt đầu cuộc trò chuyện.
                </div>
            `;
        };

        const scrollToBottom = () => {
            if (messageBoard) {
                messageBoard.scrollTop = messageBoard.scrollHeight;
            }
        };

        const refreshMessages = async () => {
            if (!historyUrl || !messageList) {
                return;
            }

            try {
                const response = await fetch(historyUrl, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const messages = payload.data || [];

                if (!messages.length) {
                    renderEmpty();
                } else {
                    messageList.innerHTML = messages.map(renderMessage).join('');
                }

                scrollToBottom();
            } catch (error) {
                return;
            }
        };

        const updateAttachmentHint = () => {
            if (!attachmentHint) {
                return;
            }

            if (imageInput?.files?.[0]) {
                attachmentHint.textContent = `Ảnh đã chọn: ${imageInput.files[0].name}`;
                return;
            }

            if (fileInput?.files?.[0]) {
                attachmentHint.textContent = `Tài liệu đã chọn: ${fileInput.files[0].name}`;
                return;
            }

            attachmentHint.textContent = 'Chưa chọn tệp đính kèm.';
        };

        if (imagePickerBtn && imageInput) {
            imagePickerBtn.addEventListener('click', () => imageInput.click());
            imageInput.addEventListener('change', () => {
                if (imageInput.files?.length) {
                    fileInput.value = '';
                }
                updateAttachmentHint();
            });
        }

        if (filePickerBtn && fileInput) {
            filePickerBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => {
                if (fileInput.files?.length) {
                    imageInput.value = '';
                }
                updateAttachmentHint();
            });
        }

        document.querySelectorAll('.emoji-item').forEach((emojiButton) => {
            emojiButton.addEventListener('click', () => {
                if (!messageBody) {
                    return;
                }

                const emoji = emojiButton.getAttribute('data-emoji') || '';
                messageBody.value = `${messageBody.value}${emoji}`;
                messageBody.focus();
            });
        });

        if (messageBody) {
            messageBody.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || event.shiftKey) {
                    return;
                }

                event.preventDefault();
                messageBody.form?.requestSubmit();
            });
        }

        if (sidebarToggleBtn && messengerFrame) {
            sidebarToggleBtn.addEventListener('click', () => {
                messengerFrame.classList.toggle('sidebar-collapsed');
            });
        }

        updateAttachmentHint();
        scrollToBottom();
        if (historyUrl) {
            setInterval(refreshMessages, 5000);
        }
    })();
</script>
@endsection