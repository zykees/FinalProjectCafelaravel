@extends('User.layouts.app')

@section('title', 'ประวัติการจองกิจกรรม')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">ประวัติการจองกิจกรรม</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($bookings->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>ยังไม่มีประวัติการจองกิจกรรม
            <a href="{{ route('user.promotions.index') }}" class="alert-link">ดูกิจกรรมที่น่าสนใจ</a>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>รหัสการจอง</th>
                                <th>กิจกรรม</th>
                                <th>วันที่จอง</th>
                                <th class="text-center">จำนวนที่นั่ง</th>
                                <th class="text-end">ราคาปกติ/ที่นั่ง</th>
                                <th class="text-end">ราคาหลังหักส่วนลด/ที่นั่ง</th>
                                <th class="text-end">ส่วนลดรวม</th>
                                <th class="text-end">ยอดชำระรวม</th>
                                <th>สถานะการชำระเงิน</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                @php
                                    $promotion = $booking->promotion;
                                    $price = $promotion->price_per_person;
                                    $discount = $promotion->discount ?? 0;
                                    $discountedPrice = $discount > 0
                                        ? round($price * (1 - $discount/100), 2)
                                        : $price;
                                    $participants = $booking->number_of_participants ?? $booking->seats ?? 1;
                                    $discountAmount = ($price - $discountedPrice) * $participants;
                                    // ถ้ามี final_price ใน DB ให้ใช้เลย
                                    $finalPrice = $booking->final_price ?? ($discountedPrice * $participants);
                                @endphp
                                <tr>
                                    <td>{{ $booking->booking_code }}</td>
                                    <td>{{ $promotion->title }}</td>
                                    <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">{{ $participants }}</td>
                                    <td class="text-end">
                                        <span class="text-decoration-line-through text-danger">
                                            ฿{{ number_format($price, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $discount > 0 ? 'text-success fw-bold' : 'text-primary' }}">
                                            ฿{{ number_format($discountedPrice, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($discount > 0)
                                            <span class="text-success">
                                                -฿{{ number_format($discountAmount, 2) }}
                                            </span>
                                            <br>
                                            <small>{{ $discount }}% ส่วนลด</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary">
                                            ฿{{ number_format($finalPrice, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ $booking->payment_status === 'paid' ? 'ชำระแล้ว' : 'รอชำระเงิน' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('user.promotion-bookings.show', $booking) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> ดูรายละเอียด
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection