<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'thsarabun';
            font-weight: normal;
            font-style: normal;
            src: url("{{ public_path('fonts/THSarabunNew.ttf') }}");
        }
        @font-face {
            font-family: 'thsarabun';
            font-weight: bold;
            font-style: normal;
            src: url("{{ public_path('fonts/THSarabunNew-Bold.ttf') }}");
        }
        * { font-family: "thsarabun"; }
        body { font-size: 16px; line-height: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1em; }
        th, td { border: 1px solid #000; padding: 5px; font-size: 16px; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tbody { text-align: center; margin: 0; padding: 0;}
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="5" style="font-size: 16px; font-weight: bold; text-align: center;">
                รายงานยอดขาย
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center;">
                วันที่: {{ $startDate ?? now()->format('Y-m-d') }} ถึง {{ $endDate ?? now()->format('Y-m-d') }}
            </th>
        </tr>
        <tr>
            <th>วันที่</th>
            <th>จำนวนออเดอร์</th>
            <th>ยอดขายสุทธิ</th>
            <th>ส่วนลดรวม</th>
            <th>ยอดเฉลี่ยต่อออเดอร์</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['detailed_data'] ?? [] as $row)
        <tr>
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['orders'] }}</td>
            <td>฿{{ number_format($row['total'], 2) }}</td>
            <td>฿{{ number_format($row['discount'], 2) }}</td>
            <td>฿{{ number_format($row['average'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">รวมทั้งหมด</th>
            <th>฿{{ number_format($data['total_sales'] ?? 0, 2) }}</th>
            <th>฿{{ number_format($data['total_discount'] ?? 0, 2) }}</th>
            <th>฿{{ number_format($data['average_order'] ?? 0, 2) }}</th>
        </tr>
    </tfoot>
</table>
</body>
</html>