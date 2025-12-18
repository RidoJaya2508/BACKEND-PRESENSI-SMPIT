<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramTemp;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram Webhook Received', $update);

        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? '';
            $username = $message['from']['username'] ?? null;
            $firstName = $message['from']['first_name'] ?? null;

            if ($chatId && str_starts_with($text, '/start connect_')) {
                $token = substr($text, strlen('/start connect_'));
                
                // Save to temporary table
                TelegramTemp::updateOrCreate(
                    ['token' => $token],
                    [
                        'chat_id' => $chatId,
                        'username' => $username,
                        'first_name' => $firstName,
                    ]
                );

                $this->telegramService->sendMessage(
                    $chatId, 
                    "✅ *Perangkat Terhubung!*\n\nData Anda telah diterima sementara. Silakan lanjutkan proses penyimpanan data siswa di Website Admin.\n\nToken: `$token`"
                );
            } elseif ($chatId && $text === '/start') {
                 $this->telegramService->sendMessage(
                    $chatId, 
                    "👋 Halo! Saya adalah Bot Presensi SMPIT.\n\nUntuk menghubungkan akun, silakan scan QR Code yang ada di menu Data Siswa pada Website Admin."
                );
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
