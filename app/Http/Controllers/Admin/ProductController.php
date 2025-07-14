<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category_name
        if ($request->filled('category_name')) {
            $query->where('category_name', $request->category_name);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%");
            });
        }

        // Sort by
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(10);

        // Get all unique category names for filter/autocomplete
        $categoryNames = Product::select('category_name')
            ->distinct()
            ->whereNotNull('category_name')
            ->pluck('category_name')
            ->toArray();

        // Get statistics
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'available')->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
            'total_categories' => count($categoryNames),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $products,
                'stats' => $stats
            ]);
        }

        return view('admin.products.index', compact('products', 'categoryNames', 'stats'));
    }

    public function create()
    {
        $categoryNames = Product::select('category_name')
            ->distinct()
            ->whereNotNull('category_name')
            ->pluck('category_name')
            ->toArray();

        return view('admin.products.create', compact('categoryNames'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:products',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'minimum_stock' => 'required|integer|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_name' => 'nullable|string|max:255',
                'status' => 'required|in:available,unavailable',
                'discount_percent' => 'nullable|integer|min:0|max:100'
            ]);

            $validated['discount_percent'] = (int)($request->input('discount_percent', 0));
            $validated['minimum_stock'] = (int)($request->input('minimum_stock', 1));

            if ($validated['stock'] < $validated['minimum_stock']) {
                return back()
                    ->withInput()
                    ->with('error', 'จำนวนในคลังต้องมากกว่าหรือเท่ากับจำนวนขั้นต่ำ');
            }

            DB::beginTransaction();

            $data = $validated;
            $data['slug'] = Str::slug($request->name);
            $data['featured'] = $request->has('featured');

            if ($request->hasFile('image')) {
    $file = $request->file('image');
    $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
        'folder' => 'Projectcafe_Products'
    ])->getSecurePath();
    if ($uploadedFileUrl) {
        $data['image'] = $uploadedFileUrl;
    } else {
        DB::rollBack();
        return back()->withInput()->with('error', 'อัปโหลดรูปภาพไป Cloudinary ไม่สำเร็จ');
    }
}

            Product::create($data);

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'เพิ่มสินค้าสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categoryNames = Product::select('category_name')
            ->distinct()
            ->whereNotNull('category_name')
            ->pluck('category_name')
            ->toArray();

        return view('admin.products.edit', compact('product', 'categoryNames'));
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:products,name,' . $product->id,
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'minimum_stock' => 'required|integer|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_name' => 'nullable|string|max:255',
                'status' => 'required|in:available,unavailable',
                'discount_percent' => 'nullable|integer|min:0|max:100'
            ]);

            $validated['discount_percent'] = (int)($request->input('discount_percent', 0));
            $validated['minimum_stock'] = (int)($request->input('minimum_stock', 1));

            if ($validated['stock'] < $validated['minimum_stock']) {
                return back()
                    ->withInput()
                    ->with('error', 'จำนวนในคลังต้องมากกว่าหรือเท่ากับจำนวนขั้นต่ำ');
            }

            DB::beginTransaction();

            $data = $validated;
            $data['slug'] = Str::slug($request->name);
            $data['featured'] = $request->has('featured');

            if ($request->hasFile('image')) {
                // ลบไฟล์เดิมจาก Cloudinary ถ้ามีและเป็น Cloudinary URL
                if ($product->image && str_starts_with($product->image, 'http')) {
                    $publicId = basename(parse_url($product->image, PHP_URL_PATH));
                    Storage::disk('cloudinary')->delete('Projectcafe_Products/' . $publicId);
                }
                $file = $request->file('image');
                $publicId = Storage::disk('cloudinary')->putFile('Projectcafe_Products', $file);
                if ($publicId) {
                    $cloudName = env('CLOUDINARY_CLOUD_NAME');
                    $data['image'] = "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
                } else {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'อัปโหลดรูปภาพไป Cloudinary ไม่สำเร็จ');
                }
            }

            $product->update($data);

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'อัพเดตสินค้าสำเร็จ');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            // ลบรูปภาพจาก Cloudinary ถ้ามีและเป็น Cloudinary URL
            if ($product->image && str_starts_with($product->image, 'http')) {
                $publicId = basename(parse_url($product->image, PHP_URL_PATH));
                Storage::disk('cloudinary')->delete('Projectcafe_Products/' . $publicId);
            }

            // ลบข้อมูลจาก database จริงๆ
            $product->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'ลบสินค้าสำเร็จ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStock(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'stock' => 'required|integer|min:0',
                'stock_note' => 'nullable|string|max:255'
            ]);

            $product->update($validated);

            return back()->with('success', 'อัพเดตสต็อกสำเร็จ');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Product $product)
    {
        try {
            $product->status = $product->status === 'available' ? 'unavailable' : 'available';
            $product->save();

            return response()->json([
                'success' => true,
                'status' => $product->status,
                'status_text' => $product->status === 'available' ? 'พร้อมขาย' : 'ไม่พร้อมขาย',
                'message' => 'เปลี่ยนสถานะสำเร็จ'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}