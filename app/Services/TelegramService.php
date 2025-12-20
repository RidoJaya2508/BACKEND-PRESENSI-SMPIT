<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
    }

    public function sendMessage($chatId, $text)
    {
        if (!$this->botToken || !$chatId) {
            return;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
        }
    }

    public function sendNotification($chatId, $studentName, $className, $subjectName, $status, $time, $date)
    {
        if (!$this->botToken || !$chatId) {
            return;
        }

        $message = $this->formatMessage($studentName, $className, $subjectName, $status, $time, $date);

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification: ' . $e->getMessage());
        }
    }

    private function formatMessage($studentName, $className, $subjectName, $status, $time, $date)
    {
        $icon = 'ℹ️';
        $statusText = $status;
        $note = '';

        switch ($status) {
            case 'Hadir':
                $icon = '✅';
                $note = 'Terima kasih, ananda telah siap mengikuti pembelajaran.';
                break;
            case 'Terlambat':
                $icon = '⚠️';
                $note = 'Mohon perhatian, ananda datang terlambat.';
                break;
            case 'Sakit':
                $icon = '🏥';
                $note = 'Semoga lekas sembuh.';
                break;
            case 'Izin':
                $icon = '✉️';
                $note = 'Izin telah dicatat.';
                break;
            case 'Alpa':
            case 'Bolos':
                $icon = '❌';
                $note = 'Mohon konfirmasi ketidakhadiran ananda.';
                break;
        }

        return "{$icon} *Laporan Presensi Siswa*\n\n" .
               "Nama: *{$studentName}*\n" .
               "Kelas: {$className}\n" .
               "Mata Pelajaran: *{$subjectName}*\n" .
               "Tanggal: {$date}\n" .
               "Status: *{$status}*\n" .
               "Jam Pelajaran: {$time}\n\n" .
               "_{$note}_";
    }
}
