@extends('admin.layouts.admin')

@section('title', 'รายงานการจองโปรโมชั่น')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายงานการจองโปรโมชั่น</h1>
        <div>
            <a href="{{ route('admin.reports.export', ['type' => 'bookings', 'format' => 'excel']) }}" class="btn btn-sm btn-success me-2">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'bookings', 'format' => 'pdf']) }}" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                การจองทั้งหมด</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalBookings ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                ยืนยันแล้ว</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $confirmedBookings ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                รอดำเนินการ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingBookings ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                ยกเลิกแล้ว</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $cancelledBookings ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">ตัวกรองข้อมูล</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.bookings') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">ช่วงวันที่</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">ถึง</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" name="status">
                        <option value="">ทั้งหมด</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>ยืนยันแล้ว</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="fas fa-search fa-sm"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table: รายวัน -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">สรุปจำนวนการจองรายวัน</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>จำนวนจองทั้งหมด</th>
                            <th>ยืนยันแล้ว</th>
                            <th>รอดำเนินการ</th>
                            <th>ยกเลิก</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailedData ?? [] as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['confirmed'] }}</td>
                                <td>{{ $row['pending'] }}</td>
                                <td>{{ $row['cancelled'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">ไม่พบข้อมูลการจอง</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bookings Table: รายละเอียดแต่ละรายการ -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">รายละเอียดการจองแต่ละรายการ</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="bookingsDetailTable">
                    <thead>
                        <tr>
                            <th>วันที่จอง</th>
                            <th>รหัสการจอง</th>
                            <th>กิจกรรม</th>
                            <th>ผู้จอง</th>
                            <th class="text-center">จำนวนที่นั่ง</th>
                            <th class="text-end">ยอดสุทธิ</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings ?? [] as $booking)
                            @php
                                $participants = $booking->number_of_participants ?? $booking->seats ?? 1;
                                $finalPrice = $booking->final_price ?? (
                                    $booking->promotion
                                        ? round($booking->promotion->price_per_person * (1 - ($booking->promotion->discount ?? 0)/100), 2) * $participants
                                        : 0
                                );
                            @endphp
                            <tr>
                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $booking->booking_code }}</td>
                                <td>{{ $booking->promotion->title ?? '-' }}</td>
                                <td>{{ $booking->user->name ?? '-' }}</td>
                                <td class="text-center">{{ $participants }}</td>
                                <td class="text-end">฿{{ number_format($finalPrice, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ $booking->status === 'confirmed' ? 'ยืนยันแล้ว' : ($booking->status === 'pending' ? 'รอดำเนินการ' : 'ยกเลิก') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">ไม่พบข้อมูลการจอง</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#bookingsTable').DataTable({
        "order": [[0, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json"
        }
    });
    $('#bookingsDetailTable').DataTable({
        "order": [[0, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json"
        },
        "paging": false,
        "info": false,
        "searching": false
    });
});
</script>
@endpush