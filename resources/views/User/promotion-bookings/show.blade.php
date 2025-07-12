@extends('User.layouts.app')

@section('title', 'รายละเอียดการจอง #' . $booking->booking_code)

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-4">
                รายละเอียดการจอง #{{ $booking->booking_code }}
            </h3>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <!-- ข้อมูลการจอง -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">ข้อมูลการจอง</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>กิจกรรม:</strong></p>
                                    <p>{{ $booking->promotion->title }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>วันที่จอง:</strong></p>
                                    <p>{{ $booking->activity_date ? $booking->activity_date->format('d/m/Y') : '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>เวลา:</strong></p>
                                    <p>{{ $booking->activity_time ? \Carbon\Carbon::parse($booking->activity_time)->format('H:i') : '-' }} น.</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>จำนวนผู้เข้าร่วม:</strong></p>
                                    <p>{{ $booking->number_of_participants }} คน</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>สถานที่:</strong></p>
                                    <p>{{ $booking->promotion->location }}</p>
                                </div>
                            </div>
                            @if($booking->note)
                                <div class="mb-3">
                                    <p class="mb-1"><strong>หมายเหตุ:</strong></p>
                                    <p>{{ $booking->note }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- ข้อมูลการชำระเงิน -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">การชำระเงิน</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>ราคารวม:</strong></p>
                                    <p>฿{{ number_format($booking->total_price, 2) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>ส่วนลด:</strong></p>
                                    <p>฿{{ number_format($booking->discount_amount, 2) }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>ยอดชำระสุทธิ:</strong></p>
                                    <h4 class="text-primary">฿{{ number_format($booking->final_price, 2) }}</h4>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>สถานะการชำระ:</strong></p>
                                    <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ $booking->payment_status === 'paid' ? 'ชำระแล้ว' : 'รอชำระเงิน' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- อัปโหลดสลิปการโอนเงิน หรือแสดงข้อมูลการชำระเงิน -->
                    @if($booking->payment_status === 'pending')
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">อัพโหลดสลิปการโอนเงิน</h5>
                                <form action="{{ route('user.promotion-bookings.upload-payment', $booking) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">วันที่โอนเงิน</label>
                                        <input type="datetime-local" 
                                               class="form-control @error('payment_date') is-invalid @enderror"
                                               name="payment_date"
                                               required>
                                        @error('payment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">จำนวนเงินที่โอน</label>
                                        <input type="number"
                                               class="form-control @error('payment_amount') is-invalid @enderror"
                                               name="payment_amount"
                                               min="{{ $booking->final_price }}"
                                               value="{{ $booking->final_price }}"
                                               step="0.01"
                                               required>
                                        @error('payment_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">สลิปการโอนเงิน</label>
                                        <input type="file" 
                                               class="form-control @error('payment_slip') is-invalid @enderror" 
                                               name="payment_slip" 
                                               accept="image/*" 
                                               required>
                                        @error('payment_slip')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>อัพโหลดสลิป
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        @if($booking->payment_slip)
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">ข้อมูลการชำระเงิน</h5>
                                    <div class="mb-3">
                                        <p><strong>วันที่โอน:</strong>
                                            {{ $booking->payment_date ? \Carbon\Carbon::parse($booking->payment_date)->format('d/m/Y H:i') : '-' }}
                                        </p>
                                        <p><strong>จำนวนเงิน:</strong> ฿{{ $booking->payment_amount ? number_format($booking->payment_amount, 2) : '-' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <p><strong>สลิปการโอนเงิน:</strong></p>
                                        <a href="{{ $booking->payment_slip }}" target="_blank">
                                            <img src="{{ $booking->payment_slip }}" alt="Payment Slip" class="img-fluid rounded" style="max-width:300px;">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="col-md-4">
                    <!-- Payment Information -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">ช่องทางการชำระเงิน</h5>
                            <p class="mb-2"><strong>ธนาคารกสิกรไทย</strong></p>
                            <p class="mb-1">เลขที่บัญชี: xxx-x-xxxxx-x</p>
                            <p class="mb-3">ชื่อบัญชี: บริษัท XXX จำกัด</p>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>กรุณาชำระเงินภายใน 24 ชั่วโมง
                            </div>
                        </div>
                    </div>

                    <!-- Download Section -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ดาวน์โหลดเอกสาร</h5>
                            <a href="{{ route('user.promotion-bookings.quotation', $booking) }}" 
                               class="btn btn-outline-primary w-100">
                                <i class="fas fa-file-pdf me-2"></i>ใบเสนอราคา
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection