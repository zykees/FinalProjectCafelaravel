@extends('User.layouts.app')

@section('title', $promotion->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-3 py-2 rounded">
            <li class="breadcrumb-item"><a href="{{ route('user.promotions.index') }}">โปรโมชั่น</a></li>
            <li class="breadcrumb-item active">{{ $promotion->title }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="row g-0">
            @if($promotion->image)
                <div class="col-md-6">
                    <img src="{{ $promotion->image }}" 
                         class="img-fluid rounded-start w-100 h-100 object-fit-cover" 
                         alt="{{ $promotion->title }}" style="min-height:320px;object-fit:cover;">
                </div>
            @endif
            <div class="col-md-{{ $promotion->image ? '6' : '12' }}">
                <div class="card-body">
                    <h2 class="card-title mb-2">{{ $promotion->title }}</h2>
                    <p class="card-text text-muted mb-3">{{ $promotion->description }}</p>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i> รายละเอียดกิจกรรม</h5>
                        <div class="activity-details ps-2 border-start border-3 border-primary">
                            {!! $promotion->activity_details !!}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5 class="fw-bold mb-2"><i class="fas fa-map-marker-alt me-1"></i> สถานที่</h5>
                            <p class="mb-3">{{ $promotion->location }}</p>

                            <h5 class="fw-bold mb-2"><i class="fas fa-users me-1"></i> จำนวนที่รับได้</h5>
                            <p class="mb-0">
                                <span class="badge bg-info text-dark fs-6">
                                    {{ $promotion->getRemainingSlots() }} / {{ $promotion->max_participants }} คน
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-2"><i class="fas fa-calendar-alt me-1"></i> วันและเวลา</h5>
                            <p class="mb-0">
                                <span class="d-block"><strong>เริ่ม:</strong> {{ $promotion->starts_at->format('d/m/Y H:i') }}</span>
                                <span class="d-block"><strong>สิ้นสุด:</strong> {{ $promotion->ends_at->format('d/m/Y H:i') }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-2"><i class="fas fa-gift me-1"></i> สิ่งที่ผู้เข้าร่วมจะได้รับ</h5>
                        <div class="included-items ps-2 border-start border-3 border-success">
                            {!! $promotion->included_items !!}
                        </div>
                    </div>

                    <div class="card bg-light mb-4 border-0">
                        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1 text-primary">
                                    ฿{{ number_format($promotion->price_per_person, 2) }} <small class="text-muted">/ คน</small>
                                </h4>
                                @if($promotion->discount > 0)
                                    <div class="text-success fw-bold">
                                        <i class="fas fa-tag"></i> ส่วนลด {{ $promotion->discount }}%
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 mt-md-0">
                                @if(!$promotion->isExpired())
                                    <a href="{{ route('user.promotion-bookings.create', ['promotion' => $promotion->id]) }}" 
                                       class="btn btn-primary btn-lg px-4">
                                        <i class="fas fa-calendar-plus"></i> จองเลย
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-lg px-4" disabled>
                                        <i class="fas fa-clock"></i> หมดเวลาจอง
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($promotion->isExpired())
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            กิจกรรมนี้ไม่สามารถจองได้แล้ว เนื่องจาก
                            @if($promotion->ends_at->isPast())
                                หมดเวลาจอง
                            @elseif($promotion->status === 'inactive')
                                ปิดรับจอง
                            @else
                                จำนวนผู้เข้าร่วมเต็มแล้ว
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection