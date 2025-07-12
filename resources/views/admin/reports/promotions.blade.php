@extends('admin.layouts.admin')

@section('title', 'รายงานโปรโมชั่น')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">รายงานโปรโมชั่น</h1>
        <div>
            <a href="{{ route('admin.reports.export', ['type' => 'promotions', 'format' => 'excel']) }}" class="btn btn-sm btn-success me-2">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.export', ['type' => 'promotions', 'format' => 'pdf']) }}" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary ms-2">
                <i class="fas fa-arrow-left"></i> กลับหน้ารายงาน
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(isset($data))
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                โปรโมชั่นที่ใช้งานได้
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $data['active_promotions'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percent fa-2x text-gray-300"></i>
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
                                จำนวนการใช้งาน
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $data['total_uses'] ?? 0 }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                ยอดส่วนลดรวม
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ฿{{ number_format($data['total_savings'] ?? 0, 2) }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                ส่วนลดเฉลี่ย
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ฿{{ number_format($data['average_discount'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">ตัวกรองข้อมูล</h6>
        </div>
        <div class="card-body">
            @include('admin.reports._filter', [
                'route' => route('admin.reports.promotions'),
                'reportType' => 'promotions',
                'allowExport' => true
            ])
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Promotions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">รายละเอียดโปรโมชั่น</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="promotionsTable">
                    <thead>
    <tr>
        <th>ชื่อโปรโมชั่น</th>
        <th>ประเภทส่วนลด</th>
        <th>มูลค่าส่วนลด</th>
        <th>จำนวนการใช้งาน</th>
        <th>จำนวนสูงสุด</th>
        <th>ยอดส่วนลดรวม</th>
        <th>วันที่เริ่ม</th>
        <th>วันที่สิ้นสุด</th>
        <th>สถานะ</th>
    </tr>
</thead>
<tbody>
    @forelse($data['detailed_data'] ?? [] as $promotion)
        <tr>
            <td>{{ $promotion['title'] }}</td>
            <td>
                @if($promotion['discount_type'] === 'percent')
                    เปอร์เซ็นต์
                @else
                    บาท
                @endif
            </td>
            <td>
                @if($promotion['discount_type'] === 'percent')
                    {{ $promotion['discount'] }}%
                @else
                    ฿{{ number_format($promotion['discount'], 2) }}
                @endif
            </td>
            <td>{{ $promotion['used_count'] }}</td>
            <td>{{ $promotion['max_uses'] }}</td>
            <td>
                ฿{{ number_format($promotion['total_savings'], 2) }}
            </td>
            <td>{{ $promotion['start_date'] ? \Carbon\Carbon::parse($promotion['start_date'])->format('d/m/Y') : '-' }}</td>
            <td>{{ $promotion['end_date'] ? \Carbon\Carbon::parse($promotion['end_date'])->format('d/m/Y') : '-' }}</td>
            <td>
                @if($promotion['status'] == 'active')
                    <span class="badge bg-success">ใช้งานได้</span>
                @else
                    <span class="badge bg-secondary">หมดอายุ</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center">ไม่พบข้อมูลโปรโมชั่น</td>
        </tr>
    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if(!isset($data))
        <div class="alert alert-info">
            กรุณาเลือกช่วงวันที่เพื่อดูรายงาน
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#promotionsTable').DataTable({
        "order": [[5, "desc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Thai.json"
        }
    });
});
</script>
@endpush