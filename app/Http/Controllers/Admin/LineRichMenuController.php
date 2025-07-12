<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LineRichMenuService;
use App\Models\RichMenu;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
class LineRichMenuController extends Controller
{
    public function index()
    {
        $richMenus = RichMenu::orderByDesc('id')->get();
        return view('admin.line-richmenu.index', compact('richMenus'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'chatBarText' => 'required|string|max:255',
                'button_uris' => 'required|array|size:6',
                'button_uris.*' => 'required|url',
                'image' => 'required|image|mimes:png|max:2048',
            ]);

            $service = new LineRichMenuService();

            // 1. สร้าง Rich Menu บน LINE (6 ปุ่ม)
            $areas = [];
            $width = 2500;
            $height = 843;
            $buttonWidth = intval($width / 6);
            for ($i = 0; $i < 6; $i++) {
                $areas[] = [
                    "bounds" => [
                        "x" => $i * $buttonWidth,
                        "y" => 0,
                        "width" => $buttonWidth,
                        "height" => $height
                    ],
                    "action" => [
                        "type" => "uri",
                        "uri" => $request->button_uris[$i]
                    ]
                ];
            }
            $data = [
                "size" => ["width" => $width, "height" => $height],
                "selected" => true,
                "name" => $request->name,
                "chatBarText" => $request->chatBarText,
                "areas" => $areas
            ];
            $result = $service->createRichMenu($data);

            if (!isset($result['richMenuId'])) {
                return back()->with('error', 'สร้าง Rich Menu ไม่สำเร็จ');
            }

            $richMenuId = $result['richMenuId'];

$imageFile = $request->file('image');
$imagePath = $imageFile->store('richmenu', 'public');
$mainImagePath = storage_path('app/public/' . $imagePath);

// ตรวจสอบขนาดภาพ
list($imgWidth, $imgHeight) = getimagesize($mainImagePath);
if ($imgWidth != 2500 || $imgHeight != 843) {
    @unlink($mainImagePath);
    return back()->with('error', 'กรุณาอัปโหลด PNG ขนาด 2500x843 px เท่านั้น');
}

// ตรวจสอบ MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $mainImagePath);
finfo_close($finfo);
if ($mimeType !== 'image/png') {
    @unlink($mainImagePath);
    return back()->with('error', 'ไฟล์ที่อัปโหลดไม่ใช่ PNG');
}

// 3. อัปโหลดไฟล์ไป LINE Rich Menu ด้วย cURL
$token = env('LINE_CHANNEL_ACCESS_TOKEN');
$richMenuId = $result['richMenuId'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/bot/richmenu/{$richMenuId}/content");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token}",
    "Content-Type: image/png"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($mainImagePath));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode !== 200) {
    Log::error('RichMenu: LINE API Upload Error', [
        'httpCode' => $httpCode,
        'response' => $response,
        'error' => $error,
        'richMenuId' => $richMenuId,
        'mainImagePath' => $mainImagePath
    ]);
    return back()->with('error', 'อัปโหลดรูปภาพ Rich Menu ไป LINE ไม่สำเร็จ: ' . $response);
}

            // 4. ตั้งเป็น default
            try {
                $service->setDefaultRichMenu($richMenuId);
            } catch (\Exception $e) {
                return redirect()->route('admin.line-richmenu.index')->with('error', $e->getMessage());
            }

            // 5. บันทึกข้อมูลลง MySQL
            RichMenu::create([
                'rich_menu_id'   => $richMenuId,
                'name'           => $request->name,
                'chat_bar_text'  => $request->chatBarText,
                'button_uris'    => $request->button_uris,
                'image_url'      => asset('storage/' . $imagePath),
            ]);

            return redirect()->route('admin.line-richmenu.index')->with('success', 'สร้าง Rich Menu สำเร็จ (อัปโหลดไป LINE แล้ว)');
        }

        // GET: แสดงฟอร์ม
        return view('admin.line-richmenu.create');
    }

    public function setDefault($richMenuId)
    {
        $service = new LineRichMenuService();
        try {
            $service->setDefaultRichMenu($richMenuId);
            return redirect()->route('admin.line-richmenu.index')->with('success', 'ตั้ง Rich Menu นี้เป็นค่าเริ่มต้นเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->route('admin.line-richmenu.index')->with('error', $e->getMessage());
        }
    }

    public function delete($richMenuId)
    {
        $service = new LineRichMenuService();
        $client = new \GuzzleHttp\Client();
        $client->delete("https://api.line.me/v2/bot/richmenu/{$richMenuId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
            ]
        ]);
        // ลบในฐานข้อมูลด้วย
        $menu = RichMenu::where('rich_menu_id', $richMenuId)->first();
        if ($menu && $menu->image_url) {
            // ลบไฟล์ภาพใน storage ด้วย
            $storagePath = str_replace(asset('storage') . '/', '', $menu->image_url);
            Storage::disk('public')->delete($storagePath);
        }
        RichMenu::where('rich_menu_id', $richMenuId)->delete();
        return redirect()->route('admin.line-richmenu.index')->with('success', 'ลบ Rich Menu สำเร็จ');
    }
}