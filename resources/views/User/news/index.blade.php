@extends('User.layouts.app')
@section('title', 'ข่าวสารทั้งหมด')
@section('content')
<div class="container py-4">
    <h1 class="mb-4">ข่าวสารทั้งหมด</h1>
    @forelse($news as $item)
        <div class="mb-4">
            <h5>
                <a href="{{ route('user.news.show', $item) }}">{{ $item->title }}</a>
            </h5>
            <div class="text-muted small mb-2">
                {{ $item->published_at ? $item->published_at->format('d/m/Y H:i') : '' }}
            </div>
            <div>
                {{ Str::limit(strip_tags($item->content), 200) }}
            </div>
        </div>
    @empty
        <div class="text-muted">ยังไม่มีข่าวสาร</div>
    @endforelse
    {{ $news->links() }}
</div>
@endsection