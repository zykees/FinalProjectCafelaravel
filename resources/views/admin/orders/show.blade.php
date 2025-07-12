@php
    use App\Models\Order;
@endphp

@extends('admin.layouts.admin')

@section('title', 'รายละเอียดออเดอร์')

<link href="{{ asset('css/admin/orders.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายละเอียดออเดอร์ #{{ $order->order_code }}</h1>
        <div>
            <button type="button" class="btn btn-info btn-sm me-2" onclick="printOrder()">
                <i class="fas fa-print"></i> พิมพ์ใบสั่งซื้อ
            </button>
            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-edit"></i> แก้ไข
            </a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Items -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">รายการสินค้า</h6>
                    <span class="badge bg-{{ $order->status_color }}">
                        {{ __('orders.status.' . $order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>รูปภาพ</th>
                                    <th>สินค้า</th>
                                    <th class="text-end">ราคาปกติ</th>
                                    <th class="text-end">ส่วนลด</th>
                                    <th class="text-end">ราคาหลังหักส่วนลด</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-end">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $discountPercent = $item->product->discount_percent ?? 0;
                                        $originalPrice = $item->product->price;
                                        $discountedPrice = $discountPercent > 0
                                            ? round($originalPrice * (1 - $discountPercent/100), 2)
                                            : $originalPrice;
                                        $itemTotal = $discountedPrice * $item->quantity;
                                        $itemDiscount = ($originalPrice - $discountedPrice) * $item->quantity;
                                    @endphp
                                    <tr>
                                        <td style="width: 80px">
                                            <img src="{{ $item->product->image}}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 50px">
                                        </td>
                                        <td>
                                            {{ $item->product->name }}
                                            @if($discountPercent > 0)
                                                <br>
                                                <span class="badge bg-success">-{{ $discountPercent }}%</span>
                                            @endif
                                            @if($item->product->featured)
                                                <span class="badge bg-info ms-1">แนะนำ</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($discountPercent > 0)
                                                <span class="text-decoration-line-through text-danger">
                                                    ฿{{ number_format($originalPrice, 2) }}
                                                </span>
                                            @else
                                                <span class="fw-bold text-primary">
                                                    ฿{{ number_format($originalPrice, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($discountPercent > 0)
                                                <span class="text-success">-฿{{ number_format($originalPrice - $discountedPrice, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success">
                                                ฿{{ number_format($discountedPrice, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary">
                                                ฿{{ number_format($itemTotal, 2) }}
                                            </span>
                                            @if($discountPercent > 0 && $itemDiscount > 0)
                                                <br>
                                                <small class="text-danger">ประหยัดไป ฿{{ number_format($itemDiscount, 2) }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end">ราคารวมปกติ:</td>
                                    <td class="text-end">฿{{ number_format($order->calculated_subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end text-danger">ส่วนลดรวม:</td>
                                    <td class="text-end text-danger">-฿{{ number_format($order->calculated_discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end text-success">ยอดสุทธิหลังหักส่วนลด:</td>
                                    <td class="text-end text-success">฿{{ number_format($order->calculated_total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="col-xl-4">
            <!-- Customer Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลลูกค้า</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold">ชื่อ:</label>
                        <p class="mb-0">{{ $order->user->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">อีเมล:</label>
                        <p class="mb-0">{{ $order->user->email }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">เบอร์โทร:</label>
                        <p class="mb-0">{{ $order->user->phone ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">สถานะออเดอร์</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold">รหัสออเดอร์:</label>
                        <p class="mb-0">{{ $order->order_code }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">วันที่สั่งซื้อ:</label>
                        <p class="mb-0">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">สถานะ:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $order->status_color }}">
                                {{ __('orders.status.' . $order->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">สถานะการชำระเงิน:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $order->payment_status_color }}">
                                {{ __('orders.payment_status.' . $order->payment_status) }}
                            </span>
                        </p>
                    </div>
                    @if($order->promotion)
                        <div class="mb-3">
                            <label class="fw-bold">โปรโมชัน:</label>
                            <p class="mb-0">{{ $order->promotion->title }}</p>
                        </div>
                    @endif
                    @if($order->notes)
                        <div class="mb-0">
                            <label class="fw-bold">หมายเหตุ:</label>
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif
                    @if($order->payment_slip)
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">หลักฐานการชำระเงิน</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <img src="{{ $order->payment_slip }}" alt="slip" style="max-width:200px;" class="img-thumbnail">
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>วันที่โอน:</strong> {{ $order->payment_date ? $order->payment_date->format('d/m/Y H:i') : '-' }}</p>
                                        <p><strong>จำนวนเงิน:</strong> ฿{{ number_format($order->payment_amount, 2) }}</p>
                                        <p><strong>สถานะ:</strong> 
                                            <span class="badge bg-{{ $order->payment_status_color }}">
                                                {{ __('orders.payment_status.' . $order->payment_status) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printOrder() {
    window.open('{{ route('admin.orders.print', $order) }}', '_blank');
}
</script>
@endpush