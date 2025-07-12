<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบเสนอราคา</title>
    <style>
         @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/THSarabunNew Bold.ttf') }}") format('truetype');
        }
        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 16pt;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 1em;
        }
        .table th, .table td { 
            border: 1px solid #000; 
            padding: 8px; 
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1 class="text-center">ใบเสนอราคา</h1>
    <p>เลขที่: {{ $booking->id }}</p>
    <p>วันที่: {{ $booking->created_at->format('d/m/Y') }}</p>

    <div class="customer-info">
        <p><strong>ชื่อลูกค้า:</strong> {{ $booking->user->name }}</p>
        <p><strong>อีเมล:</strong> {{ $booking->user->email }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>รายการ</th>
                <th>จำนวน</th>
                <th>ราคาต่อหน่วย</th>
                <th>รวม</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $booking->promotion->title }}</td>
                <td class="text-center">{{ $booking->number_of_participants }}</td>
                <td class="text-right">฿{{ number_format($booking->promotion->price_per_person, 2) }}</td>
                <td class="text-right">฿{{ number_format($booking->total_price, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">ส่วนลด</td>
                <td class="text-right">฿{{ number_format($booking->discount_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>ยอดรวมสุทธิ</strong></td>
                <td class="text-right"><strong>฿{{ number_format($booking->final_price, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="payment-info" style="margin-top: 20px;">
        <h4>ช่องทางการชำระเงิน</h4>
        <p>ธนาคาร: กสิกรไทย</p>
        <p>เลขที่บัญชี: xxx-x-xxxxx-x</p>
        <p>ชื่อบัญชี: บริษัท xxx จำกัด</p>
    </div>
</body>
</html>