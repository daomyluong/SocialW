@props(['post'])

<div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
    <div class="space-y-3 mb-4">
        @foreach($post->comments as $comment)
            <div class="flex justify-between items-start group">
                <div class="flex space-x-2">
                    <span class="font-bold text-sm">{{ $comment->user->username }}:</span>
                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $comment->content }}</p>
                </div>
                
                {{-- Kiểm tra nếu là chủ comment thì cho phép xóa --}}
                @if(auth()->id() === $comment->author_user_id)
                    <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 opacity-0 group-hover:opacity-100 transition">Xóa</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <form action="{{ route('comments.store', $post->id) }}" method="POST" class="flex items-center space-x-2">
        @csrf
        <input type="text" name="content" placeholder="Viết bình luận..." 
            class="flex-1 bg-gray-100 border-none rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white">
        <button type="submit" class="text-blue-500 font-bold text-sm">Đăng</button>
    </form>
</div>