@extends('User.layouts.app')

@section('title', 'แดชบอร์ด')

<link href="{{ asset('css/users/dashboard.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">แดชบอร์ด</h1>
        <a href="{{ route('user.promotions.index') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>จองกิจกรรมใหม่
        </a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- Activity Statistics --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-calendar-check me-2"></i>การจองกิจกรรม
                    </h5>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="border rounded p-3 text-center bg-light">
                                <h3 class="mb-0">{{ $activityStats['total'] ?? 0 }}</h3>
                                <small class="text-muted">ทั้งหมด</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3 text-center bg-warning bg-opacity-10">
                                <h3 class="mb-0 text-warning">{{ $activityStats['pending'] ?? 0 }}</h3>
                                <small class="text-muted">รอดำเนินการ</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3 text-center bg-success bg-opacity-10">
                                <h3 class="mb-0 text-success">{{ $activityStats['confirmed'] ?? 0 }}</h3>
                                <small class="text-muted">ยืนยันแล้ว</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Statistics --}}
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-shopping-cart me-2"></i>การสั่งซื้อ
                    </h5>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="border rounded p-3 text-center bg-light">
                                <h3 class="mb-0">{{ $orderStats['total'] ?? 0 }}</h3>
                                <small class="text-muted">ทั้งหมด</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3 text-center bg-warning bg-opacity-10">
                                <h3 class="mb-0 text-warning">{{ $orderStats['pending'] ?? 0 }}</h3>
                                <small class="text-muted">รอดำเนินการ</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3 text-center bg-success bg-opacity-10">
                                <h3 class="mb-0 text-success">{{ $orderStats['completed'] ?? 0 }}</h3>
                                <small class="text-muted">สำเร็จ</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-newspaper me-2"></i> ข่าวสารล่าสุด
    </div>
    <div class="card-body">
        @if($news->count())
            <ul class="list-group list-group-flush">
                @foreach($news as $item)
                    <li class="list-group-item">
                        <strong>{{ $item->title }}</strong>
                        <span class="text-muted small">
                            {{ $item->published_at ? $item->published_at->format('d/m/Y H:i') : '' }}
                        </span>
                        <div class="mt-1">
                            {{ Str::limit(strip_tags($item->content), 120) }}
                        </div>
                        @if($item->image)
                            <div class="mt-2">
                                <img src="{{ $item->getImageUrl() }}" alt="news image" style="max-width: 120px;">
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="mt-2 text-end">
                <a href="{{ route('user.news.index') }}" class="btn btn-link">ดูข่าวทั้งหมด</a>
            </div>
        @else
            <div class="text-muted">ยังไม่มีข่าวสาร</div>
        @endif
    </div>
</div> 
    {{-- Upcoming Activities --}}
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5 class="card-title mb-4">
                <i class="fas fa-calendar me-2"></i>กิจกรรมที่กำลังจะมาถึง
            </h5>
            @if(isset($upcomingActivities) && $upcomingActivities->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสการจอง</th>
                                <th>กิจกรรม</th>
                                <th>วันที่</th>
                                <th>เวลา</th>
                                <th>จำนวนผู้เข้าร่วม</th>
                                <th>สถานะ</th>
                                <th>การชำระเงิน</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingActivities as $booking)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $booking->booking_code }}</small>
                                    </td>
                                    <td>{{ $booking->promotion->title ?? 'ไม่พบข้อมูล' }}</td>
                                    <td>{{ $booking->activity_date ? $booking->activity_date->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $booking->activity_time ? \Carbon\Carbon::parse($booking->activity_time)->format('H:i') : '-' }} น.</td>
                                    <td>{{ $booking->number_of_participants }} คน</td>
                                    <td>
                                        <span class="badge bg-{{ $booking->status_color }}">
                                            @switch($booking->status)
                                                @case('pending')
                                                    รอดำเนินการ
                                                    @break
                                                @case('confirmed')
                                                    ยืนยันแล้ว
                                                    @break
                                                @case('cancelled')
                                                    ยกเลิก
                                                    @break
                                                @default
                                                    {{ $booking->status }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $booking->payment_status_color }}">
                                            @switch($booking->payment_status)
                                                @case('pending')
                                                    รอชำระเงิน
                                                    @break
                                                @case('paid')
                                                    ชำระแล้ว
                                                    @break
                                                @case('rejected')
                                                    ไม่อนุมัติ
                                                    @break
                                                @default
                                                    {{ $booking->payment_status }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('user.promotion-bookings.show', $booking) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>รายละเอียด
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <img src="{{ asset('images/no-data.svg') }}" alt="No Data" class="mb-3" style="height: 150px;">
                    <p class="text-muted mb-0">ไม่มีกิจกรรมที่กำลังจะมาถึง</p>
                    <a href="{{ route('user.promotions.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-calendar-plus me-2"></i>จองกิจกรรมเลย
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection



@push('scripts')
<script>
$(document).ready(function() {
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
});
</script>
@endpush