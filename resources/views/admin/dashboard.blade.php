@extends('admin.layouts.admin')

@section('title', 'แดชบอร์ด')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">แดชบอร์ด</h1>
    </div>

    <!-- Stats Cards Row -->
    <div class="row">
        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">จำนวนผู้ใช้ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">จำนวนออเดอร์ทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">จำนวนการจองโปรโมชั่น</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBookings ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">จำนวนสินค้า</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coffee fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">ออเดอร์ล่าสุด</h6>
                    @if(isset($recentOrders) && count($recentOrders))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">
                            ดูทั้งหมด
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>รหัสออเดอร์</th>
                                    <th>ลูกค้า</th>
                                    <th>ยอดรวม</th>
                                    <th>สถานะ</th>
                                    <th>วันที่</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders ?? [] as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ optional($order->user)->name ?? 'ไม่ระบุ' }}</td>
                                        <td>฿{{ number_format($order->total_amount ?? 0, 2) }}</td>
                                        <td>
                                            @php
                                                $statusColor = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ][$order->status ?? 'pending'] ?? 'secondary';
                                                $statusText = [
                                                    'pending' => 'รอดำเนินการ',
                                                    'processing' => 'กำลังดำเนินการ',
                                                    'completed' => 'เสร็จสิ้น',
                                                    'cancelled' => 'ยกเลิก'
                                                ][$order->status ?? 'pending'] ?? 'ไม่ระบุ';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td>{{ optional($order->created_at)->format('Y-m-d H:i') ?? 'ไม่ระบุ' }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> ดู
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">ไม่พบออเดอร์</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">การจองโปรโมชั่นล่าสุด</h6>
                    @if(isset($recentBookings) && count($recentBookings))
                        <a href="{{ route('admin.promotion-bookings.index') }}" class="btn btn-sm btn-primary">
                            ดูทั้งหมด
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>รหัส</th>
                                    <th>ลูกค้า</th>
                                    <th>วันที่จอง</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBookings ?? [] as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ optional($booking->user)->name ?? 'ไม่ระบุ' }}</td>
                                        <td>{{ optional($booking->created_at)->format('Y-m-d H:i') ?? 'ไม่ระบุ' }}</td>
                                        <td>
                                            @php
                                                $bookingColor = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'success',
                                                    'cancelled' => 'danger'
                                                ][$booking->status ?? 'pending'] ?? 'secondary';
                                                $bookingText = [
                                                    'pending' => 'รอดำเนินการ',
                                                    'confirmed' => 'ยืนยันแล้ว',
                                                    'cancelled' => 'ยกเลิก'
                                                ][$booking->status ?? 'pending'] ?? 'ไม่ระบุ';
                                            @endphp
                                            <span class="badge bg-{{ $bookingColor }}">
                                                {{ $bookingText }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">ไม่พบการจอง</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection