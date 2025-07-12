@extends('User.layouts.app')

@section('title', 'ตะกร้าสินค้า')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">ตะกร้าสินค้า</h2>

    @if(Cart::isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-shopping-cart me-2"></i>ตะกร้าสินค้าว่างเปล่า 
            <a href="{{ route('user.shop.index') }}" class="alert-link">เลือกซื้อสินค้า</a>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <form id="cartForm" action="{{ route('user.shop.update-cart') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>สินค้า</th>
                                    <th>ราคาปกติ</th>
                                    <th>ส่วนลด (%)</th>
                                    <th>ราคาหลังหักส่วนลด</th>
                                    <th>จำนวน</th>
                                    <th>คงเหลือ</th>
                                    <th>รวม</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $cartTotal = 0;
                                    $cartDiscount = 0;
                                    $cartFinalTotal = 0;
                                @endphp
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
                                    <tr data-item-id="{{ $item->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->attributes->image)
                                                    <img src="{{ $item->attributes->image }}" 
                                                         alt="{{ $item->name }}" 
                                                         class="img-thumbnail me-3" 
                                                         style="width: 64px;">
                                                @endif
                                                <span>{{ $item->name }}</span>
                                            </div>
                                        </td>
                                       <td class="item-price" data-price="{{ $originalPrice }}">
    <span class="{{ $discountPercent > 0 ? 'text-decoration-line-through text-danger' : '' }}">
        ฿{{ number_format($originalPrice, 2) }}
    </span>
</td>
<td class="item-discount" data-discount="{{ $discountPercent }}">
    @if($discountPercent > 0)
        <span class="badge bg-success">-{{ $discountPercent }}%</span>
    @else
        -
    @endif
</td>
                                        <td class="item-discounted-price">
    <span class="fw-bold text-success">
        ฿{{ number_format($discountedPrice, 2) }}
    </span>
</td>
                                        <td>
                                            <input type="number" 
                                                   name="quantity[{{ $item->id }}]" 
                                                   value="{{ $item->quantity }}" 
                                                   min="1"
                                                   max="{{ $item->attributes->stock }}"
                                                   class="form-control quantity-input" 
                                                   style="width: 80px;"
                                                   data-stock="{{ $item->attributes->stock }}"
                                                   >
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $item->attributes->stock }} ชิ้น
                                            </span>
                                        </td>
                                        <td class="item-total">
                                            <span class="fw-bold text-primary">
                                                ฿{{ number_format($itemTotal, 2) }}
                                            </span>
                                            @if($discountPercent > 0)
                                                <br>
                                                <small class="text-danger">
                                                    ประหยัดไป ฿{{ number_format($itemDiscount, 2) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="removeItem('{{ $item->id }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end"><strong>ราคารวมปกติ:</strong></td>
                                <td colspan="2"><strong id="cartTotal">฿{{ number_format($cartTotal, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end text-danger"><strong>ส่วนลดรวม:</strong></td>
                                <td colspan="2" class="text-danger"><strong id="cartDiscount">-฿{{ number_format($cartDiscount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end text-success"><strong>ยอดสุทธิหลังหักส่วนลด:</strong></td>
                                <td colspan="2" class="text-success"><strong id="cartFinalTotal">฿{{ number_format($cartFinalTotal, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('user.shop.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>เลือกซื้อสินค้าต่อ
                        </a>
                        <div>
                            <button type="submit" class="btn btn-success me-2">
                                <i class="fas fa-sync-alt me-2"></i>อัพเดทตะกร้า
                            </button>
                            <a href="{{ route('user.shop.checkout') }}" 
                               class="btn btn-primary {{ Cart::isEmpty() ? 'disabled' : '' }}">
                                <i class="fas fa-shopping-cart me-2"></i>ชำระเงิน
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input')) {
        checkStock(e.target);
    }
});

function checkStock(input) {
    const stock = parseInt(input.dataset.stock);
    const quantity = parseInt(input.value);
    const toast = new bootstrap.Toast(document.getElementById('stockToast'));
    
    if (quantity > stock) {
        input.value = stock; // Reset to maximum available stock
        document.querySelector('.toast-body').textContent = 
            `สินค้ามีไม่เพียงพอ (มีสินค้าคงเหลือ ${stock} ชิ้น)`;
        toast.show();
        updateItemTotal(input);
        return false;
    }
    
    updateItemTotal(input);
    return true;
}
function updateItemTotal(input) {
    const row = input.closest('tr');
    const price = parseFloat(row.querySelector('.item-price').dataset.price);
    const discountPercent = parseFloat(row.querySelector('.item-discount').dataset.discount);
    const quantity = parseInt(input.value);

    let discountedPrice = price;
    if (!isNaN(discountPercent) && discountPercent > 0) {
        discountedPrice = price * (1 - discountPercent / 100);
    }
    const itemTotal = discountedPrice * quantity;
    const itemDiscount = (price - discountedPrice) * quantity;

    // อัพเดท ราคารวม
    const saveText = discountPercent > 0 && itemDiscount > 0
        ? `<br><small class="text-danger">ประหยัดไป ฿${numberFormat(itemDiscount)}</small>`
        : '';
    row.querySelector('.item-total').innerHTML =
        `<span class="fw-bold text-primary">฿${numberFormat(itemTotal)}</span>${saveText}`;

    // อัพเดท ราคาปกติ + ขีดฆ่า
    const priceSpan = row.querySelector('.item-price span');
    priceSpan.textContent = '฿' + numberFormat(price);
    if (discountPercent > 0) {
        priceSpan.classList.add('text-decoration-line-through', 'text-danger');
    } else {
        priceSpan.classList.remove('text-decoration-line-through', 'text-danger');
    }

    // อัพเดท ราคาหลังหักส่วนลด
    const discountedPriceTd = row.querySelector('.item-discounted-price span.fw-bold');
    discountedPriceTd.textContent = '฿' + numberFormat(discountedPrice);

    // อัพเดท badge ส่วนลด
    const discountTd = row.querySelector('.item-discount');
    if (discountPercent > 0) {
        discountTd.innerHTML = `<span class="badge bg-success">-${discountPercent}%</span>`;
    } else {
        discountTd.innerHTML = '-';
    }
    

    // Update cart total
    updateCartTotal();
}

function updateCartTotal() {
    let cartTotal = 0;
    let cartDiscount = 0;
    let cartFinalTotal = 0;
    document.querySelectorAll('tbody tr').forEach(row => {
        const price = parseFloat(row.querySelector('.item-price') ? row.querySelector('.item-price').dataset.price : 0);
        const discountPercent = parseFloat(row.querySelector('.item-discount') ? row.querySelector('.item-discount').dataset.discount : 0);
        const quantity = parseInt(row.querySelector('input.quantity-input').value);

        let discountedPrice = price;
        if (!isNaN(discountPercent) && discountPercent > 0) {
            discountedPrice = price * (1 - discountPercent / 100);
        }
        const itemTotal = discountedPrice * quantity;
        const itemDiscount = (price - discountedPrice) * quantity;

        cartTotal += price * quantity;
        cartDiscount += itemDiscount;
        cartFinalTotal += itemTotal;
    });
    document.getElementById('cartTotal').textContent = '฿' + numberFormat(cartTotal);
    document.getElementById('cartDiscount').textContent = '-฿' + numberFormat(cartDiscount);
    document.getElementById('cartFinalTotal').textContent = '฿' + numberFormat(cartFinalTotal);
}

function numberFormat(number) {
    return number.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function removeItem(itemId) {
    if (confirm('ยืนยันการลบสินค้า?')) {
        fetch(`{{ url('user/shop/cart/remove') }}/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`tr[data-item-id="${itemId}"]`).remove();
                updateCartTotal();
                // แสดง Toast แจ้งเตือน
                var toastEl = document.getElementById('cartRemoveToast');
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
                // ถ้าตะกร้าว่าง reload หน้า
                if (document.querySelectorAll('tbody tr').length === 0) {
                    setTimeout(() => location.reload(), 1200);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการลบสินค้า');
        });
    }
}

// Auto-submit form when quantity changes
document.getElementById('cartForm').addEventListener('submit', function(e) {
    const inputs = this.querySelectorAll('.quantity-input');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!checkStock(input)) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        e.preventDefault();
    }
});

</script>
@endpush