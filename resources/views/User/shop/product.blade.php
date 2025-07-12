{{-- filepath: resources/views/User/shop/product.blade.php --}}
@extends('User.layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('user.shop.index') }}">ร้านค้า</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $product->name }}
            </li>
        </ol>
    </nav>

    <div class="row">
        <!-- รูปสินค้า -->
        <div class="col-md-6 mb-4">
            @if($product->image)
                <img src="{{ $product->image}}" 
                     alt="{{ $product->name }}" 
                     class="img-fluid rounded shadow product-image">
            @else
                <img src="{{ asset('images/no-image.png') }}" 
                     alt="No Image" 
                     class="img-fluid rounded shadow product-image">
            @endif
        </div>

        <!-- รายละเอียดสินค้า -->
        <div class="col-md-6">
            <h1 class="mb-3">{{ $product->name }}</h1>
            
            <div class="d-flex align-items-center mb-3">
                @if($product->discount_percent > 0)
                    <span class="h4 text-danger text-decoration-line-through mb-0">
                        ฿{{ number_format($product->price, 2) }}
                    </span>
                    <span class="h3 text-success ms-3 mb-0">
                        ฿{{ number_format($product->price * (1 - $product->discount_percent/100), 2) }}
                    </span>
                    <span class="badge bg-success ms-3">-{{ $product->discount_percent }}%</span>
                @else
                    <span class="h3 text-primary mb-0">
                        ฿{{ number_format($product->price, 2) }}
                    </span>
                @endif
                @if($product->stock > 0)
                    <span class="badge bg-success ms-3">มีสินค้า</span>
                @else
                    <span class="badge bg-danger ms-3">สินค้าหมด</span>
                @endif
            </div>

            <div class="mb-4">
                {!! $product->description !!}
            </div>

            @if($product->stock > 0)
                <form action="{{ route('user.shop.add-to-cart', $product) }}" method="POST">
                    @csrf
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto">
                            <label for="quantity" class="col-form-label">จำนวน:</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   class="form-control @error('quantity') is-invalid @enderror" 
                                   value="1" 
                                   min="1" 
                                   max="{{ $product->stock }}">
                        </div>
                        <div class="col-auto">
                            <span class="form-text">
                                (สินค้าคงเหลือ: {{ $product->stock }} ชิ้น)
                            </span>
                        </div>
                    </div>

                    @error('quantity')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-cart-plus me-2"></i>เพิ่มลงตะกร้า
                    </button>
                </form>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    สินค้าหมด กรุณาติดต่อเจ้าหน้าที่เพื่อสอบถามข้อมูลเพิ่มเติม
                </div>
            @endif

            <!-- ข้อมูลเพิ่มเติม -->
            <div class="mt-4">
                <h4>ข้อมูลสินค้า</h4>
                <table class="table table-bordered mt-2">
                    <tr>
                        <th class="bg-light">รหัสสินค้า</th>
                        <td>{{ $product->code ?? 'ไม่ระบุ' }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">หมวดหมู่</th>
                        <td>{{ $product->category->name ?? 'ไม่ระบุ' }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">แท็ก</th>
                        <td>
                            @if(method_exists($product, 'tags'))
                                @forelse($product->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                                @empty
                                    ไม่มีแท็ก
                                @endforelse
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    @if(isset($relatedProducts) && $relatedProducts->isNotEmpty())
        <div class="mt-5">
            <h3>สินค้าที่เกี่ยวข้อง</h3>
            <div class="row">
                @foreach($relatedProducts as $related)
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="{{ $related->image }}" 
                                 class="card-img-top" 
                                 alt="{{ $related->name }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $related->name }}</h5>
                                <p class="card-text text-primary">
                                    @if($related->discount_percent > 0)
                                        <span class="text-danger text-decoration-line-through">
                                            ฿{{ number_format($related->price, 2) }}
                                        </span>
                                        <span class="text-success ms-2">
                                            ฿{{ number_format($related->price * (1 - $related->discount_percent/100), 2) }}
                                        </span>
                                        <span class="badge bg-success ms-1">
                                            -{{ $related->discount_percent }}%
                                        </span>
                                    @else
                                        ฿{{ number_format($related->price, 2) }}
                                    @endif
                                </p>
                                <a href="{{ route('user.shop.product', $related) }}" 
                                   class="btn btn-outline-primary">
                                    ดูรายละเอียด
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .product-image {
        max-height: 500px;
        object-fit: contain;
    }
</style>
@endpush