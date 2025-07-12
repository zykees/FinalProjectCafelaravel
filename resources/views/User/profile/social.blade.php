@extends('User.layouts.app')

@section('title', 'เชื่อมต่อโซเชียล')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            <!-- เมนูด้านข้าง -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="{{ auth()->user()->avatar_url }}" 
                             alt="Profile Picture" 
                             class="rounded-circle img-thumbnail" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <h5 class="mt-3">{{ auth()->user()->name }}</h5>
                    </div>
                    
                    <div class="list-group">
                        <a href="{{ route('user.profile.index') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>ข้อมูลส่วนตัว
                        </a>
                        <a href="{{ route('user.profile.edit') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-edit me-2"></i>แก้ไขข้อมูล
                        </a>
                        <a href="{{ route('user.profile.social') }}" 
                           class="list-group-item list-group-item-action active">
                            <i class="fas fa-share-alt me-2"></i>เชื่อมต่อโซเชียล
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">เชื่อมต่อบัญชีโซเชียล</h4>

                    <!-- Google -->
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <div>
                            <h5 class="mb-1">
                                <i class="fab fa-google text-danger me-2"></i>Google
                            </h5>
                            @if(auth()->user()->google_id)
                                <small class="text-success">เชื่อมต่อแล้ว</small>
                            @else
                                <small class="text-muted">ยังไม่ได้เชื่อมต่อ</small>
                            @endif
                        </div>
                        <div>
                            @if(auth()->user()->google_id)
                                <form action="{{ route('user.social.disconnect', 'google') }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        ยกเลิกการเชื่อมต่อ
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('user.social.connect', 'google') }}" 
                                   class="btn btn-outline-primary">
                                    เชื่อมต่อ
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- LINE -->
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <div>
                            <h5 class="mb-1">
                                <i class="fab fa-line text-success me-2"></i>LINE
                            </h5>
                            @if(auth()->user()->line_id)
                                <small class="text-success">เชื่อมต่อแล้ว</small>
                            @else
                                <small class="text-muted">ยังไม่ได้เชื่อมต่อ</small>
                            @endif
                        </div>
                        <div>
                            @if(auth()->user()->line_id)
                                <form action="{{ route('user.social.disconnect', 'line') }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        ยกเลิกการเชื่อมต่อ
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('user.social.connect', 'line') }}" 
                                   class="btn btn-outline-primary">
                                    เชื่อมต่อ
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection