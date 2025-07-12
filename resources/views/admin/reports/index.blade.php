@extends('admin.layouts.admin')

@section('title', 'แดชบอร์ดรายงาน')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">แดชบอร์ดรายงานภาพรวม</h1>
    </div>

    <!-- กราฟยอดขายและยอดจอง 30 วันล่าสุด -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>กราฟยอดขาย & ยอดจองโปรโมชั่น (30 วันล่าสุด)</h5>
                    <canvas id="dashboardChart" height="90"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- สรุปสถิติหลัก -->
    <div class="row">
        <!-- ยอดขายสุทธิ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                ยอดขายสุทธิ (30 วันล่าสุด)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ฿{{ number_format($stats['total_sales'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ยอดเงินจากการจองโปรโมชั่น -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ยอดเงินจากการจองโปรโมชั่น (30 วัน)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ฿{{ number_format($stats['total_booking_amount'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- จำนวนออเดอร์ -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                จำนวนออเดอร์ (30 วัน)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_orders'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- จำนวนการจองโปรโมชั่น -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                จำนวนการจองโปรโมชั่น (30 วัน)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_promotion_bookings'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- รายละเอียดเพิ่มเติม -->
    <div class="row">
        <!-- โปรโมชั่นที่เปิดให้จอง -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="fas fa-bullhorn me-2"></i>โปรโมชั่นที่เปิดให้จอง</h5>
                    <div class="display-5 mb-2 text-info">{{ number_format($stats['active_promotions'] ?? 0) }}</div>
                    <p class="mb-0">จำนวนโปรโมชั่นที่กำลังเปิดให้จองในระบบ</p>
                </div>
            </div>
        </div>
        <!-- โปรโมชั่นที่ถูกจองจริง -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="fas fa-check-circle me-2"></i>โปรโมชั่นที่มีการจอง</h5>
                    <div class="display-5 mb-2 text-success">{{ number_format($stats['booked_promotions'] ?? 0) }}</div>
                    <p class="mb-0">จำนวนโปรโมชั่นที่มีผู้จองอย่างน้อย 1 ครั้งใน 30 วันล่าสุด</p>
                </div>
            </div>
        </div>
        <!-- จำนวนลูกค้า -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="fas fa-users me-2"></i>จำนวนลูกค้าทั้งหมด</h5>
                    <div class="display-5 mb-2 text-primary">{{ number_format($stats['total_customers'] ?? 0) }}</div>
                    <p class="mb-0">จำนวนลูกค้าที่ลงทะเบียนในระบบ</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ลิงก์ไปยังรายงานย่อย -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">รายงานยอดขาย</h5>
                    <p class="card-text">ดูรายละเอียดยอดขาย สินค้าขายดี และแนวโน้มรายได้</p>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary w-100">
                        ดูรายงานยอดขาย
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">รายงานการจอง</h5>
                    <p class="card-text">วิเคราะห์รูปแบบการจอง ช่วงเวลายอดนิยม และอัตราการจอง</p>
                    <a href="{{ route('admin.reports.bookings') }}" class="btn btn-success w-100">
                        ดูรายงานการจอง
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h5 class="card-title">รายงานโปรโมชั่น</h5>
                    <p class="card-text">ติดตามประสิทธิภาพโปรโมชั่น การใช้งาน และส่วนลดที่ลูกค้าได้รับ</p>
                    <a href="{{ route('admin.reports.promotions') }}" class="btn btn-info w-100">
                        ดูรายงานโปรโมชั่น
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dashboardChart').getContext('2d');
    const dashboardChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chart['labels'] ?? []) !!},
            datasets: [
                {
                    label: 'ยอดขายสุทธิ',
                    data: {!! json_encode($chart['sales'] ?? []) !!},
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                },
                {
                    label: 'ยอดจองโปรโมชั่น',
                    data: {!! json_encode($chart['bookings'] ?? []) !!},
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '฿' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush