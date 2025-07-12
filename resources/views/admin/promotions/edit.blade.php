@extends('admin.layouts.admin')

@section('title', 'แก้ไขกิจกรรม')


<link href="{{ asset('css/admin/promotions.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">แก้ไขกิจกรรม</h1>
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> กลับไปรายการ
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">ชื่อกิจกรรม</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title', $promotion->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">รายละเอียดย่อ</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="2">{{ old('description', $promotion->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="activity_details" class="form-label">รายละเอียดกิจกรรม</label>
                        <textarea class="form-control @error('activity_details') is-invalid @enderror" 
                                  id="activity_details" name="activity_details" rows="4">{{ old('activity_details', $promotion->activity_details) }}</textarea>
                        @error('activity_details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="max_participants" class="form-label">จำนวนผู้เข้าร่วมสูงสุด</label>
                        <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                               id="max_participants" name="max_participants" 
                               value="{{ old('max_participants', $promotion->max_participants) }}" min="1" required>
                        @error('max_participants')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="price_per_person" class="form-label">ราคาต่อคน</label>
                        <div class="input-group">
                            <span class="input-group-text">฿</span>
                            <input type="number" class="form-control @error('price_per_person') is-invalid @enderror" 
                                   id="price_per_person" name="price_per_person" 
                                   value="{{ old('price_per_person', $promotion->price_per_person) }}" min="0" step="0.01" required>
                        </div>
                        @error('price_per_person')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
     <div class="col-md-6 mb-3">
    <label for="discount" class="form-label">ส่วนลด (%)</label>
    <div class="input-group">
        <input type="number" class="form-control @error('discount') is-invalid @enderror"
               id="discount" name="discount"
               value="{{ old('discount', isset($promotion) ? (int)$promotion->discount : 0) }}"
               min="0" max="100" step="1" placeholder="0">
        <span class="input-group-text">%</span>
    </div>
    @error('discount')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">สถานที่จัดกิจกรรม</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" 
                               id="location" name="location" 
                               value="{{ old('location', $promotion->location) }}" required>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="starts_at" class="form-label">วันและเวลาเริ่มกิจกรรม</label>
                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" 
                               id="starts_at" name="starts_at" 
                               value="{{ old('starts_at', $promotion->starts_at->format('Y-m-d\TH:i')) }}" required>
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="ends_at" class="form-label">วันและเวลาสิ้นสุดกิจกรรม</label>
                        <input type="datetime-local" class="form-control @error('ends_at') is-invalid @enderror" 
                               id="ends_at" name="ends_at" 
                               value="{{ old('ends_at', $promotion->ends_at->format('Y-m-d\TH:i')) }}" required>
                        @error('ends_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="included_items" class="form-label">สิ่งที่ผู้เข้าร่วมจะได้รับ</label>
                    <textarea class="form-control @error('included_items') is-invalid @enderror" 
                              id="included_items" name="included_items" rows="3">{{ old('included_items', $promotion->included_items) }}</textarea>
                    @error('included_items')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">รูปภาพกิจกรรม</label>
                    @if($promotion->image)
                        <div class="mb-2">
                            <img src="{{ $promotion->image }}" 
                                class="img-thumbnail" style="max-height: 200px" alt="{{ $promotion->title }}">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                        id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยนรูปภาพ</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">สถานะ</label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                            id="status" name="status" required>
                        <option value="active" {{ old('status', $promotion->status) == 'active' ? 'selected' : '' }}>เปิดรับจอง</option>
                        <option value="inactive" {{ old('status', $promotion->status) == 'inactive' ? 'selected' : '' }}>ปิดรับจอง</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
            value="1" {{ old('is_featured', isset($promotion) ? $promotion->is_featured : false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_featured">
            โปรโมชั่นแนะนำ
        </label>
    </div>
</div>

                <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // ป้องกัน input number ใส่ + - e . และอักขระแปลก
    document.querySelectorAll('input[type="number"]').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            // อนุญาตเฉพาะตัวเลข, backspace, delete, tab, arrow, home, end
            if (
                // ตัวเลขบนแป้นหลัก
                (e.keyCode >= 48 && e.keyCode <= 57 && !e.shiftKey) ||
                // ตัวเลขบน numpad
                (e.keyCode >= 96 && e.keyCode <= 105) ||
                // ปุ่มควบคุม
                [8,9,13,27,46,37,38,39,40].includes(e.keyCode)
            ) {
                // อนุญาต
            } else {
                e.preventDefault();
            }
        });
        // ป้องกัน paste อะไรที่ไม่ใช่ตัวเลข
        el.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        var discountInput = document.getElementById('discount');
    if (discountInput) {
        discountInput.addEventListener('blur', function() {
            if (this.value === '' || isNaN(this.value)) {
                this.value = 0;
            }
        });
    }
    });

    // ป้องกัน input date/datetime-local พิมพ์เอง ต้องเลือกจาก picker เท่านั้น
    document.querySelectorAll('input[type="date"],input[type="datetime-local"]').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            e.preventDefault();
        });
        el.addEventListener('paste', function(e) {
            e.preventDefault();
        });
    });
});
$(document).ready(function() {
    $('#activity_details').summernote({
        height: 200,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});
</script>
@endpush