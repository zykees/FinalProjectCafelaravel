@extends('admin.layouts.admin')

@section('title', 'รายละเอียดกิจกรรม')

<link href="{{ asset('css/admin/promotions.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายละเอียดกิจกรรม</h1>
        <div>
            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> แก้ไข
            </a>
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> กลับ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลกิจกรรม</h6>
                </div>
                <div class="card-body">
                @if($promotion->image)
                    <div class="mb-4">
                        <img src="{{ $promotion->image }}" 
                            class="promotion-image"
                            alt="{{ $promotion->title }}">
                    </div>
                @endif

                    <h4>{{ $promotion->title }}</h4>
                    <p class="text-muted">{{ $promotion->description }}</p>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>รายละเอียดกิจกรรม</h6>
                            <div class="border rounded p-3">
                                {!! $promotion->activity_details !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>สิ่งที่ผู้เข้าร่วมจะได้รับ</h6>
                            <div class="border rounded p-3">
                                {!! $promotion->included_items !!}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong>สถานที่:</strong> {{ $promotion->location }}</p>
                            <p><strong>วันที่จัด:</strong> {{ $promotion->starts_at->format('d/m/Y H:i') }}</p>
                            <p><strong>สิ้นสุด:</strong> {{ $promotion->ends_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>ราคาต่อคน:</strong> ฿{{ number_format($promotion->price_per_person, 2) }}</p>
                            <p><strong>ส่วนลด:</strong> {{ $promotion->discount > 0 ? (int)$promotion->discount . '%' : '-' }}</p>
                            <p>
                                <strong>สถานะ:</strong>
                                <span class="badge bg-{{ $promotion->status === 'active' ? 'success' : 'danger' }}">
                                    {{ $promotion->status === 'active' ? 'เปิดรับจอง' : 'ปิดรับจอง' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">สถิติการจอง</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h4>ผู้เข้าร่วม</h4>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ ($promotion->current_participants / $promotion->max_participants) * 100 }}%">
                            </div>
                        </div>
                        <small>{{ $promotion->current_participants }}/{{ $promotion->max_participants }} คน</small>
                    </div>

                    <div class="mb-4">
                        <h4>การจองทั้งหมด</h4>
                        <p class="h2">{{ $stats['total_bookings'] ?? 0 }}</p>
                    </div>

                    <div class="mb-4">
                        <h4>รายได้รวม</h4>
                        <p class="h2">฿{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            @if(isset($recentBookings) && $recentBookings->isNotEmpty())
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">การจองล่าสุด</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentBookings as $booking)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $booking->user->name }}</h6>
                                <small>{{ $booking->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $booking->number_of_participants }} คน</p>
                            <small>฿{{ number_format($booking->final_price, 2) }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection