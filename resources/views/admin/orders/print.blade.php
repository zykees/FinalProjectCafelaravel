<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบสั่งซื้อ #{{ $order->order_code }}</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ใบสั่งซื้อ</h1>
        <h2>เลขที่: {{ $order->order_code }}</h2>
        <p>วันที่: {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div style="margin-bottom: 20px;">
        <h3>ข้อมูลลูกค้า</h3>
        <p>ชื่อ: {{ $order->shipping_name }}</p>
        <p>ที่อยู่: {{ $order->shipping_address }}</p>
        <p>เบอร์โทร: {{ $order->shipping_phone }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">ลำดับ</th>
                <th>สินค้า</th>
                <th class="text-end">ราคาปกติ</th>
                <th class="text-end">ส่วนลด</th>
                <th class="text-end">ราคาหลังหักส่วนลด</th>
                <th class="text-center">จำนวน</th>
                <th class="text-end">รวม</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal = 0;
                $discountTotal = 0;
                $finalTotal = 0;
            @endphp
            @foreach($order->items as $index => $item)
                @php
                    $discountPercent = $item->product->discount_percent ?? 0;
                    $originalPrice = $item->product->price;
                    $discountedPrice = $discountPercent > 0
                        ? round($originalPrice * (1 - $discountPercent/100), 2)
                        : $originalPrice;
                    $itemTotal = $discountedPrice * $item->quantity;
                    $itemDiscount = ($originalPrice - $discountedPrice) * $item->quantity;
                    $subtotal += $originalPrice * $item->quantity;
                    $discountTotal += $itemDiscount;
                    $finalTotal += $itemTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-end">
                        @if($discountPercent > 0)
                            <span style="text-decoration: line-through; color: #dc3545;">
                                ฿{{ number_format($originalPrice, 2) }}
                            </span>
                        @else
                            ฿{{ number_format($originalPrice, 2) }}
                        @endif
                    </td>
                    <td class="text-end">
                        @if($discountPercent > 0)
                            -฿{{ number_format($originalPrice - $discountedPrice, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end">
                        ฿{{ number_format($discountedPrice, 2) }}
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">
                        ฿{{ number_format($itemTotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end"><strong>ราคารวมปกติ</strong></td>
                <td class="text-end"><strong>฿{{ number_format($subtotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-end"><strong>ส่วนลดรวม</strong></td>
                <td class="text-end"><strong>-฿{{ number_format($discountTotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="6" class="text-end"><strong>ยอดสุทธิหลังหักส่วนลด</strong></td>
                <td class="text-end"><strong>฿{{ number_format($finalTotal, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>สถานะคำสั่งซื้อ: {{ __('orders.status.' . $order->status) }}</p>
        <p>สถานะการชำระเงิน: {{ __('orders.payment_status.' . $order->payment_status) }}</p>
        <p>พิมพ์วันที่: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>