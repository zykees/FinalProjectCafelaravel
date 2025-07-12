@extends('admin.layouts.admin')

@section('title', 'รายละเอียดผู้ใช้')

@push('styles')
<link href="{{ asset('css/admin/users.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายละเอียดผู้ใช้</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> แก้ไข
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> กลับหน้ารายชื่อ
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- User Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ข้อมูลผู้ใช้</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 150px;">ชื่อ</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>อีเมล</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>เบอร์โทร</th>
                            <td>{{ $user->phone ?? 'ไม่มีข้อมูล' }}</td>
                        </tr>
                        <tr>
                            <th>สถานะ</th>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                    {{ $user->status === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>ที่อยู่</th>
                            <td>{{ $user->address ?? 'ไม่มีข้อมูล' }}</td>
                        </tr>
                        <tr>
                            <th>วันที่สมัคร</th>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Order History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ออเดอร์ล่าสุด</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>รหัสออเดอร์</th>
                                    <th>ยอดรวม</th>
                                    <th>สถานะ</th>
                                    <th>วันที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->orders()->latest()->take(5)->get() as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>฿{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status_color }}">
                                                {{ 
                                                    [
                                                        'pending' => 'รอดำเนินการ',
                                                        'processing' => 'กำลังดำเนินการ',
                                                        'completed' => 'เสร็จสิ้น',
                                                        'cancelled' => 'ยกเลิก'
                                                    ][$order->status] ?? $order->status
                                                }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">ไม่พบออเดอร์</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">การจองโปรโมชั่นล่าสุด</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>รหัส</th>
                                    <th>วันที่จอง</th>
                                    <th>จำนวนที่นั่ง</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                               @forelse($user->promotionBookings->take(5) as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->created_at->format('Y-m-d') }}</td>
                                        <td>{{ $booking->number_of_participants ?? $booking->seats ?? 1 }}</td>
                                        <td>
                                            <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ $booking->status === 'confirmed' ? 'ยืนยันแล้ว' : ($booking->status === 'pending' ? 'รอดำเนินการ' : 'ยกเลิก') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">ไม่พบการจองโปรโมชั่น</td>
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