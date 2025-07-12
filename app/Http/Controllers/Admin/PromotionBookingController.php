<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PromotionBooking;
use App\Models\Promotion;
use App\Notifications\BookingStatusChanged;
use App\Notifications\PaymentStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class PromotionBookingController extends Controller
{
    // ฟังก์ชันส่งข้อความไป LINE
     protected function sendLineMessage($lineUserId, $message, $flex = null)
    {
        if (!$lineUserId) {
            Log::warning('LINE PUSH: ไม่พบ line_user_id ของ user');
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
                'altText' => 'แจ้งเตือนสถานะการจองกิจกรรม',
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
        $query = PromotionBooking::with(['promotion', 'user']);

        // Filter: สถานะการจอง
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: สถานะการชำระเงิน
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter: กิจกรรม (promotion)
        if ($request->filled('promotion_id')) {
            $query->where('promotion_id', $request->promotion_id);
        }

        // Filter: วันที่จอง
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: ค้นหา (booking code, ชื่อผู้จอง, เบอร์, email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('promotion', function($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        switch ($request->get('sort', 'latest')) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'total_desc':
                $query->orderBy('final_price', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('final_price', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $bookings = $query->paginate(15)->appends($request->query());
        $promotions = Promotion::orderBy('title')->get();

        // สถิติ 4 กล่อง
        $stats = [
            'total'      => PromotionBooking::count(),
            'pending'    => PromotionBooking::where('status', 'pending')->count(),
            'confirmed'  => PromotionBooking::where('status', 'confirmed')->count(),
            'completed'  => PromotionBooking::where('status', 'completed')->count(),
        ];

        return view('admin.promotion-bookings.index', compact('bookings', 'promotions', 'stats'));
    }

    public function show(PromotionBooking $booking)
    {
        return view('admin.promotion-bookings.show', compact('booking'));
    }

    public function edit(PromotionBooking $booking)
    {
        return view('admin.promotion-bookings.edit', compact('booking'));
    }

   
    public function update(Request $request, PromotionBooking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'payment_status' => 'required|in:pending,paid,rejected',
            'admin_comment' => 'nullable|string|max:500'
        ]);

        $booking->update($validated);

        // แจ้งเตือน LINE Flex Message
        $lineUserId = $booking->user->line_id ?? null;
        $flex = $this->buildBookingFlexMessage($booking);
        $this->sendLineMessage($lineUserId, '', $flex);

        // Laravel notification เดิม
        if($booking->wasChanged('status')) {
            $booking->user->notify(new BookingStatusChanged($booking));
        }

        return redirect()
            ->route('admin.promotion-bookings.show', $booking)
            ->with('success', 'อัพเดทสถานะการจองเรียบร้อย');
    }
     public function updatePaymentStatus(Request $request, PromotionBooking $booking)
    {
        try {
            $validated = $request->validate([
                'payment_status' => 'required|in:pending,paid,rejected',
                'admin_comment' => 'nullable|string|max:500'
            ]);

            $booking->update($validated);

            // แจ้งเตือน LINE Flex Message
            $lineUserId = $booking->user->line_id ?? null;
            $flex = $this->buildBookingFlexMessage($booking);
            $this->sendLineMessage($lineUserId, '', $flex);

            // Laravel notification เดิม
            try {
                $booking->user->notify(new PaymentStatusChanged($booking));
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
            }

            return back()->with('success', 'อัพเดทสถานะการชำระเงินเรียบร้อย');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function downloadPaymentSlip(PromotionBooking $booking)
    {
        if (!$booking->payment_slip) {
            return back()->with('error', 'ไม่พบไฟล์สลิปการโอนเงิน');
        }

        $path = storage_path('app/public/' . $booking->payment_slip);

        if (!file_exists($path)) {
            return back()->with('error', 'ไฟล์ไม่พบในระบบ');
        }

        return response()->download($path);
    }

     public function updateStatus(Request $request, PromotionBooking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);
        $booking->status = $request->status;
        $booking->save();

        // แจ้งเตือน LINE Flex Message
        $lineUserId = $booking->user->line_id ?? null;
        $flex = $this->buildBookingFlexMessage($booking);
        $this->sendLineMessage($lineUserId, '', $flex);

        return redirect()->route('admin.promotion-bookings.index')
            ->with('success', 'อัปเดตสถานะการจองเรียบร้อยแล้ว');
    }
    // เพิ่มฟังก์ชันสร้าง Flex Message สำหรับการจอง
    protected function buildBookingFlexMessage($booking)
    {
        $statusColors = [
            'pending' => '#fbbf24',
            'confirmed' => '#3b82f6',
            'completed' => '#22c55e',
            'cancelled' => '#ef4444',
        ];
        $paymentColors = [
            'pending' => '#fbbf24',
            'paid' => '#22c55e',
            'rejected' => '#ef4444',
        ];
        $statusText = [
            'pending' => 'รอดำเนินการ',
            'confirmed' => 'ยืนยันแล้ว',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
        ];
        $paymentText = [
            'pending' => 'รอชำระเงิน',
            'paid' => 'ชำระแล้ว',
            'rejected' => 'ชำระเงินไม่ผ่าน',
        ];

        $flex = [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '🎫 อัปเดตสถานะการจองกิจกรรม',
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
                'contents' => [
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'รหัสการจอง',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking->booking_code,
                                'size' => 'sm',
                                'color' => '#222222',
                                'flex' => 8
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'ชื่อกิจกรรม',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking->promotion->title ?? '-',
                                'size' => 'sm',
                                'color' => '#222222',
                                'flex' => 8
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'ผู้จอง',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking->user->name ?? '-',
                                'size' => 'sm',
                                'color' => '#222222',
                                'flex' => 8
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'จำนวนที่นั่ง',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking->number_of_participants . ' ที่นั่ง',
                                'size' => 'sm',
                                'color' => '#222222',
                                'flex' => 8
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'ยอดรวม',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => number_format($booking->final_price, 2) . ' บาท',
                                'size' => 'sm',
                                'color' => '#22c55e',
                                'flex' => 8
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'สถานะจอง',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $statusText[$booking->status] ?? $booking->status,
                                'size' => 'sm',
                                'weight' => 'bold',
                                'color' => $statusColors[$booking->status] ?? '#888888',
                                'flex' => 8
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
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $paymentText[$booking->payment_status] ?? $booking->payment_status,
                                'size' => 'sm',
                                'weight' => 'bold',
                                'color' => $paymentColors[$booking->payment_status] ?? '#888888',
                                'flex' => 8
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
                                'text' => 'วันที่จอง',
                                'size' => 'sm',
                                'color' => '#888888',
                                'flex' => 4
                            ],
                            [
                                'type' => 'text',
                                'text' => $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '-',
                                'size' => 'sm',
                                'color' => '#222222',
                                'flex' => 8
                            ]
                        ]
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
  'type' => 'text',
            'text' => 'ระยะเวลากิจกรรม',
            'size' => 'sm',
            'color' => '#888888',
            'flex' => 4
                            ],
                            [
                              'type' => 'text',
            'text' => 
               ($booking->activity_date ? $booking->activity_date->format('d/m/Y') : '-') .
                ' ถึง ' .
                ($booking->promotion->ends_at ? $booking->promotion->ends_at->format('d/m/Y H:i') : '-'),
            'size' => 'sm',
            'color' => '#222222',
            'flex' => 8
                            ]
                        ]
                    ]
                ]
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'sm',
                'contents' => array_values(array_filter([
                    $booking->admin_comment ? [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'หมายเหตุ: ' . $booking->admin_comment,
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
                            'label' => 'ดูรายละเอียดการจอง',
                            'uri' => route('user.promotion-bookings.show', $booking->id)
                        ]
                    ]
                ]))
            ]
        ];

        // Log JSON ที่จะส่งไป LINE
        \Log::info('LINE FLEX BOOKING JSON', ['json' => json_encode($flex, JSON_UNESCAPED_UNICODE)]);

        return $flex;
    }
}