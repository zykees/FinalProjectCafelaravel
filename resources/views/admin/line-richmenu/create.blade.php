{{-- filepath: resources/views/admin/line-richmenu/create.blade.php --}}
@extends('admin.layouts.admin')

@section('title', 'สร้าง LINE Rich Menu')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">สร้าง LINE Rich Menu</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.line-richmenu.create') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="mb-3">สร้าง Rich Menu ใหม่</h5>
                <div class="mb-3">
                    <label for="image" class="form-label">อัปโหลดรูปภาพ Rich Menu (PNG 2500x843px)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/png" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">ชื่อ Rich Menu</label>
                    <input type="text" class="form-control" id="name" name="name" value="MainMenu" required>
                </div>
                <div class="mb-3">
                    <label for="chatBarText" class="form-label">ข้อความแถบเมนู</label>
                    <input type="text" class="form-control" id="chatBarText" name="chatBarText" value="เมนูหลัก" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ลิงก์แต่ละปุ่ม (6 ปุ่ม เรียงจากซ้ายไปขวา)</label>
                    <div class="row g-2">
                        @for($i=0; $i<6; $i++)
                        <div class="col-md-4 mb-2">
                            <input type="url" class="form-control" name="button_uris[]" placeholder="ปุ่มที่ {{ $i+1 }} (https://...)" required>
                        </div>
                        @endfor
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> สร้าง Rich Menu
                </button>
            </form>
        </div>
    </div>
</div>
@endsection