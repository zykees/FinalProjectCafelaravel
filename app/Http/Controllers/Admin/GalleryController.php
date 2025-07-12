<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GalleryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Gallery::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $galleries = $query->latest()->paginate(12);
        return view('admin.gallery.index', compact('galleries'));
    }

    public function create()
    {
        return view('admin.gallery.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:active,inactive'
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
            try {
                // อัปโหลดไฟล์ไปยัง Cloudinary ผ่าน Laravel Filesystem
                $publicId = Storage::disk('cloudinary')->putFile('Projectcafe_Gallery', $file);
                Log::info('Cloudinary publicId: ' . $publicId);

                if ($publicId) {
                    $cloudName = env('CLOUDINARY_CLOUD_NAME');
                    $imageUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
                    $validated['image'] = $imageUrl;
                } else {
                    return back()->with('error', 'อัปโหลดรูปภาพไป Cloudinary ไม่สำเร็จ');
                }
            } catch (\Throwable $e) {
                Log::error('Cloudinary upload error: ' . $e->getMessage());
                return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        } else {
            Log::warning('No valid image file uploaded');
            return back()->with('error', 'กรุณาเลือกรูปภาพที่ถูกต้อง');
        }

        Gallery::create($validated);

        return redirect()
            ->route('admin.gallery.index')
            ->with('success', 'Gallery image added successfully');
    }

    public function edit(Gallery $gallery)
    {
        return view('admin.gallery.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'status' => 'required|in:active,inactive'
    ]);

    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        $file = $request->file('image');
        try {
            // ลบไฟล์เดิมออกจาก Cloudinary (ถ้ามี)
            if ($gallery->image) {
                // ดึง public_id จาก url เดิม
                $publicId = basename(parse_url($gallery->image, PHP_URL_PATH));
                Storage::disk('cloudinary')->delete('Projectcafe_Gallery/' . $publicId);
            }
            // อัปโหลดไฟล์ใหม่เข้าโฟลเดอร์ Projectcafe_Gallery
            $newPublicId = Storage::disk('cloudinary')->putFile('Projectcafe_Gallery', $file);
            if ($newPublicId) {
                $cloudName = env('CLOUDINARY_CLOUD_NAME');
                $imageUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/{$newPublicId}";
                $validated['image'] = $imageUrl;
            } else {
                return back()->with('error', 'อัปโหลดรูปภาพไป Cloudinary ไม่สำเร็จ');
            }
        } catch (\Throwable $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage());
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    $gallery->update($validated);

    return redirect()
        ->route('admin.gallery.index')
        ->with('success', 'Gallery image updated successfully');
}

   public function destroy(Gallery $gallery)
{
    if ($gallery->image) {
        // ดึง public_id จาก url เดิม
        $publicId = basename(parse_url($gallery->image, PHP_URL_PATH));
        Storage::disk('cloudinary')->delete('Projectcafe_Gallery/' . $publicId);
    }

    $gallery->delete();

    return redirect()
        ->route('admin.gallery.index')
        ->with('success', 'Gallery image deleted successfully');
}
}