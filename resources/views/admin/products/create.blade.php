@extends('admin.layouts.admin')

@section('title', 'เพิ่มสินค้าใหม่')

<link href="{{ asset('css/admin/products.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">เพิ่มสินค้าใหม่</h1>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> กลับไปหน้ารายการ
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label required">ชื่อสินค้า</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required
                               placeholder="กรุณาระบุชื่อสินค้า">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category_name" class="form-label required">หมวดหมู่</label>
                        <input type="text" class="form-control @error('category_name') is-invalid @enderror"
                               id="category_name" name="category_name" value="{{ old('category_name') }}"
                               list="categoryList"
                               placeholder="กรอกหรือเลือกหมวดหมู่ เช่น เครื่องดื่ม, ขนม ฯลฯ" required>
                        <datalist id="categoryList">
                            @foreach($categoryNames as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                        @error('category_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">สามารถพิมพ์หมวดหมู่ใหม่ หรือเลือกจากที่เคยใช้</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="discount_percent" class="form-label">ส่วนลด (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('discount_percent') is-invalid @enderror"
                                   id="discount_percent" name="discount_percent"
                                   value="{{ old('discount_percent', 0) }}"
                                   min="0" max="100" step="1" placeholder="0">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('discount_percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label required">ราคา</label>
                        <div class="input-group">
                            <span class="input-group-text">฿</span>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price') }}" 
                                   step="0.01" min="0" required
                                   placeholder="0.00">
                        </div>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stock" class="form-label required">จำนวนในคลัง</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                               id="stock" name="stock" value="{{ old('stock', 0) }}" 
                               min="0" required>
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="minimum_stock" class="form-label">จำนวนขั้นต่ำ</label>
                        <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" 
                               id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', 1) }}" 
                               min="1">
                        @error('minimum_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label required">สถานะ</label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>
                                พร้อมขาย
                            </option>
                            <option value="unavailable" {{ old('status') == 'unavailable' ? 'selected' : '' }}>
                                ไม่พร้อมขาย
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">ตัวเลือกเพิ่มเติม</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" 
                                   id="featured" name="featured" value="1" 
                                   {{ old('featured', $product->featured ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">
                                แสดงในสินค้าแนะนำ
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">รายละเอียดสินค้า</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4"
                              placeholder="อธิบายรายละเอียดสินค้า...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">รูปภาพสินค้า</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                           id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">
                        รองรับไฟล์: JPG, PNG, GIF ขนาดไม่เกิน 2MB
                    </small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div id="imagePreview" class="mt-2 d-none">
                        <img src="" alt="ตัวอย่างรูปภาพ" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-secondary" id="resetBtn">
                        <i class="fas fa-redo"></i> ล้างฟอร์ม
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> บันทึกสินค้า
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .required:after {
        content: ' *';
        color: red;
    }
    .btn .spinner-border {
        margin-right: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // เฉพาะ input ที่ต้องเป็นตัวเลขเท่านั้น
    const onlyNumberFields = [
        'price', 'discount_percent', 'stock', 'minimum_stock'
    ];
    onlyNumberFields.forEach(function(fieldId) {
        const el = document.getElementById(fieldId);
        if (el) {
            el.addEventListener('keydown', function(e) {
                if (
                    (e.keyCode >= 48 && e.keyCode <= 57 && !e.shiftKey) ||
                    (e.keyCode >= 96 && e.keyCode <= 105) ||
                    [8,9,13,27,46,37,38,39,40,110,190].includes(e.keyCode)
                ) {
                } else {
                    e.preventDefault();
                }
            });
            el.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        }
    });

    // Image preview functionality
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = function() {
                imagePreview.querySelector('img').src = reader.result;
                imagePreview.classList.remove('d-none');
            }
            reader.readAsDataURL(file);
        } else {
            imagePreview.classList.add('d-none');
        }
    });

    // Product form validation
    const productForm = document.getElementById('productForm');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');

    productForm.addEventListener('submit', function(e) {
        let isValid = true;
        this.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'กรุณาตรวจสอบข้อมูล',
                text: 'กรุณากรอกข้อมูลให้ครบถ้วน'
            });
        }
    });

    resetBtn.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'ยืนยันการล้างฟอร์ม',
            text: 'คุณแน่ใจหรือไม่ที่จะล้างข้อมูลทั้งหมด?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ล้างข้อมูล',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                productForm.reset();
                imagePreview.classList.add('d-none');
                productForm.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            }
        });
    });
});
</script>
@endpush