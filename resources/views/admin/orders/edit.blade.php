@php
    use App\Models\Order;
@endphp

@extends('admin.layouts.admin')

@section('title', 'แก้ไขออเดอร์')


<link href="{{ asset('css/admin/orders.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">แก้ไขออเดอร์ #{{ $order->order_code }}</h1>
        <div>
            <button type="button" class="btn btn-info btn-sm me-2" onclick="printOrder()">
                <i class="fas fa-print"></i> พิมพ์ใบสั่งซื้อ
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> กลับไปหน้ารายการ
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">รายละเอียดออเดอร์</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>สินค้า</th>
                                    <th width="100">จำนวน</th>
                                    <th class="text-end">ราคาปกติ</th>
                                    <th class="text-end">ส่วนลด</th>
                                    <th class="text-end">ราคาหลังหักส่วนลด</th>
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
                                        <td>{{ $item->product->name }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
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
                                    <td colspan="5" class="text-end">ราคารวมปกติ:</td>
                                    <td class="text-end">฿{{ number_format($order->calculated_subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end text-danger">ส่วนลดรวม:</td>
                                    <td class="text-end text-danger">-฿{{ number_format($order->calculated_discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end text-success">ยอดสุทธิหลังหักส่วนลด:</td>
                                    <td class="text-end text-success">฿{{ number_format($order->calculated_total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Form -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">อัพเดทสถานะ</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label required">สถานะออเดอร์</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="{{ Order::STATUS_PENDING }}" 
                                    {{ $order->status == Order::STATUS_PENDING ? 'selected' : '' }}>
                                    รอดำเนินการ
                                </option>
                                <option value="{{ Order::STATUS_PROCESSING }}" 
                                    {{ $order->status == Order::STATUS_PROCESSING ? 'selected' : '' }}>
                                    กำลังดำเนินการ
                                </option>
                                <option value="{{ Order::STATUS_COMPLETED }}" 
                                    {{ $order->status == Order::STATUS_COMPLETED ? 'selected' : '' }}>
                                    เสร็จสิ้น
                                </option>
                                <option value="{{ Order::STATUS_CANCELLED }}" 
                                    {{ $order->status == Order::STATUS_CANCELLED ? 'selected' : '' }}>
                                    ยกเลิก
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="payment_status" class="form-label required">สถานะการชำระเงิน</label>
                            <select class="form-select @error('payment_status') is-invalid @enderror" 
                                    id="payment_status" name="payment_status" required>
                                <option value="{{ Order::PAYMENT_PENDING }}" 
                                    {{ $order->payment_status == Order::PAYMENT_PENDING ? 'selected' : '' }}>
                                    รอชำระเงิน
                                </option>
                                <option value="{{ Order::PAYMENT_PAID }}" 
                                    {{ $order->payment_status == Order::PAYMENT_PAID ? 'selected' : '' }}>
                                    ชำระแล้ว
                                </option>
                                <option value="{{ Order::PAYMENT_FAILED }}" 
                                    {{ $order->payment_status == Order::PAYMENT_FAILED ? 'selected' : '' }}>
                                    ชำระไม่สำเร็จ
                                </option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    id="notes" name="notes" rows="3">{{ old('notes', $order->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </form>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลลูกค้า</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>ชื่อ:</strong> {{ $order->user->name }}</p>
                    <p class="mb-1"><strong>อีเมล:</strong> {{ $order->user->email }}</p>
                    <p class="mb-1"><strong>เบอร์โทร:</strong> {{ $order->user->phone ?? '-' }}</p>
                    <p class="mb-0"><strong>วันที่สั่งซื้อ:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
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

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...';
    });
});
</script>
@endpush