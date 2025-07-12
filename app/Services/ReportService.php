<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Booking;
use App\Models\Promotion;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportService
{

public function getQuickStats()
{
    try {
        $lastMonth = Carbon::now()->subDays(30);
        $previousMonth = Carbon::now()->subDays(60)->subDays(30);
        
        // คำนวณยอดขาย 30 วันล่าสุด
        $currentSales = Order::where('created_at', '>=', $lastMonth)
            ->where('status', 'completed')
            ->sum('total_amount');
            
        // คำนวณยอดขายเดือนก่อนหน้า
        $previousSales = Order::whereBetween('created_at', [$previousMonth, $lastMonth])
            ->where('status', 'completed')
            ->sum('total_amount');
            
        // คำนวณการจอง 30 วันล่าสุด
        $currentBookings = Booking::where('created_at', '>=', $lastMonth)->count();
        
        // คำนวณการจองเดือนก่อนหน้า
        $previousBookings = Booking::whereBetween('created_at', [$previousMonth, $lastMonth])->count();
        
        // คำนวณเปอร์เซ็นต์การเติบโต
        $salesGrowth = $previousSales > 0 ? (($currentSales - $previousSales) / $previousSales) * 100 : 0;
        $bookingsGrowth = $previousBookings > 0 ? (($currentBookings - $previousBookings) / $previousBookings) * 100 : 0;
        
        return [
            'sales' => $currentSales,
            'sales_growth' => $salesGrowth,
            'bookings' => $currentBookings,
            'bookings_growth' => $bookingsGrowth,
            'promotions' => Promotion::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count()
        ];
    } catch (\Exception $e) {
        Log::error('Error in getQuickStats: ' . $e->getMessage());
        return [
            'sales' => 0,
            'sales_growth' => 0,
            'bookings' => 0,
            'bookings_growth' => 0,
            'promotions' => 0
        ];
    }
}
    public function generateSalesReport($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                      ->where('status', 'completed')
                      ->with(['items.product'])
                      ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        
        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'average_order' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
            'daily_sales' => [
                'dates' => $orders->groupBy(fn($order) => $order->created_at->format('Y-m-d'))
                                ->keys()->toArray(),
                'amounts' => $orders->groupBy(fn($order) => $order->created_at->format('Y-m-d'))
                                  ->map->sum('total_amount')->values()->toArray()
            ],
            'top_products' => $this->getTopProducts($orders),
            'detailed_data' => $this->getSalesDetailedData($orders),
            'top_product_revenue' => $this->getTopProductRevenue($orders)
        ];
    }

    public function generateBookingsReport($startDate, $endDate)
    {
        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'total_bookings' => $bookings->count(),
            'confirmed_bookings' => $bookings->where('status', 'confirmed')->count(),
            'pending_bookings' => $bookings->where('status', 'pending')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'daily_bookings' => [
                'dates' => $bookings->groupBy(fn($booking) => $booking->created_at->format('Y-m-d'))
                                  ->keys()->toArray(),
                'counts' => $bookings->groupBy(fn($booking) => $booking->created_at->format('Y-m-d'))
                                   ->map->count()->values()->toArray()
            ],
            'detailed_data' => $this->getBookingsDetailedData($bookings)
        ];
    }

