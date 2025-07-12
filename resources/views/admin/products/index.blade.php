@extends('admin.layouts.admin')

@section('title', 'จัดการสินค้า')


<link href="{{ asset('css/admin/products.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">จัดการสินค้า</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-sm"></i> เพิ่มสินค้าใหม่
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">สินค้าทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">สินค้าพร้อมขาย</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">สินค้าหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['out_of_stock'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">หมวดหมู่ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_categories'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <!-- Card Header -->
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="m-0 font-weight-bold text-primary">รายการสินค้าทั้งหมด</h6>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('admin.products.index') }}" method="GET" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control" 
                               placeholder="ค้นหาสินค้า..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-header bg-light py-3">
            <form action="{{ route('admin.products.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
    <input type="text" name="category_name" class="form-control"
           placeholder="ค้นหาตามหมวดหมู่" value="{{ request('category_name') }}"
           list="categoryList">
    <datalist id="categoryList">
        @foreach($categoryNames as $cat)
            <option value="{{ $cat }}">
        @endforeach
    </datalist>
</div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>
                            พร้อมขาย
                        </option>
                        <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>
                            ไม่พร้อมขาย
                        </option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>
                            สินค้าหมด
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>
                            ล่าสุด
                        </option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                            ราคา (ต่ำ-สูง)
                        </option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                            ราคา (สูง-ต่ำ)
                        </option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                            ชื่อ (ก-ฮ)
                        </option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                            ชื่อ (ฮ-ก)
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> กรอง
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 80px;">รูปภาพ</th>
                            <th>ชื่อสินค้า</th>
                            <th>หมวดหมู่</th>
                            <th>ราคา</th>
                            <th>ส่วนลด (%)</th>
                            <th>สต็อก</th>
                            <th>แนะนำ</th>
                            <th>สถานะ</th>
                            <th style="width: 150px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr data-product-id="{{ $product->id }}">
                               <td class="text-center">
                                @if($product->image)
                                    <img src="{{ $product->image }}"
                                        alt="{{ $product->name }}"
                                        class="img-thumbnail"
                                        style="max-width: 50px;">
                                @else
                                    <span class="text-muted"><i class="fas fa-image"></i></span>
                                @endif
                            </td>
                                <td>
                                    {{ $product->name }}
                                    @if($product->featured)
                                        <span class="badge bg-info ms-1">แนะนำ</span>
                                    @endif
                                </td>
                                <td>{{ $product->category_name ?? '-' }}</td>
                                <td>
                                    <span class="{{ $product->discount_percent > 0 ? 'text-decoration-line-through text-danger' : '' }}">
                                        ฿{{ $product->formatted_price }}
                                    </span>
                                    @if($product->discount_percent > 0)
                                        <br>
                                        <span class="fw-bold text-success">฿{{ $product->formatted_discounted_price }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->discount_percent > 0)
                                        <span class="badge bg-success">-{{ (int)$product->discount_percent }}%</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $product->stock }}
                                    @if($product->stock == 0)
                                        <span class="badge bg-danger">สินค้าหมด</span>
                                    @elseif($product->is_low_stock)
                                        <span class="badge bg-warning">สต็อกต่ำ</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->featured)
                                        <span class="badge bg-info">แนะนำ</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->status_color }} status-badge">
                                        {{ $product->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.products.show', $product) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-success toggle-status"
                                                onclick="toggleStatus({{ $product->id }})"
                                                data-status="{{ $product->status }}"
                                                title="{{ $product->status === 'available' ? 'ปิดการขาย' : 'เปิดการขาย' }}">
                                            <i class="fas fa-toggle-{{ $product->status === 'available' ? 'on' : 'off' }}"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="deleteProduct({{ $product->id }})"
                                                title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">ไม่พบข้อมูลสินค้า</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.card-header select').forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});

function toggleStatus(productId) {
    const tr = document.querySelector(`tr[data-product-id="${productId}"]`);
    const statusBadge = tr.querySelector('.status-badge');
    const toggleBtn = tr.querySelector('.toggle-status');
    const toggleIcon = toggleBtn.querySelector('i');
    const currentStatus = toggleBtn.dataset.status;

    Swal.fire({
        title: 'ยืนยันการเปลี่ยนสถานะ',
        text: `คุณต้องการ${currentStatus === 'available' ? 'ปิด' : 'เปิด'}การขายสินค้านี้ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เปลี่ยนเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/products/${productId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status badge
                    statusBadge.textContent = data.status_text;
                    statusBadge.className = `badge bg-${data.status === 'available' ? 'success' : 'danger'} status-badge`;
                    
                    // Update toggle button
                    toggleBtn.dataset.status = data.status;
                    toggleBtn.title = `${data.status === 'available' ? 'ปิด' : 'เปิด'}การขาย`;
                    toggleIcon.className = `fas fa-toggle-${data.status === 'available' ? 'on' : 'off'}`;
                    
                    Swal.fire(
                        'สำเร็จ!',
                        'เปลี่ยนสถานะสินค้าเรียบร้อยแล้ว',
                        'success'
                    );
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'เกิดข้อผิดพลาด!',
                    error.message || 'ไม่สามารถเปลี่ยนสถานะสินค้าได้',
                    'error'
                );
            });
        }
    });
}

function deleteProduct(productId) {
    const tr = document.querySelector(`tr[data-product-id="${productId}"]`);
    if (!tr) return;

    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณแน่ใจหรือไม่ที่จะลบสินค้านี้? การกระทำนี้ไม่สามารถย้อนกลับได้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/products/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ลบแถวออกจากตาราง
                    tr.remove();
                    
                    // อัพเดทจำนวนสินค้าในการ์ด
                    const totalProducts = document.querySelector('.text-primary.text-uppercase + .h5');
                    if (totalProducts) {
                        totalProducts.textContent = parseInt(totalProducts.textContent) - 1;
                    }

                    Swal.fire(
                        'ลบสำเร็จ!',
                        'สินค้าถูกลบออกจากระบบแล้ว',
                        'success'
                    );
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'เกิดข้อผิดพลาด!',
                    error.message || 'ไม่สามารถลบสินค้าได้',
                    'error'
                );
            });
        }
    });
}
</script>
@endpush