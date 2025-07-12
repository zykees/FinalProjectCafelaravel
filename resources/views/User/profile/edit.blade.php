@extends('User.layouts.app')

@section('title', 'แก้ไขข้อมูลส่วนตัว')

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
                           class="list-group-item list-group-item-action active">
                            <i class="fas fa-edit me-2"></i>แก้ไขข้อมูล
                        </a>
                        <a href="{{ route('user.profile.social') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-share-alt me-2"></i>เชื่อมต่อโซเชียล
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- แก้ไขข้อมูลส่วนตัว -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">แก้ไขข้อมูลส่วนตัว</h4>
                    
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="avatar" class="form-label">รูปโปรไฟล์</label>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                   id="avatar" name="avatar" accept="image/*">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อ-นามสกุล</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', auth()->user()->profile->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                    </form>
                </div>
            </div>

            <!-- เปลี่ยนรหัสผ่าน -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">เปลี่ยนรหัสผ่าน</h4>
                    
                    <form action="{{ route('user.profile.update-password') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-primary">เปลี่ยนรหัสผ่าน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection