@extends('User.layouts.app')

@section('title', 'แกลเลอรี')

@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="fas fa-images me-2"></i> แกลเลอรี</h1>
    <div class="row">
        @forelse($images as $image)
            @if($image->status === 'active')
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="{{ $image->image }}" class="card-img-top" alt="{{ $image->title }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $image->title }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($image->description, 80) }}</p>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    ยังไม่มีรูปภาพในแกลเลอรี
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection