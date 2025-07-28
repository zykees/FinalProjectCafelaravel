<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionBooking;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class PromotionBookingController extends Controller
{
    // ฟังก์ชันส่งข้อความไป LINE
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
                'altText' => 'แจ้งเตือนการจองโปรโมชั่น',
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
                        'text' => '🎉 คุณได้ทำการจองโปรโมชั่นเรียบร้อยแล้ว',
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
                                'text' => ($booking->number_of_participants ?? '-') . ' ที่นั่ง',
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
                                    ($booking->activity_end_date ? $booking->activity_end_date->format('d/m/Y') : '-'),
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
                    $booking->note ? [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'หมายเหตุ: ' . $booking->note,
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
    public function index()
    {
        $bookings = auth()->user()
            ->promotionBookings()
            ->with('promotion')
            ->latest()
            ->paginate(10);

        return view('User.promotion-bookings.index', compact('bookings'));
    }

    public function create(Promotion $promotion)
    {
        if ($promotion->isExpired()) {
            return back()->with('error', 'โปรโมชั่นนี้ไม่สามารถจองได้แล้ว');
        }

        return view('User.promotion-bookings.create', compact('promotion'));
    }

    public function show(PromotionBooking $booking)
    {
        try {
            $this->authorize('view', $booking);
            return view('User.promotion-bookings.show', compact('booking'));
        } catch (\Exception $e) {
            return back()->with('error', 'ไม่มีสิทธิ์เข้าถึงข้อมูลการจองนี้');
        }
    }

    public function store(Request $request, Promotion $promotion)
    {
        try {
            $this->authorize('create', PromotionBooking::class);

            // Validate the request
            $validated = $request->validate([
                'number_of_participants' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:' . $promotion->getRemainingSlots()
                ],
                'activity_date' => [
                    'required',
                    'date',
                    'after:today',
                    'before_or_equal:' . $promotion->ends_at->format('Y-m-d')
                ],
                'activity_time' => [
                    'required',
                    'date_format:H:i',
                    'after_or_equal:' . $promotion->starts_at->format('H:i'),
                    'before_or_equal:' . $promotion->ends_at->format('H:i')
                ],
                'note' => 'nullable|string|max:500'
            ]);

            // Calculate prices
            $totalPrice = $promotion->price_per_person * $validated['number_of_participants'];
            $discountAmount = ($totalPrice * $promotion->discount) / 100;
            $finalPrice = $totalPrice - $discountAmount;

            // Create booking
            $booking = PromotionBooking::create([
                'user_id' => auth()->id(),
                'promotion_id' => $promotion->id,
                'booking_code' => 'PB' . time() . rand(1000, 9999),
                'number_of_participants' => $validated['number_of_participants'],
                'activity_date' => $validated['activity_date'],
                'activity_end_date' => $promotion->ends_at,
                'activity_time' => $validated['activity_time'],
                'note' => $validated['note'],
                'total_price' => $totalPrice,
                'discount_amount' => $discountAmount,
                'final_price' => $finalPrice,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Update promotion participants count
            $promotion->increment('current_participants', $validated['number_of_participants']);

            // แจ้งเตือน LINE ทันทีเมื่อจองสำเร็จ
            $lineUserId = auth()->user()->line_user_id ?? null;
            $flex = $this->buildBookingFlexMessage($booking);
            $this->sendLineMessage($lineUserId, '', $flex);

            return redirect()->route('user.promotion-bookings.show', $booking)
                           ->with('success', 'จองกิจกรรมสำเร็จ กรุณาชำระเงินเพื่อยืนยันการจอง');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function downloadQuotation(PromotionBooking $booking)
    {
        try {
            $this->authorize('view', $booking);

            $pdf = PDF::loadView('User.promotion-bookings.quotation', [
                'booking' => $booking,
                'user' => auth()->user(),
                'promotion' => $booking->promotion
            ]);

            $filename = 'quotation-' . $booking->booking_code . '.pdf';
          $pdf->set_option('fontDir', storage_path('fonts/'));
          $pdf->set_option('fontCache', storage_path('fonts/'));
            return $pdf->stream($filename);

        } catch (\Exception $e) {
            return back()->with('error', 'ไม่สามารถดาวน์โหลดใบเสนอราคาได้: ' . $e->getMessage());
        }
    }

    public function uploadPaymentSlip(Request $request, PromotionBooking $booking)
    {
        try {
            $this->authorize('uploadPaymentSlip', $booking);

            $validated = $request->validate([
                'payment_slip' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'payment_date' => 'required',
                'payment_amount' => 'required|numeric|min:' . $booking->final_price
            ], [
                'payment_slip.required' => 'กรุณาเลือกไฟล์สลิปการโอนเงิน',
                'payment_slip.image' => 'ไฟล์ต้องเป็นรูปภาพเท่านั้น',
                'payment_slip.max' => 'ขนาดไฟล์ต้องไม่เกิน 2MB',
                'payment_date.required' => 'กรุณาระบุวันที่โอนเงิน',
                'payment_amount.required' => 'กรุณาระบุจำนวนเงิน',
                'payment_amount.min' => 'จำนวนเงินต้องไม่น้อยกว่ายอดที่ต้องชำระ'
            ]);

            $paymentDate = $request->payment_date
                ? \Carbon\Carbon::parse(str_replace('T', ' ', $request->payment_date))
                : null;

            if ($request->hasFile('payment_slip')) {
                // ลบไฟล์เดิมจาก Cloudinary (ถ้ามี)
                if ($booking->payment_slip && str_starts_with($booking->payment_slip, 'http')) {
                    $publicId = basename(parse_url($booking->payment_slip, PHP_URL_PATH));
                    \Storage::disk('cloudinary')->delete('Projectcafe_PaymentSlips/' . $publicId);
                }

                $file = $request->file('payment_slip');
                // อัปโหลดไป Cloudinary
                $publicId = \Storage::disk('cloudinary')->putFile('Projectcafe_PaymentSlips', $file);
                if ($publicId) {
                    $cloudName = env('CLOUDINARY_CLOUD_NAME');
                    $imageUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
                } else {
                    return back()->with('error', 'อัปโหลดสลิปไป Cloudinary ไม่สำเร็จ');
                }

                // Update booking record
                $booking->update([
                    'payment_slip' => $imageUrl,
                    'payment_status' => 'pending',
                    'payment_date' => $paymentDate,
                    'payment_amount' => $request->payment_amount,
                    'status' => 'pending'
                ]);

                return back()->with('success', 'อัพโหลดสลิปการโอนเงินสำเร็จ กรุณารอการตรวจสอบ');
            }

            return back()->with('error', 'กรุณาเลือกไฟล์สลิปการโอนเงิน');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function cancelBooking(PromotionBooking $booking)
    {
        try {
            $this->authorize('cancel', $booking);

            if ($booking->status !== 'pending') {
                return back()->with('error', 'ไม่สามารถยกเลิกการจองได้ เนื่องจากสถานะไม่ใช่ \"รอดำเนินการ\"');
            }

            // Rollback promotion participants count
            $booking->promotion->decrement('current_participants', $booking->number_of_participants);

            // Update booking status
            $booking->update(['status' => 'cancelled']);

            return redirect()->route('user.promotion-bookings.index')
                             ->with('success', 'ยกเลิกการจองสำเร็จ');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy(PromotionBooking $booking)
    {
        try {
            $this->authorize('delete', $booking);

            if ($booking->status !== 'cancelled') {
                return back()->with('error', 'ไม่สามารถลบการจองนี้ได้ เนื่องจากสถานะไม่ใช่ \"ยกเลิก\"');
            }

            $booking->delete();

            return redirect()->route('user.promotion-bookings.index')
                             ->with('success', 'ลบการจองสำเร็จ');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}