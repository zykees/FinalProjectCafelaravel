<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสนอราคา #{{ $order->order_code }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .customer-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ใบเสนอราคา</h1>
        <h2>เลขที่: {{ $order->order_code }}</h2>
        <p>วันที่: {{ $order->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="company-info">
        <h3>ข้อมูลบริษัท</h3>
        <p>ชื่อบริษัท: Your Company Name</p>
        <p>ที่อยู่: Your Company Address</p>
        <p>เบอร์โทร: Your Phone Number</p>
        <p>อีเมล: your@email.com</p>
    </div>

    <div class="customer-info">
        <h3>ข้อมูลลูกค้า</h3>
        <p>ชื่อ: {{ $order->shipping_name }}</p>
        <p>ที่อยู่: {{ $order->shipping_address }}</p>
        <p>เบอร์โทร: {{ $order->shipping_phone }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>รายการ</th>
                <th>จำนวน</th>
                <th>ราคาต่อหน่วย</th>
                <th>รวม</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>฿{{ number_format($item->price, 2) }}</td>
                    <td>฿{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>ยอดรวมทั้งสิ้น</strong></td>
                <td><strong>฿{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>ขอบคุณที่ใช้บริการ</p>
    </div>
</body>
</html>