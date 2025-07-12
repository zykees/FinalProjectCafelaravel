@extends('admin.layouts.admin')

@section('title', 'แก้ไขการจองกิจกรรม')

    <link rel="stylesheet" href="{{ asset('css/admin/promotion-bookings.css') }}">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">แก้ไขการจองกิจกรรม #{{ $booking->booking_code }}</h1>
        <a href="{{ route('admin.promotion-bookings.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> กลับ
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.promotion-bookings.update', $booking) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">สถานะการจอง</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    name="status" required>
                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>
                                    รอดำเนินการ
                                </option>
                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>
                                    ยืนยันแล้ว
                                </option>
                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>
                                    ยกเลิก
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">สถานะการชำระเงิน</label>
                            <select class="form-select @error('payment_status') is-invalid @enderror" 
                                    name="payment_status" required>
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
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="admin_comment" class="form-label">หมายเหตุจากแอดมิน</label>
                    <textarea class="form-control @error('admin_comment') is-invalid @enderror" 
                              name="admin_comment" rows="3">{{ old('admin_comment', $booking->admin_comment) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
            </form>
        </div>
    </div>
</div>
@endsection