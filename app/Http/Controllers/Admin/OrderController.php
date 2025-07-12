<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    protected function sendLineMessage($lineUserId, $message, $flex = null)
{
    if (!$lineUserId) {
        Log::warning('LINE PUSH: ไม่พบ line_id ของ user');
        return false;
    }
    $token = env('LINE_CHANNEL_ACCESS_TOKEN');
    $body = [
        'to' => $lineUserId,
        'messages' => []
    ];
    if ($flex) {
        $body['messages'][] = [
            'type' => 'flex',
            'altText' => 'แจ้งเตือนสถานะคำสั่งซื้อ',
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
        Log::info('LINE PUSH SUCCESS', [
            'userId' => $lineUserId,
            'msg' => $message,
            'status' => $response->getStatusCode(),
            'response' => $response->getBody()->getContents()
        ]);
        return $response->getStatusCode() === 200;
    } catch (\Exception $e) {
        Log::error('LINE Message API Error: ' . $e->getMessage());
        return false;
    }
}

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'promotion']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order code or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sort orders
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'total_asc':
                $query->orderBy('total_amount', 'asc');
                break;
            case 'total_desc':
                $query->orderBy('total_amount', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
                break;
        }

        $orders = $query->paginate(10)->withQueryString();

        // Get statistics for dashboard
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'today_orders' => Order::whereDate('created_at', Carbon::today())->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'promotion']);

        $stats = [
            'subtotal' => $order->items->sum(function($item) {
                return $item->quantity * $item->price;
            }),
            'total_items' => $order->items->sum('quantity'),
            'discount' => $order->discount_amount ?? 0,
            'final_total' => $order->total_amount
        ];

        return view('admin.orders.show', compact('order', 'stats'));
    }

    public function edit(Order $order)
    {
        $order->load(['items.product', 'promotion']);
        $statuses = [
            'pending' => 'รอดำเนินการ',
            'processing' => 'กำลังดำเนินการ',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก'
        ];

        $paymentStatuses = [
            'pending' => 'รอชำระเงิน',
            'paid' => 'ชำระแล้ว',
            'failed' => 'การชำระเงินล้มเหลว'
        ];

        return view('admin.orders.edit', compact('order', 'statuses', 'paymentStatuses'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $oldPaymentStatus = $order->payment_status;

            // Update order status
            $order->update($validated);

            // Check if order is now completed and paid
            if ($validated['status'] === 'completed' && $validated['payment_status'] === 'paid') {
                // Decrease stock for each product
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        // Check if enough stock
                        if ($product->stock < $item->quantity) {
                            throw new \Exception("สินค้า {$product->name} มีจำนวนไม่เพียงพอในคลัง");
                        }
                        // Decrease stock
                        $product->decrement('stock', $item->quantity);
                    }
                }
            }

            // If order was completed but now cancelled, restore stock
            if ($oldStatus === 'completed' && $validated['status'] === 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        // Restore stock
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            DB::commit();
            
                   // แจ้งเตือน LINE แบบ Flex Message
        $lineUserId = $order->user->line_id ?? null;
        $flex = $this->buildOrderFlexMessage($order, $validated['notes'] ?? null);
        $this->sendLineMessage($lineUserId, '', $flex);

            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'อัพเดตออเดอร์สำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;

            // Update order status
            $order->update($validated);

            // If order becomes completed and is already paid
            if ($validated['status'] === 'completed' && $order->payment_status === 'paid') {
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        if ($product->stock < $item->quantity) {
                            throw new \Exception("สินค้า {$product->name} มีจำนวนไม่เพียงพอในคลัง");
                        }
                        $product->decrement('stock', $item->quantity);
                    }
                }
            }

            // If completed order is cancelled
            if ($oldStatus === 'completed' && $validated['status'] === 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            DB::commit();

            // แจ้งเตือน LINE เมื่อสถานะเปลี่ยน
           $lineUserId = $order->user->line_id ?? null;
        $flex = $this->buildOrderFlexMessage($order, $order->notes ?? null);
        $this->sendLineMessage($lineUserId, '', $flex);


            return back()->with('success', 'อัพเดตสถานะสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        try {
            if ($order->status === 'completed') {
                return back()->with('error', 'ไม่สามารถลบออเดอร์ที่เสร็จสิ้นแล้วได้');
            }

            DB::beginTransaction();

            // Restore product quantities if order is not cancelled
            if ($order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Delete related records
            $order->items()->delete();
            $order->delete();

            DB::commit();
            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'ลบออเดอร์สำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function print(Order $order)
    {
        $order->load(['user', 'items.product', 'promotion']);
        return view('admin.orders.print', compact('order'));
    }

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
                    'text' => '📦 อัปเดตสถานะคำสั่งซื้อ',
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
                            'text' => 'ชื่อ: ' . ($order->user->name ?? '-'),
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
                        // ควรเป็น public URL เท่านั้น!
                        'uri' => url('/user/orders/' . $order->id)
                    ]
                ]
            ]))
        ]
    ];

    // Log JSON ที่จะส่งไป LINE
    \Log::info('LINE FLEX JSON', ['json' => json_encode($flex, JSON_UNESCAPED_UNICODE)]);

    return $flex;
}
    public function export(Request $request)
    {
        // Add export functionality if needed
    }
}