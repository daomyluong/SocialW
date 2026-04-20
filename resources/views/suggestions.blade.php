@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width: 600px;">
    <h5 class="fw-bold mb-4">Gợi ý</h5>
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-4">
            @forelse($allSuggestions as $user)
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=random" 
                             class="rounded-circle me-3" width="44" height="44">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">{{ $user->username }}</span>
                            <span class="text-muted small">{{ $user->display_name }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-follow-ig border-0 bg-transparent text-primary fw-bold p-0" 
                            data-user-id="{{ $user->id }}">
                        Theo dõi
                    </button>
                </div>
            @empty
                <p class="text-center text-muted">Không còn gợi ý nào.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection