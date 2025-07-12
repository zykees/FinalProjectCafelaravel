@extends('User.layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">ยืนยันการสั่งซื้อ</h2>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('user.shop.process-checkout') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ข้อมูลการจัดส่ง</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_name" class="form-label required">ชื่อผู้รับ</label>
                            <input type="text" 
                                   class="form-control @error('shipping_name') is-invalid @enderror" 
                                   id="shipping_name" 
                                   name="shipping_name" 
                                   value="{{ old('shipping_name', auth()->user()->name) }}"
                                   required>
                            @error('shipping_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address" class="form-label required">ที่อยู่จัดส่ง</label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                      id="shipping_address" 
                                      name="shipping_address" 
                                      rows="3" 
                                      required>{{ old('shipping_address', auth()->user()->address) }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shipping_phone" class="form-label required">เบอร์โทรศัพท์</label>
                            <input type="text" 
                                   class="form-control @error('shipping_phone') is-invalid @enderror" 
                                   id="shipping_phone" 
                                   name="shipping_phone"
                                   value="{{ old('shipping_phone', auth()->user()->phone) }}"
                                   required>
                            @error('shipping_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">วิธีการชำระเงิน</label>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="payment_method" 
                                       id="bank_transfer" 
                                       value="bank_transfer" 
                                       checked>
                                <label class="form-check-label" for="bank_transfer">
                                    โอนเงินผ่านธนาคาร
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- สรุปรายการสั่งซื้อ -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">สรุปรายการสั่งซื้อ</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            @php
                                $cartTotal = 0;
                                $cartDiscount = 0;
                                $cartFinalTotal = 0;
                            @endphp
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>สินค้า</th>
                                        <th class="text-end">ราคาปกติ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach(Cart::getContent() as $item)
                                    @php
                                        $discountPercent = $item->attributes->discount_percent ?? 0;
                                        $originalPrice = $item->price;
                                        $discountedPrice = $discountPercent > 0
                                            ? round($originalPrice * (1 - $discountPercent/100), 2)
                                            : $originalPrice;
                                        $itemTotal = $discountedPrice * $item->quantity;
                                        $itemDiscount = ($originalPrice - $discountedPrice) * $item->quantity;
                                        $cartTotal += $originalPrice * $item->quantity;
                                        $cartDiscount += $itemDiscount;
                                        $cartFinalTotal += $itemTotal;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $item->name }} x {{ $item->quantity }}
                                            @if($discountPercent > 0)
                                                <br>
                                                <span class="badge bg-success">-{{ $discountPercent }}%</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($discountPercent > 0)
                                                <span class="text-decoration-line-through text-danger">
                                                    ฿{{ number_format($originalPrice, 2) }}
                                                </span>
                                                <br>
                                                <span class="fw-bold text-success">
                                                    ฿{{ number_format($discountedPrice, 2) }}
                                                </span>
                                            @else
                                                <span class="fw-bold text-primary">
                                                    ฿{{ number_format($originalPrice, 2) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="border-top text-end"><strong>ราคารวมปกติ:</strong></td>
                                        <td class="text-end border-top"><strong>฿{{ number_format($cartTotal, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-end text-danger"><strong>ส่วนลดรวม:</strong></td>
                                        <td class="text-end text-danger"><strong>-฿{{ number_format($cartDiscount, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-end text-success"><strong>ยอดสุทธิหลังหักส่วนลด:</strong></td>
                                        <td class="text-end text-success"><strong>฿{{ number_format($cartFinalTotal, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            ยืนยันการสั่งซื้อ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection