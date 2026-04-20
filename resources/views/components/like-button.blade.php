@props(['postId', 'isLiked' => false, 'likeCount' => 0])

<div class="flex items-center space-x-2">
    <form action="{{ route('posts.like', $postId) }}" method="POST">
        @csrf
        <button type="submit" 
            class="px-4 py-1 rounded-full border transition font-semibold
            {{ $isLiked 
                ? 'bg-blue-600 text-white border-blue-600' 
                : 'bg-transparent text-black border-gray-300 hover:bg-gray-100 dark:text-white dark:border-gray-700' }}">
            {{ $isLiked ? 'Unlike' : 'Like' }}
        </button>
    </form>
    <span class="text-gray-500 text-sm font-medium">{{ $likeCount }} lượt thích</span>
</div>