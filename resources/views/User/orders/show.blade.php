@extends('User.layouts.app')

@section('title', 'รายละเอียดคำสั่งซื้อ')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">รายละเอียดคำสั่งซื้อ #{{ $order->order_code }}</h2>
        <a href="{{ route('user.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> กลับไปหน้ารายการ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">รายการสินค้า</h5>
                    <span class="badge bg-{{ $order->status_color }}">
                        {{ __('orders.status.' . $order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 50px">รูป</th>
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
                                        <td>
                                            @if($item->product->image)
                                                <img src="{{ $item->product->image}}" 
                                                     alt="{{ $item->product->name }}"
                                                     class="img-thumbnail"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->product->name }}
                                            @if($discountPercent > 0)
                                                <br>
                                                <span class="badge bg-success">-{{ $discountPercent }}%</span>
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
                                    <td class="text-end">฿{{ number_format($orderTotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end text-danger">ส่วนลดรวม:</td>
                                    <td class="text-end text-danger">-฿{{ number_format($orderDiscount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end text-success">ยอดสุทธิหลังหักส่วนลด:</td>
                                    <td class="text-end text-success">฿{{ number_format($orderFinalTotal, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Upload Section -->
            @if($order->payment_status === 'pending')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">แจ้งชำระเงิน</h5>
                    </div>
                    <div class="card-body">
                        <!-- Bank Account Info -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading mb-2">ข้อมูลการโอนเงิน</h6>
                            <p class="mb-1">ธนาคาร: กสิกรไทย</p>
                            <p class="mb-1">เลขบัญชี: xxx-x-xxxxx-x</p>
                            <p class="mb-1">ชื่อบัญชี: บริษัท XXX จำกัด</p>
                            <p class="mb-0">จำนวนเงินที่ต้องชำระ: <strong class="text-success">฿{{ number_format($orderFinalTotal, 2) }}</strong></p>
                        </div>

                        <!-- Payment Form -->
                        <form action="{{ route('user.orders.upload-payment', $order) }}" 
                              method="POST" 
                              enctype="multipart/form-data"
                              id="paymentForm">
                            @csrf
                            <div class="mb-3">
                                <label for="payment_slip" class="form-label required">สลิปการโอนเงิน</label>
                                <input type="file" 
                                       class="form-control @error('payment_slip') is-invalid @enderror" 
                                       id="payment_slip" 
                                       name="payment_slip"
                                       accept="image/*"
                                       required>
                                <div class="form-text">รองรับไฟล์: JPG, PNG ขนาดไม่เกิน 2MB</div>
                                @error('payment_slip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payment_date" class="form-label required">วันที่โอนเงิน</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('payment_date') is-invalid @enderror" 
                                           id="payment_date" 
                                           name="payment_date"
                                           value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}"
                                           required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="payment_amount" class="form-label required">จำนวนเงินที่โอน</label>
                                    <div class="input-group">
                                        <span class="input-group-text">฿</span>
                                        <input type="number" 
                                               class="form-control @error('payment_amount') is-invalid @enderror" 
                                               id="payment_amount" 
                                               name="payment_amount"
                                               value="{{ old('payment_amount', $orderFinalTotal) }}"
                                               step="0.01"
                                               min="{{ $orderFinalTotal }}"
                                               required>
                                    </div>
                                    @error('payment_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div id="previewContainer" class="mb-3 d-none">
                                <label class="form-label">ตัวอย่างรูปภาพ</label>
                                <img id="preview" src="#" alt="ตัวอย่างสลิป" class="img-thumbnail" style="max-height: 200px">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>แจ้งชำระเงิน
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Order Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">ข้อมูลคำสั่งซื้อ</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>วันที่สั่งซื้อ:</strong><br>
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mb-2">
                        <strong>สถานะ:</strong><br>
                        <span class="badge bg-{{ $order->status_color }}">
                            {{ $order->status_text }}
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>สถานะการชำระเงิน:</strong><br>
                        <span class="badge bg-{{ $order->payment_status_color }}">
                            {{ $order->payment_status === 'paid' ? 'ชำระแล้ว' : 'รอชำระเงิน' }}
                        </span>
                    </p>
                </div>
            </div>
            <div class="mb-3">
                <a href="{{ route('user.orders.quotation', $order) }}" 
                   class="btn btn-secondary" 
                   target="_blank">
                    <i class="fas fa-file-pdf me-2"></i>ดาวน์โหลดใบเสนอราคา
                </a>
            </div>
            <!-- Shipping Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">ข้อมูลการจัดส่ง</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>ชื่อผู้รับ:</strong><br>
                        {{ $order->shipping_name }}
                    </p>
                    <p class="mb-2"><strong>ที่อยู่:</strong><br>
                        {{ $order->shipping_address }}
                    </p>
                    <p class="mb-0"><strong>เบอร์โทร:</strong><br>
                        {{ $order->shipping_phone }}
                    </p>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ข้อมูลการชำระเงิน</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>วิธีการชำระเงิน:</strong><br>
                        {{ __('orders.payment_method.' . $order->payment_method) }}
                    </p>
                    @if($order->payment_date)
                        <p class="mb-2"><strong>วันที่ชำระเงิน:</strong><br>
                            {{ \Carbon\Carbon::parse($order->payment_date)->format('d/m/Y H:i') }}
                        </p>
                    @endif
                    @if($order->payment_slip)
                        <p class="mb-0">
                            <strong>สลิปการโอนเงิน:</strong><br>
                            <a href="{{ $order->payment_slip }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-image me-2"></i>ดูสลิป
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required:after {
    content: ' *';
    color: red;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentSlip = document.getElementById('payment_slip');
    const previewContainer = document.getElementById('previewContainer');
    const preview = document.getElementById('preview');
    const paymentForm = document.getElementById('paymentForm');

    if (paymentSlip) {
        paymentSlip.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                }
                reader.readAsDataURL(e.target.files[0]);
            } else {
                previewContainer.classList.add('d-none');
            }
        });

        paymentForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังอัพโหลด...';
        });
    }
});
</script>
@endpush