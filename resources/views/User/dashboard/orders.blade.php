@extends('User.layouts.app')

@section('title', 'คำสั่งซื้อของฉัน')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>คำสั่งซื้อของฉัน</h2>
        <a href="{{ route('user.shop.index') }}" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i> ไปยังร้านค้า
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="alert alert-info">
            ยังไม่มีประวัติการสั่งซื้อ <a href="{{ route('user.shop.index') }}">ช้อปเลย</a>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัสคำสั่งซื้อ</th>
                                <th>วันที่สั่งซื้อ</th>
                                <th>จำนวนสินค้า</th>
                                <th>ยอดรวม</th>
                                <th>วิธีการชำระเงิน</th>
                                <th>สถานะ</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->items_count }} รายการ</td>
                                <td>฿{{ number_format($order->total_amount, 2) }}</td>
                                <td>{{ $order->payment_method_text }}</td>
                                <td>{!! $order->status_badge !!}</td>
                                <td>
                                    <a href="{{ route('user.orders.show', $order) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->status === 'pending' && $order->payment_method === 'bank_transfer')
                                        <a href="{{ route('user.orders.upload-payment', $order) }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-upload"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection