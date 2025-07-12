@extends('User.layouts.app')

@section('title', $promotion->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">หน้าหลัก</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.promotions.index') }}">โปรโมชั่น</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $promotion->title }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="row g-0 align-items-stretch">
            @if($promotion->image)
                <div class="col-lg-5 col-md-6 col-12 d-flex align-items-center">
                    <img src="{{ $promotion->image }}"
                         class="img-fluid rounded-start w-100"
                         alt="{{ $promotion->title }}"
                         style="min-height:220px;max-height:400px;object-fit:cover;">
                </div>
            @endif
            <div class="@if($promotion->image) col-lg-7 col-md-6 col-12 @else col-12 @endif">
                <div class="card-body">
                    <h2 class="card-title mb-3">{{ $promotion->title }}</h2>
                    
                    @if($promotion->isExpired())
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            กิจกรรมนี้ไม่สามารถจองได้แล้ว เนื่องจาก
                            @if($promotion->ends_at->isPast())
                                หมดระยะเวลาจอง
                            @elseif($promotion->status === 'inactive')
                                ปิดรับจองชั่วคราว
                            @else
                                จำนวนผู้เข้าร่วมเต็มแล้ว
                            @endif
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="lead text-muted">{{ $promotion->description }}</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title">รายละเอียดการจัด</h5>
                                    <p class="mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        {{ $promotion->location }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ $promotion->starts_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        {{ $promotion->starts_at->format('H:i') }} - 
                                        {{ $promotion->ends_at->format('H:i') }} น.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h5 class="card-title">จำนวนผู้เข้าร่วม</h5>
                                    <div class="progress mb-2" style="height:0.5rem;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ ($promotion->current_participants / $promotion->max_participants) * 100 }}%">
                                        </div>
                                    </div>
                                    <p class="mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        เหลือ {{ $promotion->getRemainingSlots() }} ที่นั่ง
                                        จากทั้งหมด {{ $promotion->max_participants }} ที่นั่ง
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>รายละเอียดกิจกรรม</h5>
                        <div class="activity-details">
                            {!! $promotion->activity_details !!}
                        </div>
                    </div>

                    @if($promotion->included_items)
                        <div class="mb-4">
                            <h5>สิ่งที่ผู้เข้าร่วมจะได้รับ</h5>
                            <div class="included-items">
                                {!! $promotion->included_items !!}
                            </div>
                        </div>
                    @endif

                    <!-- ราคาปกติ/ราคาหลังหักส่วนลด/ส่วนลด -->
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                <div>
                                    @php
                                        $price = $promotion->price_per_person;
                                        $discount = $promotion->discount ?? 0;
                                        $priceAfterDiscount = $discount > 0
                                            ? round($price * (1 - $discount/100), 2)
                                            : $price;
                                    @endphp
                                    @if($discount > 0)
                                        <div>
                                            <span class="text-decoration-line-through me-2" style="color:#ffc107;">
                                                ฿{{ number_format($price, 2) }}
                                            </span>
                                            <span class="fs-3 fw-bold text-success">
                                                ฿{{ number_format($priceAfterDiscount, 2) }}
                                            </span>
                                            <span class="badge bg-warning text-dark ms-2">
                                                <i class="fas fa-tag me-1"></i>
                                                ส่วนลด {{ $discount }}%
                                            </span>
                                        </div>
                                        <small class="text-white-50">ราคาปกติ / ราคาหลังหักส่วนลด ต่อคน</small>
                                    @else
                                        <span class="fs-3 fw-bold text-white">
                                            ฿{{ number_format($price, 2) }}
                                        </span>
                                        <small class="text-white-50">ต่อคน</small>
                                    @endif
                                </div>
                                
                                @if(!$promotion->isExpired())
                                    <a href="{{ route('user.promotion-bookings.create', ['promotion' => $promotion->id]) }}" 
                                       class="btn btn-light btn-lg mt-3 mt-md-0">
                                        <i class="fas fa-calendar-plus me-2"></i>
                                        จองเลย
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-lg mt-3 mt-md-0" disabled>
                                        <i class="fas fa-clock me-2"></i>
                                        หมดเวลาจอง
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- /ราคาปกติ/ราคาหลังหักส่วนลด/ส่วนลด -->
                </div>
            </div>
        </div>
        <div class="alert alert-info m-4">
            <h6 class="alert-heading">
                <i class="fas fa-info-circle me-2"></i>หมายเหตุ
            </h6>
            <ul class="mb-0">
                <li>กรุณาจองล่วงหน้าอย่างน้อย 1 วัน</li>
                <li>สามารถยกเลิกการจองได้ก่อนวันจัดกิจกรรม 2 วัน</li>
                <li>ต้องชำระเงินภายใน 24 ชั่วโมงหลังจากจอง</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.activity-details, .included-items {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
}
.promotion-image {
    width: 100%;
    max-width: 480px;
    min-height: 220px;
    max-height: 400px;
    object-fit: cover;
    display: block;
    margin-left: auto;
    margin-right: auto;
}
</style>
@endpush