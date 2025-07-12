@extends('User.layouts.app')

@section('title', 'จองกิจกรรม - ' . $promotion->title)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">หน้าหลัก</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.promotions.index') }}">โปรโมชั่น</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.promotions.show', $promotion) }}">{{ $promotion->title }}</a></li>
            <li class="breadcrumb-item active">จองกิจกรรม</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">จองกิจกรรม</h3>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('user.promotion-bookings.store', $promotion) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="number_of_participants" class="form-label">จำนวนผู้เข้าร่วม</label>
                            <input type="number" 
                                   class="form-control @error('number_of_participants') is-invalid @enderror"
                                   id="number_of_participants" 
                                   name="number_of_participants"
                                   min="1"
                                   max="{{ $promotion->getRemainingSlots() }}"
                                   value="{{ old('number_of_participants', 1) }}"
                                   required>
                            @error('number_of_participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-users"></i> เหลือที่นั่งว่าง {{ $promotion->getRemainingSlots() }} ที่นั่ง
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="activity_date" class="form-label">วันที่ต้องการเข้าร่วม</label>
                            <input type="date" 
                                   class="form-control @error('activity_date') is-invalid @enderror"
                                   id="activity_date" 
                                   name="activity_date"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   max="{{ $promotion->ends_at->format('Y-m-d') }}"
                                   value="{{ old('activity_date') }}"
                                   required>
                            @error('activity_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> สามารถจองล่วงหน้าได้จนถึง {{ $promotion->ends_at->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="activity_time" class="form-label">เวลาที่ต้องการเข้าร่วม</label>
                            <input type="time" 
                                   class="form-control @error('activity_time') is-invalid @enderror"
                                   id="activity_time" 
                                   name="activity_time"
                                   min="{{ $promotion->starts_at->format('H:i') }}"
                                   max="{{ $promotion->ends_at->format('H:i') }}"
                                   value="{{ old('activity_time', $promotion->starts_at->format('H:i')) }}"
                                   required>
                            @error('activity_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-clock"></i> เวลาทำการ {{ $promotion->starts_at->format('H:i') }} - {{ $promotion->ends_at->format('H:i') }} น.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="note" class="form-label">หมายเหตุเพิ่มเติม (ถ้ามี)</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      id="note" 
                                      name="note" 
                                      rows="3"
                                      placeholder="เช่น ความต้องการพิเศษ หรือข้อควรระวัง">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-calendar-check me-2"></i>ยืนยันการจอง
                            </button>
                            <a href="{{ route('user.promotions.show', $promotion) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>ย้อนกลับ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">สรุปการจอง</h5>
                    <hr>
                    <div class="mb-3">
                        <h6>{{ $promotion->title }}</h6>
                        <p class="text-muted small mb-0">{{ $promotion->description }}</p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>ราคาปกติ/คน</span>
                            <span class="text-decoration-line-through text-danger" id="normalPrice">
                                ฿{{ number_format($promotion->price_per_person, 2) }}
                            </span>
                        </div>
                        @if($promotion->discount > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>ราคาหลังหักส่วนลด/คน</span>
                            <span class="text-success fw-bold" id="discountedPrice">
                                ฿{{ number_format($promotion->price_per_person * (1 - $promotion->discount/100), 2) }}
                            </span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>จำนวนผู้เข้าร่วม</span>
                            <span id="participantCount">1 คน</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>ยอดรวม</span>
                            <span id="totalPrice">฿{{ number_format($promotion->price_per_person, 2) }}</span>
                        </div>
                        @if($promotion->discount > 0)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>ส่วนลด</span>
                                <span class="text-success" id="discountAmount">-฿0.00</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <strong>ราคาสุทธิ</strong>
                            <strong class="text-primary h4 mb-0" id="finalPrice">
                                ฿{{ number_format($promotion->price_per_person * (1 - $promotion->discount/100), 2) }}
                            </strong>
                        </div>
                    </div>
                    <hr>
                    <div class="small">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>{{ $promotion->location }}
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-calendar me-2"></i>{{ $promotion->starts_at->format('d/m/Y') }}
                        </div>
                        <div>
                            <i class="fas fa-clock me-2"></i>{{ $promotion->starts_at->format('H:i') }} - {{ $promotion->ends_at->format('H:i') }} น.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>ข้อควรทราบ
                    </h6>
                    <ul class="small mb-0">
                        <li>ต้องชำระเงินภายใน 24 ชั่วโมงหลังจากจอง</li>
                        <li>สามารถยกเลิกการจองได้ก่อนวันจัดกิจกรรม 2 วัน</li>
                        <li>กรุณามาถึงสถานที่ก่อนเวลาเริ่มกิจกรรม 15 นาที</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const formatter = new Intl.NumberFormat('th-TH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    const pricePerPerson = {{ $promotion->price_per_person }};
    const discount = {{ $promotion->discount }};
    const discountedPricePerPerson = pricePerPerson * (1 - discount/100);

    function updateSummary() {
        const participants = parseInt($('#number_of_participants').val()) || 0;

        // ราคาปกติ/คน
        $('#normalPrice').text('฿' + formatter.format(pricePerPerson));

        // ราคาหลังหักส่วนลด/คน
        if(discount > 0) {
            $('#discountedPrice').text('฿' + formatter.format(discountedPricePerPerson));
        }

        // จำนวนผู้เข้าร่วม
        $('#participantCount').text(participants + ' คน');

        // ยอดรวม (ราคาปกติ x จำนวน)
        const totalPrice = participants * pricePerPerson;
        $('#totalPrice').text('฿' + formatter.format(totalPrice));

        // ส่วนลด
        let discountAmount = 0;
        if(discount > 0) {
            discountAmount = totalPrice * discount / 100;
            $('#discountAmount').text('-฿' + formatter.format(discountAmount));
        }

        // ราคาสุทธิ
        const finalPrice = totalPrice - discountAmount;
        $('#finalPrice').text('฿' + formatter.format(finalPrice));
    }

    $('#number_of_participants').on('change keyup', updateSummary);

    // เรียกครั้งแรก
    updateSummary();
});
</script>
@endpush