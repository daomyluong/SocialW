@props(['userId', 'isFollowing' => false])

@if(auth()->id() !== $userId) {{-- Không cho phép tự follow chính mình --}}
    <form action="{{ route('users.follow', $userId) }}" method="POST">
        @csrf
        <button type="submit" 
            class="w-full py-2 rounded-xl font-bold border transition
            {{ $isFollowing 
                ? 'bg-white text-black border-gray-300' 
                : 'bg-black text-white border-black dark:bg-white dark:text-black' }}">
            {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
        </button>
    </form>
@endif