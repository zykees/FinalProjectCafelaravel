{{-- filepath: c:\xampp\htdocs\projectcafelaravel10\resources\views\admin\promotion-bookings\show.blade.php --}}
@extends('admin.layouts.admin')

@section('title', 'รายละเอียดการจอง')

<link rel="stylesheet" href="{{ asset('css/admin/promotion-bookings.css') }}">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายละเอียดการจอง #{{ $booking->booking_code }}</h1>
        <div>
            <a href="{{ route('admin.promotion-bookings.edit', $booking) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> แก้ไข
            </a>
            <a href="{{ route('admin.promotion-bookings.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลการจอง</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ผู้จอง:</strong> {{ $booking->user->name }}</p>
                            <p><strong>อีเมล:</strong> {{ $booking->user->email }}</p>
                            <p><strong>เบอร์โทร:</strong> {{ $booking->user->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>วันที่จอง:</strong> {{ $booking->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>วันที่จัดกิจกรรม:</strong> {{ $booking->activity_date ? $booking->activity_date->format('d/m/Y') : '-' }}</p>
                            <p><strong>วันสิ้นสุดกิจกรรม:</strong>
    {{ 
        $booking->promotion && $booking->promotion->ends_at
            ? \Carbon\Carbon::parse($booking->promotion->ends_at)->format('d/m/Y')
            : (
                $booking->activity_end_date
                    ? \Carbon\Carbon::parse($booking->activity_end_date)->format('d/m/Y')
                    : '-'
            )
    }}
</p>
                            <p><strong>เวลา:</strong> {{ $booking->activity_time ? \Carbon\Carbon::parse($booking->activity_time)->format('H:i') : '-' }} น.</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>กิจกรรม:</strong> {{ $booking->promotion->title }}</p>
                            <p><strong>จำนวนผู้เข้าร่วม:</strong> {{ $booking->number_of_participants }} คน</p>
                            <p><strong>สถานที่:</strong> {{ $booking->promotion->location }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ราคารวม:</strong> ฿{{ number_format($booking->total_price, 2) }}</p>
                            <p><strong>ส่วนลด:</strong> ฿{{ number_format($booking->discount_amount, 2) }}</p>
                            <p><strong>ยอดชำระสุทธิ:</strong> ฿{{ number_format($booking->final_price, 2) }}</p>
                        </div>
                    </div>

                    @if($booking->note)
                        <div class="mb-3">
                            <p><strong>หมายเหตุจากผู้จอง:</strong></p>
                            <p>{{ $booking->note }}</p>
                        </div>
                    @endif

                    @if($booking->admin_comment)
                        <div class="mb-3">
                            <p><strong>หมายเหตุจากแอดมิน:</strong></p>
                            <p>{{ $booking->admin_comment }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">สถานะ</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p><strong>สถานะการจอง:</strong></p>
                        <span class="badge bg-{{ $booking->status_color }} p-2">
                            {{ __('bookings.status.' . $booking->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <p><strong>สถานะการชำระเงิน:</strong></p>
                        <span class="badge bg-{{ $booking->payment_status_color }} p-2">
                            {{ __('bookings.payment_status.' . $booking->payment_status) }}
                        </span>
                    </div>

                    @if($booking->payment_slip)
                        <div class="mb-3">
                            <p><strong>สลิปการโอนเงิน:</strong></p>
                            <a href="{{ $booking->payment_slip }}" target="_blank">
                                <img src="{{ $booking->payment_slip }}" 
                                     class="img-fluid rounded mb-2" 
                                     alt="Payment Slip"
                                     style="max-width: 100%; height: auto; cursor: pointer"
                                     title="คลิกเพื่อดูรูปขนาดเต็ม">
                            </a>
                            <div class="mt-2">
                                <p><strong>วันที่โอน:</strong>
                                    {{ $booking->payment_date ? $booking->payment_date->format('d/m/Y H:i') : '-' }}
                                </p>
                                <p><strong>จำนวนเงิน:</strong>
                                    ฿{{ $booking->payment_amount ? number_format($booking->payment_amount, 2) : '-' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.promotion-bookings.update-payment', $booking) }}" 
                          method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">อัพเดทสถานะการชำระเงิน</label>
                            <select name="payment_status" class="form-select mb-2" required>
                                <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>
                                    รอชำระเงิน
                                </option>
                                <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>
                                    ชำระแล้ว
                                </option>
                                <option value="rejected" {{ $booking->payment_status == 'rejected' ? 'selected' : '' }}>
                                    ปฏิเสธการชำระ
                                </option>
                            </select>
                            <textarea name="admin_comment" class="form-control mb-2" 
                                      placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                            <button type="submit" class="btn btn-primary w-100">บันทึกสถานะการชำระเงิน</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection