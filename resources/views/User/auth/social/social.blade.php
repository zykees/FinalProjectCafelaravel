@extends('User.layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">บัญชีที่เชื่อมต่อ</div>
                <div class="card-body">
  @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

                 <!-- LINE Account -->
<div class="d-flex justify-content-between align-items-center p-3 border-bottom">
    <div>
        <i class="fab fa-line fa-2x text-success me-2"></i>
        <span>LINE</span>
        @if($user->line_id)
            <small class="text-success ms-2">
                <i class="fas fa-check-circle"></i> เชื่อมต่อแล้ว
            </small>
        @endif
    </div>
    <div>
        @if($user->line_id)
            <form action="{{ route('user.social.disconnect', 'line') }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    ยกเลิกการเชื่อมต่อ
                </button>
            </form>
        @else
            <a href="{{ route('user.social.connect.line') }}" class="btn btn-success">
                <i class="fas fa-plug me-1"></i> เชื่อมต่อ
            </a>
        @endif
    </div>
</div>
    </div>
</div>
@endsection