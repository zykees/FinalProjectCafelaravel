<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LineRichMenuService
{
    protected $accessToken;

    public function __construct()
    {
        $this->accessToken = env('LINE_CHANNEL_ACCESS_TOKEN');
    }

    public function createRichMenu($data)
    {
        $client = new Client();
        try {
            $response = $client->post('https://api.line.me/v2/bot/richmenu', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode($data)
            ]);
            $result = json_decode($response->getBody(), true);
            // ตรวจสอบว่ามี richMenuId จริงหรือไม่
            if (!isset($result['richMenuId'])) {
                throw new \Exception('LINE API ไม่ได้ส่ง richMenuId กลับมา');
            }
            return $result;
        } catch (RequestException $e) {
            // ดึง error message จาก LINE API
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('สร้าง Rich Menu ไม่สำเร็จ: ' . $errorBody);
        }
    }

    public function uploadRichMenuImage($richMenuId, $imagePath)
    {
        $client = new Client();
        try {
            $response = $client->post("https://api.line.me/v2/bot/richmenu/{$richMenuId}/content", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type'  => 'image/png',
                ],
                'body' => fopen($imagePath, 'r')
            ]);
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('อัปโหลดรูปภาพ Rich Menu ไม่สำเร็จ: ' . $errorBody);
        }
    }

    public function setDefaultRichMenu($richMenuId)
    {
        $client = new Client();
        try {
            $response = $client->post("https://api.line.me/v2/bot/user/all/richmenu/{$richMenuId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ]
            ]);
            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('ตั้งค่า Default Rich Menu ไม่สำเร็จ: ' . $errorBody);
        }
    }

    public function getRichMenuList()
    {
        $client = new Client();
        try {
            $response = $client->get('https://api.line.me/v2/bot/richmenu/list', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('ดึงรายการ Rich Menu ไม่สำเร็จ: ' . $errorBody);
        }
    }

    public function getRichMenuImageUrl($richMenuId)
    {
        // คืน route proxy สำหรับแสดงรูป Rich Menu บนเว็บ
        return route('admin.line-richmenu.image', ['richMenuId' => $richMenuId]);
    }
}