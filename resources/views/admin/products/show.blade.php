@extends('admin.layouts.admin')

@section('title', 'รายละเอียดสินค้า')


<link href="{{ asset('css/admin/products.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายละเอียดสินค้า</h1>
        <div>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit fa-sm"></i> แก้ไขสินค้า
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left fa-sm"></i> กลับไปหน้ารายการ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">รูปภาพสินค้า</h6>
                </div>
                <div class="card-body text-center">
                    @if($product->image)
                        <img src="{{ $product->image }}" 
                            alt="{{ $product->name }}" 
                            class="img-fluid rounded">
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-image fa-3x mb-3"></i>
                            <p class="mb-0">ไม่มีรูปภาพ</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">สถิติ</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h4 class="mb-0">{{ $product->orders->count() }}</h4>
                            <small class="text-muted">ออเดอร์ทั้งหมด</small>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="mb-0">{{ $product->orders->sum('pivot.quantity') }}</h4>
                            <small class="text-muted">จำนวนที่ขายได้</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลสินค้า</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width: 200px;">รหัสสินค้า</th>
                                <td>{{ $product->id }}</td>
                            </tr>
                            <tr>
                                <th>ชื่อสินค้า</th>
                                <td>
                                    {{ $product->name }}
                                    @if($product->featured)
                                        <span class="badge bg-info ms-2">แนะนำ</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>หมวดหมู่</th>
                                <td>
                                    {{ $product->category_name ?? '-'}}
                                </td>
                            </tr>
                            <tr>
                                <th>ราคา</th>
    <td>
        <span class="{{ $product->discount_percent > 0 ? 'text-decoration-line-through text-danger' : '' }}">
            ฿{{ $product->formatted_price }}
        </span>
        @if($product->discount_percent > 0)
            <span class="badge bg-success ms-2">ส่วนลด {{ (int)$product->discount_percent }}%</span>
            <br>
            <span class="fw-bold text-success">ราคาหลังหักส่วนลด: ฿{{ $product->formatted_discounted_price }}</span>
        @endif
    </td>
</tr>
<tr>
    <th>ส่วนลด (%)</th>
    <td>
        {{ $product->discount_percent > 0 ? (int)$product->discount_percent . '%' : '-' }}
    </td>
</tr>
                            </tr>
                            <tr>
                                <th>สต็อก</th>
                                <td>
                                    {{ $product->stock }} ชิ้น
                                    @if($product->is_low_stock)
                                        <span class="badge bg-warning ms-2">สต็อกต่ำ</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>สต็อกขั้นต่ำ</th>
                                <td>{{ $product->minimum_stock }} ชิ้น</td>
                            </tr>
                            <tr>
                                <th>สถานะ</th>
                                <td>
                                    <span class="badge bg-{{ $product->status_color }}">
                                        {{ $product->status_text }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>รายละเอียด</th>
                                <td>{{ $product->description ?: 'ไม่มีรายละเอียด' }}</td>
                            </tr>
                            <tr>
                                <th>วันที่สร้าง</th>
                                <td>{{ $product->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>แก้ไขล่าสุด</th>
                                <td>{{ $product->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">ออเดอร์ล่าสุด</h6>
                    <span class="small">5 รายการล่าสุด</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>รหัสออเดอร์</th>
                                    <th>ลูกค้า</th>
                                    <th>จำนวน</th>
                                    <th>ราคารวม</th>
                                    <th>วันที่</th>
                                    <th>สถานะ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->orders()->latest()->take(5)->get() as $order)
                                    <tr>
                                        <td>{{ $order->order_code }}</td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>{{ $order->pivot->quantity }}</td>
                                        <td>฿{{ number_format($order->pivot->quantity * $order->pivot->price, 2) }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status_color }}">
                                                {{ __('orders.status.' . $order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">ไม่มีประวัติการสั่งซื้อ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
