@extends('User.layouts.app')

@section('title', 'ประวัติคำสั่งซื้อ')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ประวัติคำสั่งซื้อ</h2>
        <a href="{{ route('user.shop.index') }}" class="btn btn-primary">
            <i class="fas fa-shopping-cart me-2"></i>ไปยังร้านค้า
        </a>
    </div>

    @if($orders->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>ยังไม่มีประวัติการสั่งซื้อ 
            <a href="{{ route('user.shop.index') }}" class="alert-link">เริ่มช้อปปิ้งเลย!</a>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสคำสั่งซื้อ</th>
                                <th>วันที่สั่งซื้อ</th>
                                <th>จำนวนสินค้า</th>
                                <th>ยอดรวม</th>
                                <th>สถานะการชำระเงิน</th>
                                <th>สถานะคำสั่งซื้อ</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->order_code }}</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $order->items_count }} รายการ</td>
                                <td>฿{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status_color }}">
        {{ $order->payment_status === 'paid' ? 'ชำระแล้ว' : 'รอชำระเงิน' }}
    </span>
                                </td>
                                <td>
                                   <span class="badge bg-{{ $order->status_color }}">
        {{ $order->status_text }}
    </span>
                                </td>
                                <td>
                                    <a href="{{ route('user.orders.show', $order) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($order->payment_status === 'pending')
                                        <a href="{{ route('user.orders.show', $order) }}" 
                                           class="btn btn-sm btn-success" 
                                           title="แจ้งชำระเงิน">
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