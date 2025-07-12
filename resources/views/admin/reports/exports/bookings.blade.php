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
            <th colspan="6" style="font-size: 16px; font-weight: bold; text-align: center;">
                รายงานการจอง
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">
                วันที่: {{ $data['date_range']['start'] }} ถึง {{ $data['date_range']['end'] }}
            </th>
        </tr>
        <tr>
            <th>วันที่จอง</th>
            <th>รหัสการจอง</th>
            <th>ชื่อผู้จอง</th>
            <th>จำนวนที่นั่ง</th>
            <th>วันที่เข้าใช้บริการ</th>
            <th>สถานะ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['bookings'] as $booking)
        <tr>
            <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
            <td>{{ $booking->id }}</td>
            <td>{{ $booking->user->name }}</td>
            <td>
                {{ $booking->number_of_participants ?? $booking->seats ?? 1 }}
            </td>
            <td>
                {{ \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') }} {{ $booking->booking_time }}
            </td>
            <td>{{ $booking->status }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">รวมการจองทั้งหมด</th>
            <th>{{ $data['total_bookings'] }} รายการ</th>
            <th>จำนวนที่นั่งรวม</th>
            <th>{{ $data['total_guests'] }} ที่นั่ง</th>
        </tr>
    </tfoot>
</table>
</body>
</html>