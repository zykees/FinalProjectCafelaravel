{{-- filepath: c:\xampp\htdocs\projectcafelaravel10\resources\views\admin\orders\index.blade.php --}}
@php
    use App\Models\Order;
@endphp
@extends('admin.layouts.admin')

@section('title', 'จัดการออเดอร์')


<link href="{{ asset('css/admin/orders.css') }}" rel="stylesheet">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">จัดการออเดอร์</h1>
    </div>

    <!-- Statistics Cards (4 กล่องเท่ากันเหมือน products) -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">ออเดอร์ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">รอดำเนินการ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">เสร็จสิ้น</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ออเดอร์วันนี้</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today_orders'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <!-- Filters -->
        <div class="card-header py-3">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-auto">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">ทุกสถานะ</option>
                    <option value="{{ Order::STATUS_PENDING }}" {{ request('status') == Order::STATUS_PENDING ? 'selected' : '' }}>รอดำเนินการ</option>
                    <option value="{{ Order::STATUS_PROCESSING }}" {{ request('status') == Order::STATUS_PROCESSING ? 'selected' : '' }}>กำลังดำเนินการ</option>
                    <option value="{{ Order::STATUS_COMPLETED }}" {{ request('status') == Order::STATUS_COMPLETED ? 'selected' : '' }}>เสร็จสิ้น</option>
                    <option value="{{ Order::STATUS_CANCELLED }}" {{ request('status') == Order::STATUS_CANCELLED ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>

            <div class="col-auto">
                <select class="form-select" name="payment_status" onchange="this.form.submit()">
                    <option value="">สถานะการชำระเงิน</option>
                    <option value="{{ Order::PAYMENT_PENDING }}" {{ request('payment_status') == Order::PAYMENT_PENDING ? 'selected' : '' }}>รอชำระเงิน</option>
                    <option value="{{ Order::PAYMENT_PAID }}" {{ request('payment_status') == Order::PAYMENT_PAID ? 'selected' : '' }}>ชำระแล้ว</option>
                    <option value="{{ Order::PAYMENT_FAILED }}" {{ request('payment_status') == Order::PAYMENT_FAILED ? 'selected' : '' }}>ชำระไม่สำเร็จ</option>
                </select>
            </div>

            <div class="col-auto">
                <div class="input-group">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="วันที่เริ่มต้น">
                    <span class="input-group-text">ถึง</span>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="วันที่สิ้นสุด">
                </div>
            </div>

            <div class="col-auto">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="ค้นหา...">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="col-auto">
                <select class="form-select" name="sort" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>ล่าสุด</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>เก่าสุด</option>
                    <option value="total_desc" {{ request('sort') == 'total_desc' ? 'selected' : '' }}>ยอดรวมมาก-น้อย</option>
                    <option value="total_asc" {{ request('sort') == 'total_asc' ? 'selected' : '' }}>ยอดรวมน้อย-มาก</option>
                </select>
            </div>
        </form>
        </div>

       <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>รหัสออเดอร์</th>
                            <th>ลูกค้า</th>
                            <th>จำนวนสินค้า</th>
                            <th>ราคารวมปกติ</th>
                            <th>ส่วนลดรวม</th>
                            <th>ยอดสุทธิ</th>
                            <th>สถานะ</th>
                            <th>การชำระเงิน</th>
                            <th>วันที่</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_code }}</td>
                                <td>{{ $order->user->name }}</td>
                                <td>{{ $order->items->sum('quantity') }} รายการ</td>
                                <td>฿{{ number_format($order->calculated_subtotal, 2) }}</td>
                                <td>
                                    @if($order->calculated_discount > 0)
                                        ฿{{ number_format($order->calculated_discount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    ฿{{ number_format($order->calculated_total, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status_color }}">
                                        {{ __('orders.status.' . $order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status_color }}">
                                        {{ __('orders.payment_status.' . $order->payment_status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" 
                                           class="btn btn-sm btn-primary" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($order->canBeCancelled())
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="cancelOrder({{ $order->id }})" title="ยกเลิก">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">ไม่พบข้อมูลออเดอร์</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit on filter change (select)
    document.querySelectorAll('form select[name="status"], form select[name="payment_status"], form select[name="sort"]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Auto submit on date change
    document.querySelectorAll('form input[type="date"]').forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
});

    // Handle date range inputs
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    
    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            dateTo.min = this.value;
        });
        dateTo.addEventListener('change', function() {
            dateFrom.max = this.value;
        });
    }

function cancelOrder(orderId) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกออเดอร์นี้?')) {
        fetch(`/admin/orders/${orderId}/cancel`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการยกเลิกออเดอร์');
        });
    }
}
</script>
@endpush