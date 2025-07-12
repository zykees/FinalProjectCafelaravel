@extends('admin.layouts.admin')

@section('title', 'จัดการกิจกรรม')


    <link rel="stylesheet" href="{{ asset('css/admin/promotions.css') }}">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">จัดการกิจกรรม</h1>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> สร้างกิจกรรมใหม่
        </a>
    </div>

    {{-- สรุป 4 กล่อง --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">กิจกรรมทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">กิจกรรมเปิดรับจอง</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">กิจกรรมปิดรับจอง</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">กิจกรรมแนะนำ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['featured'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- จบ 4 กล่อง --}}

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ฟิลเตอร์แบบ orders --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('admin.promotions.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">ทุกสถานะ</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>เปิดรับจอง</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>ปิดรับจอง</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="is_featured" class="form-select" onchange="this.form.submit()">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('is_featured') === '1' ? 'selected' : '' }}>เฉพาะกิจกรรมแนะนำ</option>
                        <option value="0" {{ request('is_featured') === '0' ? 'selected' : '' }}>ไม่ใช่กิจกรรมแนะนำ</option>
                    </select>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="date" name="starts_at" class="form-control" value="{{ request('starts_at') }}">
                        <span class="input-group-text">ถึง</span>
                        <input type="date" name="ends_at" class="form-control" value="{{ request('ends_at') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อกิจกรรม..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>รูปภาพ</th>
                            <th>ชื่อกิจกรรม</th>
                            <th>วันที่จัด</th>
                            <th>ผู้เข้าร่วม</th>
                            <th>ราคา/คน</th>
                            <th>ส่วนลด (%)</th>
                            <th>แนะนำ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $promotion)
                            <tr>
                                <td>
                                @if($promotion->image)
                                    <img src="{{ $promotion->image }}"
                                        alt="{{ $promotion->title }}"
                                        class="img-thumbnail"
                                        style="max-height: 50px; width: auto;"
                                        onclick="window.open(this.src, '_blank')"
                                        title="คลิกเพื่อดูรูปขนาดเต็ม">
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-image"></i> ไม่มีรูป
                                    </span>
                                @endif
                                </td>
                                <td>{{ $promotion->title }}</td>
                                <td>
                                    {{ $promotion->starts_at ? $promotion->starts_at->format('d/m/Y H:i') : '-' }}
                                    <br>ถึง<br>
                                    {{ $promotion->ends_at ? $promotion->ends_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td>
                                    {{ $promotion->current_participants }}/{{ $promotion->max_participants }}
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $promotion->max_participants > 0 ? ($promotion->current_participants / $promotion->max_participants) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                </td>
                                <td>฿{{ number_format($promotion->price_per_person, 2) }}</td>
                                <td>
                                    @if($promotion->discount > 0)
                                        <span class="badge bg-success">-{{ (int)$promotion->discount }}%</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($promotion->is_featured)
                                        <span class="badge bg-success">แนะนำ</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $promotion->status === 'active' ? 'success' : 'danger' }}">
                                        {{ $promotion->status === 'active' ? 'เปิดรับจอง' : 'ปิดรับจอง' }}
                                    </span>
                                </td>
                    
                                
                                 <td>
                                    <a href="{{ route('admin.promotions.show', $promotion) }}" 
                                       class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}" 
                                       class="btn btn-primary btn-sm" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.promotions.destroy', $promotion) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('ยืนยันการลบกิจกรรม?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">ไม่พบข้อมูลกิจกรรม</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $promotions->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ป้องกัน input ฟิลด์เลขให้พิมพ์ได้เฉพาะตัวเลข (0-9) เท่านั้น
    document.querySelectorAll('input[type="number"]').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            // อนุญาตเฉพาะตัวเลข, backspace, delete, tab, arrow, home, end
            if (
                // ตัวเลขบนแป้นหลัก
                (e.keyCode >= 48 && e.keyCode <= 57 && !e.shiftKey) ||
                // ตัวเลขบน numpad
                (e.keyCode >= 96 && e.keyCode <= 105) ||
                // ปุ่มควบคุม
                [8,9,13,27,46,37,38,39,40].includes(e.keyCode)
            ) {
                // อนุญาต
            } else {
                e.preventDefault();
            }
        });
        // ป้องกัน paste อะไรที่ไม่ใช่ตัวเลข
        el.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});
</script>
@endpush