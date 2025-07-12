@extends('admin.layouts.admin')

@section('title', 'Gallery Management')


<link href="{{ asset('css/admin/gallery.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gallery Management</h1>
        <a href="{{ route('admin.gallery.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Image
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('admin.gallery.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($galleries as $gallery)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="{{ $gallery->image }}" class="card-img-top" alt="{{ $gallery->title }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $gallery->title }}</h5>
                                <p class="card-text">{{ Str::limit($gallery->description, 100) }}</p>
                                <span class="badge bg-{{ $gallery->getStatusColor() }}">
                                    {{ ucfirst($gallery->status) }}
                                </span>
                                <div class="mt-3">
                                    <a href="{{ route('admin.gallery.edit', $gallery) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.gallery.destroy', $gallery) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center">No gallery images found</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">
                {{ $galleries->links() }}
            </div>
        </div>
    </div>
</div>
@endsection