@extends('User.layouts.app')

@section('title', 'ร้านค้า')

@section('content')
<div class="container py-4">
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <form action="{{ route('user.shop.index') }}" method="GET" id="categoryFilterForm">
                <div class="input-group">
                    <input type="text" name="category_name" class="form-control"
                        placeholder="เลือกหรือพิมพ์หมวดหมู่" value="{{ request('category_name') }}"
                        list="categoryList" onchange="document.getElementById('categoryFilterForm').submit()">
                    <datalist id="categoryList">
                        @foreach($categoryNames as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>
            </form>
        </div>
        <div class="col-md-8 mb-2">
            <form action="{{ route('user.shop.index') }}" method="GET">
                <div class="input-group">
                    <input type="hidden" name="category_name" value="{{ request('category_name') }}">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาสินค้า..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($products->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>ไม่พบสินค้าที่ค้นหา
        </div>
    @else
        <div class="row">
            @foreach($products as $product)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 d-flex align-items-stretch">
                    <div class="card h-100 w-100">
                        @if($product->image)
                            <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($product->description, 100) }}</p>
                            <p class="card-text mb-2">
                                @if($product->discount_percent > 0)
                                    <span class="h6 text-danger text-decoration-line-through">
                                        ฿{{ number_format($product->price, 2) }}
                                    </span>
                                    <span class="h6 text-success ms-2">
                                        ฿{{ number_format($product->price * (1 - $product->discount_percent/100), 2) }}
                                    </span>
                                    <span class="badge bg-success ms-1">
                                        -{{ $product->discount_percent }}%
                                    </span>
                                @else
                                    <span class="h6 text-primary">
                                        ฿{{ number_format($product->price, 2) }}
                                    </span>
                                @endif
                            </p>
                            <small class="text-muted mb-2">
                                หมวดหมู่: {{ $product->category_name ?? '-' }}<br>
                                สินค้าคงเหลือ:
                                @if($product->stock > 0)
                                    <span class="text-success">{{ $product->stock }} ชิ้น</span>
                                @else
                                    <span class="text-danger">สินค้าหมด</span>
                                @endif
                            </small>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('user.shop.product', $product) }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-eye me-1"></i> ดูรายละเอียด
                                </a>
                                <form action="{{ route('user.shop.add-to-cart', $product) }}" method="POST" class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus me-2"></i>
                                        @if($product->stock > 0)
                                            เพิ่มลงตะกร้า
                                        @else
                                            สินค้าหมด
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection