@extends('admin.layouts.admin')

@section('title', 'News Management')


<link href="{{ asset('css/admin/news.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">News Management</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('admin.news.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Published At</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $item)
                            <tr>
                                <td>{{ $item->title }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->getStatusColor() }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($item->published_at)->format('Y-m-d H:i') ?? 'Not published' }}</td>
                                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.news.edit', $item) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.news.destroy', $item) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this news?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No news found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $news->links() }}
        </div>
    </div>
</div>
@endsection