{{-- filepath: resources/views/admin/line-richmenu/index.blade.php --}}
@extends('admin.layouts.admin')

@section('title', 'LINE Rich Menu ทั้งหมด')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">LINE Rich Menu ทั้งหมด</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-4">
        <a href="{{ route('admin.line-richmenu.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> สร้าง Rich Menu ใหม่
        </a>
    </div>

    @if(isset($richMenus) && count($richMenus) > 0)
        <div class="row">
            @foreach($richMenus as $menu)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        @if($menu->image_url)
                            <img src="{{ $menu->image_url }}" class="card-img-top" alt="Rich Menu Image" style="max-width:100%;max-height:180px;object-fit:cover;">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ $menu->name ?? '-' }}</h6>
                            <p class="card-text">ID: {{ $menu->rich_menu_id }}</p>
                            <p class="card-text">ChatBar: {{ $menu->chat_bar_text ?? '-' }}</p>
                            <div class="mb-2">
                                <strong>ลิงก์แต่ละปุ่ม:</strong>
                                <ol class="mb-0">
                                    @foreach($menu->button_uris as $i => $uri)
                                        <li>
                                            <div>URL: <a href="{{ $uri }}" target="_blank">{{ $uri }}</a></div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                            <form action="{{ route('admin.line-richmenu.set-default', $menu->rich_menu_id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">ตั้งเป็นค่าเริ่มต้น</button>
                            </form>
                            <form action="{{ route('admin.line-richmenu.delete', $menu->rich_menu_id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('ยืนยันลบ Rich Menu นี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">ยังไม่มี Rich Menu ในระบบ</div>
    @endif
</div>
@endsection