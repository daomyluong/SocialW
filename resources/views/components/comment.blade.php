<div class="comment-section mt-2">
    <div class="space-y-2 mb-2">
        {{-- Kiểm tra xem bài viết này có đang được yêu cầu "Xem thêm" không --}}
        @php
            $isExpanded = request('expanded_post') == $post->id;
            $displayComments = $isExpanded ? $post->comments : $post->comments->take(5);
        @endphp

        @foreach($displayComments as $comment)
            <div class="d-flex justify-content-between align-items-start mb-2" style="font-size: 0.85rem;">
                <div class="d-flex">
                    <span class="fw-bold me-2">{{ $comment->user->username ?? 'User_'.$comment->author_user_id }}:</span>
                    <span class="text-dark">{{ $comment->content }}</span>
                </div>
                @if(Auth::id() == $comment->author_user_id || Auth::id() == 1)
                    <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="ms-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-link p-0 text-danger" style="font-size: 10px; text-decoration: none;">Xóa</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Nút bấm: Chỉ hiện nếu chưa mở rộng và có hơn 5 bình luận --}}
    @if(!$isExpanded && $post->comment_count > 5)
        <a href="#" 
           class="load-more-btn text-primary d-block mb-2" 
           data-post-id="{{ $post->id }}"
           style="font-size: 0.8rem; text-decoration: none;">
            <i class="fa-solid fa-comments me-1"></i> 
            Xem thêm bình luận khác...
        </a>
    @elseif($isExpanded)
        <a href="{{ url()->current() }}" class="text-secondary d-block mb-2" style="font-size: 0.8rem; text-decoration: none;">
            Thu gọn bình luận
        </a>
    @endif
    
    {{-- Điểm neo để sau khi load trang nó tự cuộn xuống đúng bài viết --}}
    <div id="extra-comments-{{ $post->id }}"></div>
</div>