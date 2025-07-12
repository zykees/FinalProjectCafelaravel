@extends('admin.layouts.admin')

@section('title', 'รายงานการขาย')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายงานการขาย</h1>
        <div>
            <a href="{{ route('admin.reports.export', ['type' => 'sales', 'format' => 'excel']) }}" class="btn btn-sm btn-success me-2">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'sales', 'format' => 'pdf']) }}" class="btn btn-sm btn-danger">
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
                                ยอดขายรวม</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ฿{{ number_format($totalSales ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                จำนวนออเดอร์</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalOrders ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                ยอดเฉลี่ยต่อออเดอร์</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ฿{{ number_format(($totalOrders ?? 0) > 0 ? ($totalSales ?? 0) / ($totalOrders ?? 1) : 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                ออเดอร์รอชำระ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $orders->where('payment_status', 'pending')->count() ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
            <form action="{{ route('admin.reports.sales') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">ช่วงวันที่</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">ถึง</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานะการชำระเงิน</label>
                    <select class="form-select" name="payment_status">
                        <option value="">ทั้งหมด</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>ชำระแล้ว</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>รอชำระ</option>
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

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">รายการออเดอร์</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="salesTable">
                    <thead>
                        <tr>
                            <th>รหัสออเดอร์</th>
                            <th>วันที่</th>
                            <th>ลูกค้า</th>
                            <th>จำนวนรายการ</th>
                            <th>ราคารวมปกติ</th>
                            <th>ส่วนลด</th>
                            <th>ยอดสุทธิ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders ?? [] as $order)
                            <tr>
                                <td>{{ $order->order_code }}</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
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
                                <td>฿{{ number_format($order->calculated_total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ $order->payment_status === 'paid' ? 'ชำระแล้ว' : 'รอชำระ' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($orders))
                <div class="d-flex justify-content-center mt-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#salesTable').DataTable({
        "order": [[1, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json"
        }
    });
});
</script>
@endpush