public function generatePromotionsReport($startDate, $endDate)
{
    try {
        // ดึง promotions ที่มีช่วงเวลาตรงกับที่กำหนด
        $promotions = Promotion::with(['orders' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->where('status', 'completed');
        }])->get();

        // นับ active promotions
        $activePromotions = $promotions->filter(function($promotion) {
            $now = now();
            return $promotion->status === 'active' 
                && $promotion->start_date <= $now 
                && $promotion->end_date >= $now;
        })->count();

        // คำนวณข้อมูลสรุป
        $totalUses = $promotions->sum(function($promotion) {
            return $promotion->orders->count();
        });

        $totalSavings = $promotions->sum(function($promotion) {
            return $promotion->orders->sum('discount_amount');
        });

        // จัดเรียง top promotions
        $sortedPromotions = $promotions->sortByDesc(function($promotion) {
            return $promotion->orders->count();
        })->take(5);

        return [
            'active_promotions' => $activePromotions,
            'total_uses' => $totalUses,
            'total_savings' => $totalSavings,
            'average_discount' => $totalUses > 0 ? $totalSavings / $totalUses : 0,
            'usage_trend' => $this->getPromotionUsageTrend($promotions, $startDate, $endDate),
            'top_promotions' => [
                'names' => $sortedPromotions->pluck('name')->toArray(),
                'uses' => $sortedPromotions->map(function($promotion) {
                    return $promotion->orders->count();
                })->toArray()
            ],
            'detailed_data' => $this->getPromotionsDetailedData($promotions)
        ];
    } catch (\Exception $e) {
        Log::error('Error in generatePromotionsReport: ' . $e->getMessage());
        return [
            'active_promotions' => 0,
            'total_uses' => 0,
            'total_savings' => 0,
            'average_discount' => 0,
            'usage_trend' => ['dates' => [], 'counts' => []],
            'top_promotions' => ['names' => [], 'uses' => []],
            'detailed_data' => []
        ];
    }
}

    private function getTopProducts($orders)
    {
        $products = collect();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $products->push([
                    'name' => $item->product->name,
                    'revenue' => $item->quantity * $item->price
                ]);
            }
        }

        $topProducts = $products->groupBy('name')
            ->map(function ($items) {
                return [
                    'revenue' => $items->sum('revenue')
                ];
            })
            ->sortByDesc('revenue')
            ->take(5);

        return [
            'names' => $topProducts->keys()->toArray(),
            'revenues' => $topProducts->pluck('revenue')->toArray()
        ];
    }

    private function getSalesDetailedData($orders)
    {
        return $orders->groupBy(fn($order) => $order->created_at->format('Y-m-d'))
            ->map(function ($dayOrders) {
                $total = $dayOrders->sum('total_amount');
                return [
                    'date' => $dayOrders->first()->created_at->format('Y-m-d'),
                    'orders' => $dayOrders->count(),
                    'total' => $total,
                    'average' => $dayOrders->count() > 0 ? $total / $dayOrders->count() : 0
                ];
            })->values()->toArray();
    }
     private function getTopProductRevenue($orders)
    {
        $products = collect();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $products->push([
                    'revenue' => $item->quantity * $item->price
                ]);
            }
        }
        
        return $products->sum('revenue');
    }

    private function getBookingsDetailedData($bookings)
    {
        return $bookings->groupBy(fn($booking) => $booking->created_at->format('Y-m-d'))
            ->map(function ($dayBookings) {
                return [
                    'date' => $dayBookings->first()->created_at->format('Y-m-d'),
                    'total' => $dayBookings->count(),
                    'confirmed' => $dayBookings->where('status', 'confirmed')->count(),
                    'pending' => $dayBookings->where('status', 'pending')->count(),
                    'cancelled' => $dayBookings->where('status', 'cancelled')->count()
                ];
            })->values()->toArray();
    }

    private function calculateTotalSavings($promotions)
    {
        $totalSavings = 0;
        foreach ($promotions as $promotion) {
            $totalSavings += $promotion->orders->sum('discount_amount');
        }
        return $totalSavings;
    }

    private function calculateAverageDiscount($promotions)
    {
        $totalUses = $promotions->sum('orders_count');
        if ($totalUses === 0) {
            return 0;
        }
        
        $totalSavings = $this->calculateTotalSavings($promotions);
        return $totalSavings / $totalUses;
    }

   private function getPromotionUsageTrend($promotions, $startDate, $endDate)
{
    try {
        $dailyUses = collect();
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // สร้าง array วันที่
        while ($current <= $end) {
            $currentDate = $current->format('Y-m-d');
            $dailyUses[$currentDate] = 0;
            $current->addDay();
        }

        // นับการใช้งานแต่ละวัน
        foreach ($promotions as $promotion) {
            foreach ($promotion->orders as $order) {
                $date = $order->created_at->format('Y-m-d');
                if (isset($dailyUses[$date])) {
                    $dailyUses[$date]++;
                }
            }
        }

        return [
            'dates' => array_keys($dailyUses->toArray()),
            'counts' => array_values($dailyUses->toArray())
        ];
    } catch (\Exception $e) {
        Log::error('Error in getPromotionUsageTrend: ' . $e->getMessage());
        return ['dates' => [], 'counts' => []];
    }
}

private function getPromotionsDetailedData($promotions) 
{
    try {
        return $promotions->map(function ($promotion) {
            $orders = $promotion->orders;
            $totalSavings = $orders->sum('discount_amount');
            $usageCount = $orders->count();
            
            return [
                'name' => $promotion->name,
                'uses' => $usageCount,
                'total_savings' => $totalSavings,
                'average_discount' => $usageCount > 0 ? $totalSavings / $usageCount : 0,
                'status' => $promotion->status
            ];
        })->toArray();
    } catch (\Exception $e) {
        Log::error('Error in getPromotionsDetailedData: ' . $e->getMessage());
        return [];
    }
}
}
    // ... เพิ่มเติม private methods อื่นๆ ตามต้องการ
