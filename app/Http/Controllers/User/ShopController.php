<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Cart;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    // ฟังก์ชันส่งข้อความไป LINE (รองรับ flex)
    protected function sendLineMessage($lineUserId, $message, $flex = null)
    {
        if (!$lineUserId) return false;
        $token = env('LINE_CHANNEL_ACCESS_TOKEN');
        $body = [
            'to' => $lineUserId,
            'messages' => []
        ];
        if ($flex) {
            $body['messages'][] = [
                'type' => 'flex',
                'altText' => 'แจ้งเตือนการสั่งซื้อสินค้า',
                'contents' => $flex
            ];
        } else {
            $body['messages'][] = [
                'type' => 'text',
                'text' => $message
            ];
        }
        try {
            $client = new Client();
            $response = $client->post('https://api.line.me/v2/bot/message/push', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($body)
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('LINE Message API Error: ' . $e->getMessage());
            return false;
        }
    }

    // ฟังก์ชันสร้าง Flex Message สำหรับการสั่งซื้อ (หัวข้อเปลี่ยน)
    protected function buildOrderFlexMessage($order, $notes = null)
    {
        $statusColors = [
            'pending' => '#fbbf24',
            'processing' => '#3b82f6',
            'completed' => '#22c55e',
            'cancelled' => '#ef4444',
        ];
        $paymentColors = [
            'pending' => '#fbbf24',
            'paid' => '#22c55e',
            'failed' => '#ef4444',
        ];
        $statusText = [
            'pending' => 'รอดำเนินการ',
            'processing' => 'กำลังดำเนินการ',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
        ];
        $paymentText = [
            'pending' => 'รอชำระเงิน',
            'paid' => 'ชำระแล้ว',
            'failed' => 'การชำระเงินล้มเหลว',
        ];

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'type' => 'box',
                'layout' => 'baseline',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $item->product->name ?? '-',
                        'flex' => 5,
                        'size' => 'sm',
                        'color' => '#333333'
                    ],
                    [
                        'type' => 'text',
                        'text' => 'x' . $item->quantity,
                        'flex' => 2,
                        'size' => 'sm',
                        'align' => 'end',
                        'color' => '#666666'
                    ],
                    [
                        'type' => 'text',
                        'text' => number_format($item->price * $item->quantity, 2) . ' บาท',
                        'flex' => 4,
                        'size' => 'sm',
                        'align' => 'end',
                        'color' => '#666666'
                    ],
                ]
            ];
        }

        // Section รายการสินค้า
        $itemSection = array_merge(
            [
                [
                    'type' => 'text',
                    'text' => 'รายการสินค้า',
                    'weight' => 'bold',
                    'size' => 'sm',
                    'color' => '#222222'
                ]
            ],
            $items,
            [
                [
                    'type' => 'separator'
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'ยอดสุทธิ',
                            'size' => 'md',
                            'weight' => 'bold',
                            'color' => '#222222',
                            'flex' => 5
                        ],
                        [
                            'type' => 'text',
                            'text' => number_format($order->total_amount, 2) . ' บาท',
                            'size' => 'md',
                            'weight' => 'bold',
                            'color' => '#22c55e',
                            'align' => 'end',
                            'flex' => 7
                        ]
                    ]
                ]
            ]
        );

        $flex = [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '🛒 คุณได้ทำการสั่งซื้อสินค้าเรียบร้อยแล้ว',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#222222'
                    ]
                ]
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => array_merge([
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'รหัสคำสั่งซื้อ',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 3
                            ],
                            [
                                'type' => 'text',
                                'text' => $order->order_code,
                                'size' => 'sm',
                                'color' => '#222222',
                                'flex' => 7
                            ]
                        ]
                    ],
                    [
                        'type' => 'separator'
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'สถานะออเดอร์',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 3
                            ],
                            [
                                'type' => 'text',
                                'text' => $statusText[$order->status] ?? $order->status,
                                'size' => 'sm',
                                'weight' => 'bold',
                                'color' => $statusColors[$order->status] ?? '#888888',
                                'flex' => 7
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'สถานะชำระเงิน',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 3
                            ],
                            [
                                'type' => 'text',
                                'text' => $paymentText[$order->payment_status] ?? $order->payment_status,
                                'size' => 'sm',
                                'weight' => 'bold',
                                'color' => $paymentColors[$order->payment_status] ?? '#888888',
                                'flex' => 7
                            ]
                        ]
                    ],
                    [
                        'type' => 'separator'
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'margin' => 'md',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'ข้อมูลลูกค้า',
                                'weight' => 'bold',
                                'size' => 'sm',
                                'color' => '#222222'
                            ],
                            [
                                'type' => 'text',
                                'text' => 'ชื่อ: ' . ($order->shipping_name ?? $order->user->name ?? '-'),
                                'size' => 'sm',
                                'color' => '#333333'
                            ],
                            [
                                'type' => 'text',
                                'text' => 'เบอร์: ' . ($order->shipping_phone ?? '-'),
                                'size' => 'sm',
                                'color' => '#333333'
                            ],
                            [
                                'type' => 'text',
                                'text' => 'ที่อยู่: ' . ($order->shipping_address ?? '-'),
                                'size' => 'sm',
                                'wrap' => true,
                                'color' => '#333333'
                            ]
                        ]
                    ],
                    [
                        'type' => 'separator'
                    ]
                ], $itemSection)
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => array_values(array_filter([
                    $notes ? [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'หมายเหตุ: ' . $notes,
                                'size' => 'sm',
                                'color' => '#ef4444',
                                'wrap' => true
                            ]
                        ]
                    ] : null,
                    [
                        'type' => 'button',
                        'style' => 'primary',
                        'color' => '#3b82f6',
                        'action' => [
                            'type' => 'uri',
                            'label' => 'ดูรายละเอียดออเดอร์',
                            'uri' => url('/user/orders/' . $order->id)
                        ]
                    ]
                ]))
            ]
        ];

        // Log JSON ที่จะส่งไป LINE
        \Log::info('LINE FLEX ORDER JSON', ['json' => json_encode($flex, JSON_UNESCAPED_UNICODE)]);

        return $flex;
    }

    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category_name (string)
        if ($request->filled('category_name')) {
            $query->where('category_name', $request->category_name);
        }

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%");
            });
        }

        // เงื่อนไขอื่นๆ เช่น สินค้าพร้อมขาย
        $query->where('status', 'available');

        $products = $query->paginate(16)->appends($request->query());

        // ดึงชื่อหมวดหมู่ที่มีใน products ทั้งหมด (distinct)
        $categoryNames = Product::select('category_name')
            ->distinct()
            ->whereNotNull('category_name')
            ->pluck('category_name')
            ->toArray();

        return view('User.shop.index', compact('products', 'categoryNames'));
    }

    public function show(Product $product)
    {
        // ดึงสินค้าที่เกี่ยวข้องจากหมวดหมู่เดียวกัน (category_name)
        $relatedProducts = Product::where('category_name', $product->category_name)
            ->where('id', '!=', $product->id)
            ->where('status', 'available')
            ->where('stock', '>', 0)
            ->take(4)
            ->get();

        return view('User.shop.product', compact('product', 'relatedProducts'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => "required|integer|min:1|max:{$product->stock}"
        ]);

        if ($product->status !== 'available' || $product->stock <= 0) {
            return back()->with('error', 'สินค้าไม่พร้อมขายในขณะนี้');
        }

        // เช็คยอดในตะกร้าปัจจุบัน
        $cartItem = \Cart::get($product->id);
        $currentInCart = $cartItem ? $cartItem->quantity : 0;

        if ($request->quantity + $currentInCart > $product->stock) {
            return back()->with('error', 'จำนวนสินค้าที่เลือกเกินจำนวนคงเหลือในสต็อก');
        }

        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $request->quantity,
            'attributes' => [
                'image' => $product->image
            ]
        ]);

        return back()->with('success', 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว');
    }

    public function checkout()
    {
        if (Cart::isEmpty()) {
            return redirect()->route('user.shop.cart')
                ->with('error', 'ตะกร้าสินค้าว่างเปล่า');
        }

        $user = auth()->user();
        return view('User.shop.checkout', compact('user'));
    }

    public function processCheckout(Request $request)
    {
        if (Cart::isEmpty()) {
            return redirect()->route('user.shop.cart')
                ->with('error', 'ตะกร้าสินค้าว่างเปล่า');
        }

        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_address' => 'required|string',
            'shipping_phone' => 'required|string',
            'payment_method' => 'required|in:bank_transfer'
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => auth()->id(),
                'order_code' => 'ORD-' . time(),
                'total_amount' => Cart::getTotal(),
                'shipping_name' => $validated['shipping_name'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_phone' => $validated['shipping_phone'],
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            foreach(Cart::getContent() as $item) {
                $order->items()->create([
                    'product_id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ]);
            }

            DB::commit();
            Cart::clear();

            // แจ้งเตือน LINE Flex Message
            $order->load(['items.product', 'user']);
            $lineUserId = auth()->user()->line_id ?? null;
            $flex = $this->buildOrderFlexMessage($order, $order->notes ?? null);
            $this->sendLineMessage($lineUserId, '', $flex);

            return redirect()->route('user.orders.show', ['order' => $order])
                ->with('success', 'สั่งซื้อสำเร็จ! กรุณาแจ้งชำระเงินเพื่อดำเนินการต่อ');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function showOrder(Order $order)
    {
        // Verify order belongs to current user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Load relationships
        $order->load(['items.product', 'user']);

        return view('User.orders.show', compact('order'));
    }

    public function downloadQuotation(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $pdf = Pdf::loadView('User.orders.quotation', [
            'order' => $order->load('items.product')
        ]);

        return $pdf->download("ใบเสนอราคา-{$order->order_code}.pdf");
    }
}