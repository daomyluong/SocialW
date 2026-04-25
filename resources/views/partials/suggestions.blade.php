<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <div class="fw-bold mb-3">Gợi ý theo dõi</div>
        @if(isset($suggestedUsers))
            @foreach($suggestedUsers as $user)
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=random" 
                         class="rounded-circle me-2" width="34" height="34">
                    <div class="fw-semibold small text-truncate">{{ $user->display_name ?? $user->username }}</div>
                </div>
                @php
                    $isFollowing = in_array((int) $user->id, $followingIds ?? [], true);
                @endphp
                <button type="button"
                        class="btn btn-sm btn-link p-0 fw-bold follow-btn {{ $isFollowing ? 'text-secondary' : 'text-primary' }}"
                        data-user-id="{{ $user->id }}">
                    {{ $isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                </button>
            </div>
            @endforeach
        @endif
    </div>
</div>