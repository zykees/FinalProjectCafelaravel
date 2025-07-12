<div class="card-header py-3">
    <form action="{{ $route }}" method="GET" class="row g-3 align-items-center">
        <div class="col-auto">
            <label class="sr-only">ช่วงวันที่</label>
            <input type="text" class="form-control" id="reportDateRange" name="date_range" 
                   value="{{ request('date_range', now()->startOfMonth()->format('Y-m-d').' - '.now()->format('Y-m-d')) }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> กรองข้อมูล
            </button>
        </div>
        
        @if(isset($allowExport) && $allowExport)
        <div class="col-auto">
            <button type="button" class="btn btn-success" onclick="exportReport('{{ $reportType }}', 'excel')">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button type="button" class="btn btn-danger" onclick="exportReport('{{ $reportType }}', 'pdf')">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
        </div>
        @endif
    </form>
</div>

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@push('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {
    $('#reportDateRange').daterangepicker({
        startDate: moment().startOf('month'),
        endDate: moment(),
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: 'ตกลง',
            cancelLabel: 'ยกเลิก',
            fromLabel: 'จาก',
            toLabel: 'ถึง',
            customRangeLabel: 'กำหนดเอง',
            daysOfWeek: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
            monthNames: [
                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
                'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
                'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
            ]
        },
        ranges: {
           'วันนี้': [moment(), moment()],
           'เมื่อวาน': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 วันที่ผ่านมา': [moment().subtract(6, 'days'), moment()],
           '30 วันที่ผ่านมา': [moment().subtract(29, 'days'), moment()],
           'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
           'เดือนที่แล้ว': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
});

function exportReport(reportType, format) {
    const dateRange = $('#reportDateRange').val();
    const url = "{{ route('admin.reports.export', ['type' => ':type']) }}".replace(':type', reportType);
    window.location.href = `${url}?format=${format}&date_range=${dateRange}`;
}
</script>
@endpush