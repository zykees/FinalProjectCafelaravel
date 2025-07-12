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
        
        * {
            font-family: "thsarabun";
        }
        body {
            font-family: "thsarabun";
            font-size: 16px;
            line-height: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1em;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 16px;
            font-family: "thsarabun";
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="7" style="font-size: 16px; font-weight: bold; text-align: center;">
                รายงานโปรโมชั่น
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">
                วันที่: {{ $data['date_range']['start'] }} ถึง {{ $data['date_range']['end'] }}
            </th>
        </tr>
        <tr>
            <th>รหัส</th>
            <th>ชื่อโปรโมชั่น</th>
            <th>ส่วนลด</th>
            <th>จำนวนที่ใช้</th>
            <th>ยอดขายรวม</th>
            <th>ส่วนลดรวม</th>
            <th>สถานะ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['promotions'] as $promotion)
        <tr>
            <td>{{ $promotion->id }}</td>
            <td>{{ $promotion->title }}</td>
            <td>
                @if($promotion->discount_type == 'percentage')
                    {{ $promotion->discount_value }}%
                @else
                    ฿{{ number_format($promotion->discount_value, 2) }}
                @endif
            </td>
            <td>{{ $promotion->used_count }}/{{ $promotion->max_uses ?: 'ไม่จำกัด' }}</td>
            <td>฿{{ number_format($promotion->total_sales, 2) }}</td>
            <td>฿{{ number_format($promotion->total_discount, 2) }}</td>
            <td>{{ $promotion->status }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4">รวมทั้งหมด</th>
            <th>฿{{ number_format($data['total_sales'], 2) }}</th>
            <th>฿{{ number_format($data['total_discount'], 2) }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
</body>
</html>