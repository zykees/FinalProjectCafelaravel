@extends('admin.layouts.admin')

@section('title', 'จัดการการจองกิจกรรม')


    <link rel="stylesheet" href="{{ asset('css/admin/promotion-bookings.css') }}">
@stack('styles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">จัดการการจองกิจกรรม</h1>
    </div>
    <div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4 ">
        <div class="card border-left-primary shadow h-100 py-2 ">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2 ">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">การจองทั้งหมด</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">รอดำเนินการ</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ยืนยันแล้ว</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['confirmed'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">เสร็จสิ้น</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">รายการจองทั้งหมด</h6>
        </div>
        <div class="card-body">

            {{-- ฟอร์ม Filter --}}
            <form id="filterForm" action="{{ route('admin.promotion-bookings.index') }}" method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-2">
                    <select name="status" class="form-select filter-auto">
                        <option value="">ทุกสถานะจอง</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>ยืนยันแล้ว</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select filter-auto">
                        <option value="">สถานะชำระเงิน</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>รอชำระเงิน</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>ชำระแล้ว</option>
                        <option value="rejected" {{ request('payment_status') == 'rejected' ? 'selected' : '' }}>ไม่ผ่าน</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="promotion_id" class="form-select filter-auto">
                        <option value="">ทุกกิจกรรม</option>
                        @foreach($promotions as $promotion)
                            <option value="{{ $promotion->id }}" {{ request('promotion_id') == $promotion->id ? 'selected' : '' }}>
                                {{ $promotion->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control filter-auto" value="{{ request('date_from') }}" placeholder="จากวันที่">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control filter-auto" value="{{ request('date_to') }}" placeholder="ถึงวันที่">
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ค้นหา...">
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select filter-auto">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>ล่าสุด</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>เก่าสุด</option>
                        <option value="total_desc" {{ request('sort') == 'total_desc' ? 'selected' : '' }}>ยอดมาก-น้อย</option>
                        <option value="total_asc" {{ request('sort') == 'total_asc' ? 'selected' : '' }}>ยอดน้อย-มาก</option>
                    </select>
                </div>
                {{-- ปุ่มกรองจะซ่อน เพราะ auto-submit --}}
                <div class="col-md-1 d-none">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> กรอง
                    </button>
                </div>
            </form>
            {{-- จบฟอร์ม Filter --}}

            <div class="table-responsive">
                <table class="table table-bordered" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>รหัสการจอง</th>
                            <th>กิจกรรม</th>
                            <th>ผู้จอง</th>
                            <th>จำนวนที่นั่ง</th>
                            <th>ยอดรวม</th>
                            <th>สถานะการจอง</th>
                            <th>สถานะการชำระเงิน</th>
                            <th>วันที่จอง</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_code }}</td>
                                <td>
                                    @if($booking->promotion)
                                        {{ $booking->promotion->title }}
                                    @else
                                        <span class="text-muted">กิจกรรมถูกลบแล้ว</span>
                                    @endif
                                </td>
                                <td>{{ $booking->user->name ?? 'ไม่พบข้อมูลผู้ใช้' }}</td>
                                <td>{{ $booking->number_of_participants }} ที่นั่ง</td>
                                <td>฿{{ number_format($booking->final_price, 2) }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'pending' => 'secondary',
                                            'confirmed' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ][$booking->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        @switch($booking->status)
                                            @case('pending') รอดำเนินการ @break
                                            @case('confirmed') ยืนยันแล้ว @break
                                            @case('completed') เสร็จสิ้น @break
                                            @case('cancelled') ยกเลิก @break
                                            @default -
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : ($booking->payment_status === 'rejected' ? 'danger' : 'warning') }}">
                                        @switch($booking->payment_status)
                                            @case('paid') ชำระแล้ว @break
                                            @case('rejected') ไม่ผ่าน @break
                                            @default รอชำระเงิน
                                        @endswitch
                                    </span>
                                </td>
                                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.promotion-bookings.show', $booking) }}" 
                                       class="btn btn-sm btn-info mb-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- ปุ่มแก้ไขสถานะ --}}
                                    <button type="button" class="btn btn-sm btn-warning mb-1" onclick="editStatus({{ $booking->id }}, '{{ $booking->status }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {{-- ปุ่มแก้ไขสถานะการเงิน --}}
<button type="button" class="btn btn-sm btn-secondary mb-1" onclick="editPaymentStatus({{ $booking->id }}, '{{ $booking->payment_status }}')">
    <i class="fas fa-money-check-alt"></i>
</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">ไม่พบข้อมูลการจอง</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bookings->links() }}
        </div>
    </div>
</div>

{{-- Modal แก้ไขสถานะ --}}
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editStatusForm" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editStatusModalLabel">แก้ไขสถานะการจอง</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <select name="status" id="modalStatusSelect" class="form-select">
                <option value="pending">รอดำเนินการ</option>
                <option value="confirmed">ยืนยันแล้ว</option>
                <option value="completed">เสร็จสิ้น</option>
                <option value="cancelled">ยกเลิก</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-primary">บันทึก</button>
          </div>
        </div>
    </form>
  </div>
</div>
{{-- Modal แก้ไขสถานะการเงิน --}}
<div class="modal fade" id="editPaymentStatusModal" tabindex="-1" aria-labelledby="editPaymentStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editPaymentStatusForm" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editPaymentStatusModalLabel">แก้ไขสถานะการชำระเงิน</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <select name="payment_status" id="modalPaymentStatusSelect" class="form-select">
                <option value="pending">รอชำระเงิน</option>
                <option value="paid">ชำระแล้ว</option>
                <option value="rejected">ไม่ผ่าน</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-primary">บันทึก</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable (optional, ถ้าใช้ pagination Laravel ให้ปิด DataTable)
    // $('#bookingsTable').DataTable({
    //     order: [[3, 'desc']],
    //     pageLength: 10,
    //     language: {
    //         url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json'
    //     }
    // });

    // Auto-submit filter
    $('.filter-auto').on('change', function() {
        $('#filterForm').submit();
    });
    $('.filter-auto[type="date"]').on('change', function() {
        $('#filterForm').submit();
    });
});

// Modal แก้ไขสถานะ
function editStatus(bookingId, currentStatus) {
    $('#editStatusForm').attr('action', '/admin/promotion-bookings/' + bookingId + '/update-status');
    $('#modalStatusSelect').val(currentStatus);
    var modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    modal.show();
}

function editPaymentStatus(bookingId, currentStatus) {
    $('#editPaymentStatusForm').attr('action', '/admin/promotion-bookings/' + bookingId + '/update-payment-status');
    $('#modalPaymentStatusSelect').val(currentStatus);
    var modal = new bootstrap.Modal(document.getElementById('editPaymentStatusModal'));
    modal.show();
}
</script>
@endpush