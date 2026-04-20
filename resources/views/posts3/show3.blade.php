@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px;">
    <a href="{{ route('home') }}" class="btn btn-link text-decoration-none text-dark mb-3">
        <i class="fa-solid fa-arrow-left"></i> Quay lại
    </a>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <img src="https://ui-avatars.com/api/?name=User{{ $post->author_user_id }}&background=random" class="rounded-circle me-2" width="45">
                <div>
                    <span class="fw-bold d-block">User #{{ $post->author_user_id }}</span>
                    <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                </div>
            </div>

            <p style="font-size: 1.1rem;">{{ $post->content }}</p>

            @if($post->media && $post->media->count() > 0)
                <div class="rounded-4 overflow-hidden border">
                    @foreach($post->media as $m)
                        <img src="{{ asset($m->url) }}" class="img-fluid w-100 mb-1">
                    @endforeach
                </div>
            @endif
            
            <div class="mt-3 pt-2 border-top">
                <span class="text-secondary"><i class="fa-solid fa-heart text-danger"></i> {{ DB::table('post_likes')->where('post_id', $post->id)->count() }} lượt thích</span>
            </div>
        </div>
    </div>
</div>
@endsection