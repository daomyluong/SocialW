@foreach($comments as $comment)
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