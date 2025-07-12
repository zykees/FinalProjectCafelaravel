<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // รับข้อมูล event จาก LINE
        $events = $request->input('events', []);

        foreach ($events as $event) {
            // ตัวอย่าง: ตอบกลับข้อความอัตโนมัติ
            if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
                $replyToken = $event['replyToken'];
                $userMessage = $event['message']['text'];

                $this->replyText($replyToken, "คุณพิมพ์ว่า: " . $userMessage);
            }
             Log::info('LINE Webhook', ['body' => $request->all()]);
        return response('OK', 200);
        }
        Log::info('LINE Webhook', ['body' => $request->all()]);

        // ตอบกลับ 200 OK ให้ LINE
        return response('OK', 200);
    }

    protected function replyText($replyToken, $text)
    {
        $httpClient = new \GuzzleHttp\Client();
        $httpClient->post('https://api.line.me/v2/bot/message/reply', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'replyToken' => $replyToken,
                'messages' => [
                    ['type' => 'text', 'text' => $text]
                ]
            ]
        ]);
    }
}