@extends('User.layouts.app')
@section('title', $news->title)
@section('content')
<div class="container py-4">
    <h1 class="mb-3">{{ $news->title }}</h1>
    <div class="text-muted mb-2">
        {{ $news->published_at ? $news->published_at->format('d/m/Y H:i') : '' }}
    </div>
    @if($news->image)
        <div class="mb-3">
            <img src="{{ $news->getImageUrl() }}" alt="news image" style="max-width: 350px;">
        </div>
    @endif
    <div class="mb-4">
        {!! $news->content !!}
    </div>
    <a href="{{ route('user.news.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> กลับหน้าข่าวสารทั้งหมด
    </a>
</div>
@endsection