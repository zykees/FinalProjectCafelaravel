<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Promotion;
use App\Models\OrderItem;
use App\Models\PromotionBooking;
use App\Exports\ReportExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
public function index()
{
    try {
        // 30 วันล่าสุด
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        // ยอดขายสุทธิ (orders)
        $totalSales = \App\Models\Order::where('status', 'completed')->get()->sum(function($order) {
    return $order->calculated_total;
});
        $totalBookingAmount = \App\Models\PromotionBooking::where('payment_status', 'paid')->sum('final_price');
        $totalOrders = \App\Models\Order::where('status', 'completed')->count();
        $totalPromotionBookings = \App\Models\PromotionBooking::count();
        $activePromotions = \App\Models\Promotion::where('status', 'active')->count();
        $bookedPromotions = \App\Models\PromotionBooking::distinct('promotion_id')->count('promotion_id');
        $totalCustomers = \App\Models\User::count();

        // เตรียมข้อมูลกราฟยอดขาย 30 วัน
        $salesChart = \App\Models\Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($order) {
                return $order->created_at->format('Y-m-d');
            });

        $bookingChart = \App\Models\PromotionBooking::where('payment_status', 'paid')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get()
        ->groupBy(function($booking) {
            return $booking->created_at->format('Y-m-d');
        });

        $labels = [];
        $salesData = [];
        $bookingData = [];
        for ($i = 0; $i < 30; $i++) {
    $date = now()->subDays(29 - $i)->format('Y-m-d');
    $labels[] = $date;
    $salesData[] = isset($salesChart[$date])
        ? $salesChart[$date]->sum(function($order) {
            return $order->calculated_total;
        })
        : 0;
    $bookingData[] = isset($bookingChart[$date]) ? $bookingChart[$date]->sum('final_price') : 0;
}

        return view('admin.reports.index', [
            'stats' => [
                'total_sales' => $totalSales,
                'total_booking_amount' => $totalBookingAmount,
                'total_orders' => $totalOrders,
                'total_promotion_bookings' => $totalPromotionBookings,
                'active_promotions' => $activePromotions,
                'booked_promotions' => $bookedPromotions,
                'total_customers' => $totalCustomers,
            ],
            'chart' => [
                'labels' => $labels,
                'sales' => $salesData,
                'bookings' => $bookingData,
            ]
        ]);
    } catch (\Exception $e) {
        return view('admin.reports.index', ['stats' => [], 'chart' => []])
            ->with('error', 'ไม่สามารถโหลดข้อมูลรายงานได้');
    }
}
 public function salesReport(Request $request)
    {
        $orders = Order::with(['user', 'items'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->payment_status, function($query, $payment_status) {
                return $query->where('payment_status', $payment_status);
            })
            ->when($request->search, function($query, $search) {
                return $query->where('order_code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('admin.reports.sales', compact('orders'));
    }
   public function sales(Request $request)
    {
        $query = Order::with(['user', 'items'])
            ->withCount('items')
            ->select('orders.*')
            ->when($request->filled(['start_date', 'end_date']), function ($query) use ($request) {
                return $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            })
            ->when($request->payment_status, function ($query, $status) {
                return $query->where('payment_status', $status);
            });

        $totalSales = $query->sum('total_amount');
        $totalOrders = $query->count();
        
        $orders = $query->latest()->paginate(10);
         // คำนวณยอดขายรวมและยอดเฉลี่ยจากยอดสุทธิ
    $totalSales = $orders->sum(function($order) {
        return $order->calculated_total;
    });
    $totalOrders = $orders->count();
    $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        return view('admin.reports.sales', compact('orders', 'totalSales', 'totalOrders'));
    }

  public function bookings(Request $request)
{
    try {
        $dateRange = $this->getDateRange($request);

        // ดึงข้อมูล bookings รายการจริง (PromotionBooking)
        $bookings = PromotionBooking::with(['promotion', 'user'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->whereDate('created_at', '>=', $dateRange['start'])
            ->whereDate('created_at', '<=', $dateRange['end'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalBookings = $bookings->total();
        $confirmedBookings = $bookings->getCollection()->where('status', 'confirmed')->count();
        $pendingBookings = $bookings->getCollection()->where('status', 'pending')->count();
        $cancelledBookings = $bookings->getCollection()->where('status', 'cancelled')->count();

        $detailedData = $bookings->getCollection()->groupBy(function($booking) {
            return $booking->created_at->format('Y-m-d');
        })->map(function($dayBookings) {
            return [
                'date' => $dayBookings->first()->created_at->format('Y-m-d'),
                'total' => $dayBookings->count(),
                'confirmed' => $dayBookings->where('status', 'confirmed')->count(),
                'pending' => $dayBookings->where('status', 'pending')->count(),
                'cancelled' => $dayBookings->where('status', 'cancelled')->count()
            ];
        })->values()->toArray();

        return view('admin.reports.bookings', [
            'bookings' => $bookings,
            'totalBookings' => $totalBookings,
            'confirmedBookings' => $confirmedBookings,
            'pendingBookings' => $pendingBookings,
            'cancelledBookings' => $cancelledBookings,
            'detailedData' => $detailedData,
        ]);
    } catch (\Exception $e) {
        Log::error('Error in bookings report: ' . $e->getMessage());
        return back()->with('error', 'ไม่สามารถสร้างรายงานการจองได้: ' . $e->getMessage());
    }
}
  public function promotions(Request $request)
{
    $dateRange = $this->getDateRange($request);

    $promotions = Promotion::withCount([
        'bookings as used_count' => function($q) {
            $q->where('payment_status', 'paid');
        }
    ])
    ->whereDate('starts_at', '<=', $dateRange['end'])
    ->whereDate('ends_at', '>=', $dateRange['start'])
    ->get();

    $data = [
        'active_promotions' => $promotions->where('status', 'active')->count(),
        'total_uses' => $promotions->sum('used_count'),
        'total_savings' => $promotions->sum(function($promotion) {
            // ถ้าเป็น percent ให้คำนวณจากยอดจองจริง
            if (($promotion->discount_type ?? 'percent') === 'percent') {
                $sumFinal = $promotion->bookings()->where('payment_status', 'paid')->sum('final_price');
                return $sumFinal * ($promotion->discount / 100);
            } else {
                return $promotion->used_count * $promotion->discount;
            }
        }),
        'average_discount' => $promotions->avg('discount'),
        'detailed_data' => $promotions->map(function($promotion) {
            $sumFinal = $promotion->bookings()->where('payment_status', 'paid')->sum('final_price');
            return [
                'title' => $promotion->title,
                'discount' => $promotion->discount,
                'discount_type' => $promotion->discount_type ?? 'percent',
                'used_count' => $promotion->used_count,
                'max_uses' => $promotion->max_participants ?? $promotion->max_uses ?? 'ไม่จำกัด',
                'total_savings' => ($promotion->discount_type ?? 'percent') === 'percent'
                    ? $sumFinal * ($promotion->discount / 100)
                    : $promotion->used_count * $promotion->discount,
                'start_date' => $promotion->starts_at,
                'end_date' => $promotion->ends_at,
                'status' => $promotion->status,
            ];
        })->toArray(),
    ];

    return view('admin.reports.promotions', compact('data'));
}


public function export($type, Request $request)
{
    try {
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : Carbon::now()->startOfMonth();
        $endDate = request('end_date') ? Carbon::parse(request('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // ตรวจสอบช่วงวันที่
        if ($startDate->greaterThan($endDate)) {
            throw new \Exception('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
        }

        // เตรียมข้อมูลตามประเภทรายงาน
        switch ($type) {
            case 'sales':
                $data = $this->getSalesData($startDate, $endDate);
                $data['date_range'] = [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ];
                $view = 'admin.reports.exports.sales';
                $filename = 'รายงานยอดขาย_' . now('Asia/Bangkok')->format('Y-m-d');
               
                break;
                
            
            case 'bookings':
                $data = $this->getBookingsData($startDate, $endDate);
                $data['date_range'] = [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ];
                $view = 'admin.reports.exports.bookings';
                $filename = 'รายงานการจอง_' . now('Asia/Bangkok')->format('Y-m-d');
                break;
            
            case 'promotions':
                $data = $this->getPromotionsData($startDate, $endDate);
                $data['date_range'] = [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ];
                $view = 'admin.reports.exports.promotions';
                $filename = 'รายงานโปรโมชั่น_' . now('Asia/Bangkok')->format('Y-m-d');
                break;
            
            default:
                throw new \Exception('ประเภทรายงานไม่ถูกต้อง');
        }

        // บันทึก log ข้อมูลที่จะ export
        Log::info('Exporting report', [
            'type' => $type,
            'date_range' => $data['date_range'],
            'format' => $request->format ?? 'excel'
        ]);

        // ตรวจสอบและดำเนินการ export ตาม format
        $format = strtolower($request->format ?? 'excel');

        if ($format === 'pdf') {
    $pdf = Pdf::loadView($view, compact('data'));
    
    // แก้ไขการตั้งค่า PDF
    $dompdf = $pdf->getDomPDF();
    $dompdf->set_option('defaultFont', 'thsarabun');
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->set_option('isFontSubsettingEnabled', true);
    $dompdf->set_option('dpi', 150);
    
    // เพิ่มการตั้งค่า font path
    $dompdf->set_option('fontDir', public_path('fonts/'));
    $dompdf->set_option('fontCache', storage_path('fonts/'));
    
    $pdf->setPaper('a4', 'landscape');
    
    return $pdf->stream($filename . '.pdf');
}

        // ส่งออกเป็น Excel
       // แก้ไขส่วน Excel export
        return Excel::download(
            new ReportExport([
                'data' => $data,
                'startDate' => $startDate->format('Y-m-d'), // แก้ไขตรงนี้
                'endDate' => $endDate->format('Y-m-d')     // แก้ไขตรงนี้
            ], $view),
            $filename . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );

    } catch (\Exception $e) {
        Log::error('Export error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return back()->with('error', 'ไม่สามารถ export รายงานได้: ' . $e->getMessage());
    }
}
private function exportSalesReport($startDate, $endDate)
{
    $orders = Order::with(['user', 'items'])
        ->whereBetween('created_at', [
            $startDate->startOfDay(),
            $endDate->endOfDay()
        ])
        ->get();

    // สร้างไฟล์ Excel หรือ PDF ตามที่ต้องการ
    if (request('format') === 'excel') {
        return Excel::download(new SalesExport($orders), 'sales_report.xlsx');
    } else {
        return PDF::loadView('admin.reports.exports.sales_pdf', compact('orders'))
            ->download('sales_report.pdf');
    }
}

private function getSalesData($startDate, $endDate)
{
    $orders = Order::with(['items', 'items.product'])
        ->whereDate('created_at', '>=', $startDate)
        ->whereDate('created_at', '<=', $endDate)
        ->where('status', 'completed')
        ->get();

    $dailySales = $orders->groupBy(function($order) {
        return $order->created_at->format('Y-m-d');
    });

    return [
        'total_sales' => $orders->sum(function($order) {
            return $order->calculated_total;
        }),
        'total_discount' => $orders->sum(function($order) {
            return $order->calculated_discount;
        }),
        'total_orders' => $orders->count(),
        'average_order' => $orders->count() > 0 ? $orders->sum(function($order) {
            return $order->calculated_total;
        }) / $orders->count() : 0,
        'detailed_data' => $dailySales->map(function($orders) {
            return [
                'date' => $orders->first()->created_at->format('Y-m-d'),
                'orders' => $orders->count(),
                'total' => $orders->sum(function($order) {
                    return $order->calculated_total;
                }),
                'discount' => $orders->sum(function($order) {
                    return $order->calculated_discount;
                }),
                'average' => $orders->count() > 0 ? $orders->sum(function($order) {
                    return $order->calculated_total;
                }) / $orders->count() : 0,
            ];
        })->values()
    ];
}
  private function getBookingsData($startDate, $endDate)
{
    try {
        $bookings = PromotionBooking::with(['user', 'promotion'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $dailyBookings = $bookings->groupBy(function($booking) {
            return $booking->created_at->format('Y-m-d');
        });

        // เปลี่ยนตรงนี้ให้ตรงกับ field จริง
        $totalGuests = $bookings->sum(function($booking) {
            return $booking->number_of_participants ?? $booking->seats ?? 1;
        });

        return [
            'bookings' => $bookings,
            'total_bookings' => $bookings->count(),
            'confirmed_bookings' => $bookings->where('status', 'confirmed')->count(),
            'pending_bookings' => $bookings->where('status', 'pending')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'total_guests' => $totalGuests, // <<--- เพิ่มตรงนี้
            'detailed_data' => $dailyBookings->map(function($dayBookings) {
                return [
                    'date' => $dayBookings->first()->created_at->format('Y-m-d'),
                    'total' => $dayBookings->count(),
                    'confirmed' => $dayBookings->where('status', 'confirmed')->count(),
                    'pending' => $dayBookings->where('status', 'pending')->count(),
                    'cancelled' => $dayBookings->where('status', 'cancelled')->count()
                ];
            })->values()->toArray()
        ];

    } catch (\Exception $e) {
        Log::error('Error in getBookingsData: ' . $e->getMessage());
        throw $e;
    }
}

 private function getPromotionsData($startDate, $endDate)
{
    $promotions = \App\Models\Promotion::withCount([
        'bookings as used_count' => function($q) {
            $q->where('payment_status', 'paid');
        }
    ])
    ->whereDate('starts_at', '<=', $endDate)
    ->whereDate('ends_at', '>=', $startDate)
    ->get();

    // รวมยอดขายและส่วนลดรวม
    $total_sales = 0;
    $total_discount = 0;

    foreach ($promotions as $promotion) {
        // ยอดขายรวม (final_price ของ bookings ที่จ่ายเงินแล้ว)
        $sales = $promotion->bookings()->where('payment_status', 'paid')->sum('final_price');
        $total_sales += $sales;

        // ส่วนลดรวม
        if (($promotion->discount_type ?? 'percent') === 'percent') {
            $discount = $sales * ($promotion->discount / 100);
        } else {
            $discount = $promotion->used_count * $promotion->discount;
        }
        $total_discount += $discount;

        // เพิ่ม property ให้แต่ละ promotion สำหรับ export
        $promotion->total_sales = $sales;
        $promotion->total_discount = $discount;
        $promotion->discount_value = $promotion->discount; // สำหรับ blade
    }

    return [
        'promotions' => $promotions,
        'active_promotions' => $promotions->where('status', 'active')->count(),
        'total_uses' => $promotions->sum('used_count'),
        'total_savings' => $total_discount,
        'average_discount' => $promotions->avg('discount'),
        'total_sales' => $total_sales,
        'total_discount' => $total_discount,
        'detailed_data' => $promotions->map(function($promotion) {
            $sumFinal = $promotion->bookings()->where('payment_status', 'paid')->sum('final_price');
            return [
                'title' => $promotion->title,
                'discount' => $promotion->discount,
                'discount_type' => $promotion->discount_type ?? 'percent',
                'used_count' => $promotion->used_count,
                'max_uses' => $promotion->max_participants ?? $promotion->max_uses ?? 'ไม่จำกัด',
                'total_savings' => ($promotion->discount_type ?? 'percent') === 'percent'
                    ? $sumFinal * ($promotion->discount / 100)
                    : $promotion->used_count * $promotion->discount,
                'start_date' => $promotion->starts_at,
                'end_date' => $promotion->ends_at,
                'status' => $promotion->status,
            ];
        })->toArray(),
    ];
}
private function getDateRange(Request $request)
{
    try {
        $dateRange = $request->input('date_range');
        
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            $start = Carbon::parse($dates[0])->startOfDay();
            $end = Carbon::parse($dates[1])->endOfDay();
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        }

        return [
            'start' => $start,
            'end' => $end
        ];
    } catch (\Exception $e) {
        Log::error('Error in getDateRange: ' . $e->getMessage());
        throw $e;
    }
}
private function validateDateRange($startDate, $endDate)
{
    // ตรวจสอบว่าวันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด
    if ($startDate->greaterThan($endDate)) {
        throw new \Exception('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
    }

    // ตรวจสอบว่าวันที่สิ้นสุดต้องไม่เกินวันปัจจุบัน
    if ($endDate->endOfDay()->greaterThan(now()->endOfDay())) {
        throw new \Exception('วันที่สิ้นสุดต้องไม่เกินวันปัจจุบัน');
    }

    // ตรวจสอบช่วงเวลาต้องไม่เกิน 1 ปี
    if ($startDate->diffInDays($endDate) > 365) {
        throw new \Exception('ช่วงวันที่ต้องไม่เกิน 1 ปี');
    }
}
}