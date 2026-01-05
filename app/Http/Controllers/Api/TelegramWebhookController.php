<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramTemp;
use App\Models\Student;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    private function getMainMenu($isRegistered = false)
    {
        if ($isRegistered) {
            return [
                [
                    ['text' => '📊 Status Akun', 'callback_data' => 'menu_status'],
                    ['text' => '❓ Bantuan', 'callback_data' => 'menu_help']
                ]
            ];
        } else {
            return [
                [
                    ['text' => '📝 Cara Pendaftaran', 'callback_data' => 'menu_register'],
                    ['text' => '❓ Bantuan', 'callback_data' => 'menu_help']
                ]
            ];
        }
    }

    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram Webhook Received', $update);

        // Handle callback queries (button presses)
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
            return response()->json(['status' => 'ok']);
        }

        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? '';
            $username = $message['from']['username'] ?? null;
            $firstName = $message['from']['first_name'] ?? null;

            Log::info('Processing Telegram message', [
                'chat_id' => $chatId,
                'text' => $text,
                'username' => $username,
                'first_name' => $firstName
            ]);

            // Handle /start connect_XXX for QR connection
            if ($chatId && str_starts_with($text, '/start connect_')) {
                $token = substr($text, strlen('/start connect_'));
                
                Log::info('Telegram Connection Request', [
                    'token' => $token,
                    'chat_id' => $chatId,
                    'username' => $username,
                    'first_name' => $firstName
                ]);
                
                // Save to temporary table
                TelegramTemp::updateOrCreate(
                    ['token' => $token],
                    [
                        'chat_id' => (string) $chatId,
                        'username' => $username,
                        'first_name' => $firstName,
                    ]
                );

                Log::info('Telegram Connection Saved', ['token' => $token, 'chat_id' => $chatId]);

                $success = $this->telegramService->sendMessage(
                    $chatId, 
                    "✅ *Perangkat Terhubung!*\n\n" .
                    "Data Anda telah diterima dan tersimpan.\n\n" .
                    "🔄 Sistem akan otomatis mengisi ID Telegram dalam beberapa detik.\n\n" .
                    "📱 Chat ID Anda: `$chatId`\n" .
                    "👤 Token: `$token`\n\n" .
                    "Silakan tutup jendela ini dan kembali ke Website Admin untuk melanjutkan proses penyimpanan data siswa.",
                    'Markdown'
                );

                Log::info('Connection message sent', ['success' => $success]);
            } 
            // Handle plain /start for general info
            elseif ($chatId && ($text === '/start' || trim($text) === '/start')) {
                Log::info('Handling /start command', ['chat_id' => $chatId]);
                
                // Check if this chat_id is already registered
                $student = Student::where('parent_telegram_id', (string) $chatId)->first();
                
                $keyboard = $this->telegramService->createInlineKeyboard($this->getMainMenu($student ? true : false));
                
                if ($student) {
                    // User already registered
                    $success = $this->telegramService->sendMessage(
                        $chatId, 
                        "✅ *Akun Sudah Terhubung!*\n\n" .
                        "👋 Halo, *{$student->parent_name}*\n\n" .
                        "📚 *Data Siswa Terdaftar:*\n" .
                        "• Nama: *{$student->name}*\n" .
                        "• NIS: `{$student->nis}`\n" .
                        "• Kelas: *" . ($student->classGroup ? $student->classGroup->name : '-') . "*\n\n" .
                        "🔔 *Status:* Notifikasi Aktif\n" .
                        "📱 Chat ID: `$chatId`\n\n" .
                        "Anda akan menerima notifikasi presensi siswa secara real-time.\n\n" .
                        "💡 Gunakan menu di bawah atau ketik /menu untuk navigasi.",
                        'Markdown',
                        $keyboard
                    );
                } else {
                    // User not registered - show registration options
                    $success = $this->telegramService->sendMessage(
                        $chatId, 
                        "👋 *Selamat Datang di Bot Presensi SMPIT!*\n\n" .
                        "🎓 Saya adalah asisten untuk sistem presensi siswa SMP IT Mathla'un Nawakartika.\n\n" .
                        "⚠️ *Akun Anda Belum Terhubung*\n\n" .
                        "*Fitur Bot:*\n" .
                        "• 📩 Notifikasi presensi siswa real-time\n" .
                        "• 🔔 Update kehadiran langsung ke orang tua\n" .
                        "• 📊 Laporan kehadiran harian\n\n" .
                        "*Cara Menghubungkan Akun:*\n\n" .
                        "*Metode 1: Via Website (Direkomendasikan)*\n" .
                        "1. Buka Website Admin Presensi\n" .
                        "2. Masuk ke menu *Data Siswa*\n" .
                        "3. Tambah/Edit data siswa\n" .
                        "4. Klik tombol *QR Code* di kolom ID Telegram\n" .
                        "5. Scan QR Code yang muncul\n\n" .
                        "5. Scan QR Code yang muncul\n\n" .
                        "*Metode 2: Via Telegram (Alternatif)*\n" .
                        "Kirim pesan dengan format:\n" .
                        "`/daftar NIS_SISWA`\n\n" .
                        "Contoh: `/daftar 12345`\n\n" .
                        "📱 Chat ID Anda: `$chatId`\n\n" .
                        "💡 Gunakan menu di bawah atau ketik /menu untuk navigasi.",
                        'Markdown',
                        $keyboard
                    );
                }

                Log::info('Welcome message sent', ['success' => $success, 'chat_id' => $chatId, 'is_registered' => isset($student)]);
            }
            // Handle /menu command
            elseif ($chatId && ($text === '/menu' || trim($text) === '/menu')) {
                Log::info('Handling /menu command', ['chat_id' => $chatId]);
                
                $student = Student::where('parent_telegram_id', (string) $chatId)->first();
                $keyboard = $this->telegramService->createInlineKeyboard($this->getMainMenu($student ? true : false));
                
                $this->telegramService->sendMessage(
                    $chatId,
                    "🎯 *Menu Utama*\n\n" .
                    "Pilih menu di bawah ini:\n\n" .
                    "*Perintah Tersedia:*\n" .
                    "• `/start` - Informasi awal\n" .
                    "• `/menu` - Tampilkan menu ini\n" .
                    "• `/daftar NIS` - Pendaftaran akun\n" .
                    "• `/help` - Bantuan lengkap\n\n" .
                    "📱 Status: " . ($student ? "*Terhubung* ✅" : "*Belum Terhubung* ⚠️"),
                    'Markdown',
                    $keyboard
                );
            }
            // Handle /daftar command for registration via Telegram
            elseif ($chatId && preg_match('/^\/daftar\s+(\d+)$/i', trim($text), $matches)) {
                $nis = $matches[1];
                Log::info('Handling /daftar command', ['chat_id' => $chatId, 'nis' => $nis]);
                
                // Check if student exists with this NIS
                $student = Student::where('nis', $nis)->first();
                
                if (!$student) {
                    $this->telegramService->sendMessage(
                        $chatId,
                        "❌ *NIS Tidak Ditemukan*\n\n" .
                        "NIS `$nis` tidak terdaftar dalam sistem.\n\n" .
                        "Pastikan NIS yang Anda masukkan benar.\n\n" .
                        "*Format:* `/daftar NIS_SISWA`\n" .
                        "*Contoh:* `/daftar 12345`\n\n" .
                        "_Hubungi admin sekolah jika masalah berlanjut._",
                        'Markdown'
                    );
                } elseif ($student->parent_telegram_id && $student->parent_telegram_id !== (string) $chatId) {
                    // Already registered to another Telegram account
                    $this->telegramService->sendMessage(
                        $chatId,
                        "⚠️ *Sudah Terdaftar*\n\n" .
                        "Siswa dengan NIS `$nis` sudah terhubung ke akun Telegram lain.\n\n" .
                        "📚 *Data Siswa:*\n" .
                        "• Nama: *{$student->name}*\n" .
                        "• Orang Tua: *{$student->parent_name}*\n\n" .
                        "Jika Anda ingin mengganti koneksi, silakan hubungi admin sekolah untuk mereset ID Telegram terlebih dahulu.",
                        'Markdown'
                    );
                } elseif ($student->parent_telegram_id === (string) $chatId) {
                    // Already registered to this account
                    $this->telegramService->sendMessage(
                        $chatId,
                        "✅ *Sudah Terhubung*\n\n" .
                        "Akun Anda sudah terhubung dengan siswa ini.\n\n" .
                        "📚 *Data Siswa:*\n" .
                        "• Nama: *{$student->name}*\n" .
                        "• NIS: `{$student->nis}`\n" .
                        "• Kelas: *" . ($student->classGroup ? $student->classGroup->name : '-') . "*\n\n" .
                        "🔔 Notifikasi presensi sudah aktif.",
                        'Markdown'
                    );
                } else {
                    // Register this Telegram ID
                    $student->parent_telegram_id = (string) $chatId;
                    $student->save();
                    
                    Log::info('Student registered via Telegram', [
                        'nis' => $nis,
                        'chat_id' => $chatId,
                        'student_id' => $student->id
                    ]);
                    
                    $this->telegramService->sendMessage(
                        $chatId,
                        "🎉 *Pendaftaran Berhasil!*\n\n" .
                        "Akun Telegram Anda berhasil terhubung!\n\n" .
                        "📚 *Data Siswa Terdaftar:*\n" .
                        "• Nama: *{$student->name}*\n" .
                        "• NIS: `{$student->nis}`\n" .
                        "• Orang Tua: *{$student->parent_name}*\n" .
                        "• Kelas: *" . ($student->classGroup ? $student->classGroup->name : '-') . "*\n\n" .
                        "🔔 *Notifikasi Aktif*\n" .
                        "📱 Chat ID: `$chatId`\n\n" .
                        "Anda akan menerima notifikasi presensi siswa secara real-time.\n\n" .
                        "✨ Terima kasih telah menggunakan layanan kami!",
                        'Markdown'
                    );
                }
            }
            // Handle invalid /daftar format
            elseif ($chatId && str_starts_with(trim($text), '/daftar')) {
                $this->telegramService->sendMessage(
                    $chatId,
                    "❌ *Format Salah*\n\n" .
                    "Gunakan format yang benar:\n" .
                    "`/daftar NIS_SISWA`\n\n" .
                    "*Contoh:*\n" .
                    "`/daftar 12345`\n\n" .
                    "Pastikan NIS hanya berisi angka tanpa spasi.",
                    'Markdown'
                );
            }
            // Handle /help command
            elseif ($chatId && ($text === '/help' || trim($text) === '/help')) {
                // Check if user is registered
                $student = Student::where('parent_telegram_id', (string) $chatId)->first();
                
                if ($student) {
                    $this->telegramService->sendMessage(
                        $chatId,
                        "📖 *Bantuan - Bot Presensi SMPIT*\n\n" .
                        "✅ Akun Anda: *Terhubung*\n\n" .
                        "*Perintah Tersedia:*\n" .
                        "• `/start` - Info akun & status koneksi\n" .
                        "• `/help` - Tampilkan bantuan ini\n\n" .
                        "*Data Terhubung:*\n" .
                        "• Siswa: *{$student->name}*\n" .
                        "• NIS: `{$student->nis}`\n" .
                        "• Kelas: *" . ($student->classGroup ? $student->classGroup->name : '-') . "*\n\n" .
                        "🔔 Anda akan menerima notifikasi presensi secara otomatis.\n\n" .
                        "_Untuk bantuan lebih lanjut, hubungi admin sekolah._",
                        'Markdown'
                    );
                } else {
                    $this->telegramService->sendMessage(
                        $chatId,
                        "📖 *Bantuan - Bot Presensi SMPIT*\n\n" .
                        "⚠️ Akun Anda: *Belum Terhubung*\n\n" .
                        "*Perintah Tersedia:*\n" .
                        "• `/start` - Info & cara menghubungkan akun\n" .
                        "• `/daftar NIS` - Daftar menggunakan NIS siswa\n" .
                        "• `/help` - Tampilkan bantuan ini\n\n" .
                        "*Contoh Pendaftaran:*\n" .
                        "`/daftar 12345`\n\n" .
                        "*Atau gunakan QR Code:*\n" .
                        "Scan QR Code dari Website Admin Presensi\n\n" .
                        "_Untuk bantuan lebih lanjut, hubungi admin sekolah._",
                        'Markdown'
                    );
                }
            }
            // Handle unknown commands
            elseif ($chatId && str_starts_with($text, '/')) {
                Log::info('Unknown command received', ['command' => $text, 'chat_id' => $chatId]);
                
                $this->telegramService->sendMessage(
                    $chatId,
                    "❓ *Perintah Tidak Dikenal*\n\n" .
                    "Maaf, saya tidak mengerti perintah tersebut.\n\n" .
                    "*Perintah yang tersedia:*\n" .
                    "• `/start` - Informasi & status akun\n" .
                    "• `/daftar NIS` - Pendaftaran via NIS\n" .
                    "• `/help` - Bantuan\n\n" .
                    "Gunakan `/help` untuk informasi lebih detail.",
                    'Markdown'
                );
            }
            // Handle regular text messages (non-command)
            elseif ($chatId && !empty($text) && !str_starts_with($text, '/')) {
                Log::info('Regular message received', ['text' => $text, 'chat_id' => $chatId]);
                
                // Check if user is registered
                $student = Student::where('parent_telegram_id', (string) $chatId)->first();
                
                if ($student) {
                    // User is registered - provide helpful guidance
                    $this->telegramService->sendMessage(
                        $chatId,
                        "💬 *Halo, {$student->parent_name}!*\n\n" .
                        "Saya adalah bot untuk notifikasi presensi, bukan untuk percakapan.\n\n" .
                        "✅ *Akun Anda sudah terhubung dengan:*\n" .
                        "• Siswa: *{$student->name}*\n" .
                        "• NIS: `{$student->nis}`\n" .
                        "• Kelas: *" . ($student->classGroup ? $student->classGroup->name : '-') . "*\n\n" .
                        "🔔 Anda akan menerima notifikasi presensi secara otomatis.\n\n" .
                        "*Perintah yang dapat digunakan:*\n" .
                        "• `/start` - Lihat status akun\n" .
                        "• `/help` - Bantuan lengkap\n\n" .
                        "_Untuk pertanyaan lain, silakan hubungi admin sekolah._",
                        'Markdown'
                    );
                } else {
                    // User not registered - guide them to register
                    $this->telegramService->sendMessage(
                        $chatId,
                        "💬 *Halo!*\n\n" .
                        "Saya adalah bot untuk notifikasi presensi siswa SMPIT.\n\n" .
                        "⚠️ *Akun Anda belum terhubung.*\n\n" .
                        "Untuk menerima notifikasi presensi, silakan daftar terlebih dahulu:\n\n" .
                        "*Cara Pendaftaran:*\n\n" .
                        "1️⃣ *Via Telegram:*\n" .
                        "   Kirim: `/daftar NIS_SISWA`\n" .
                        "   Contoh: `/daftar 12345`\n\n" .
                        "2️⃣ *Via Website:*\n" .
                        "   • Login ke Website Admin Presensi\n" .
                        "   • Buka menu Data Siswa\n" .
                        "   • Scan QR Code di kolom Telegram\n\n" .
                        "*Perintah yang tersedia:*\n" .
                        "• `/start` - Informasi lengkap\n" .
                        "• `/menu` - Menu navigasi\n" .
                        "• `/help` - Bantuan\n\n" .
                        "📱 Chat ID Anda: `$chatId`",
                        'Markdown'
                    );
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $data = $callbackQuery['data'] ?? '';
        $callbackId = $callbackQuery['id'] ?? null;

        Log::info('Handling callback query', [
            'chat_id' => $chatId,
            'data' => $data
        ]);

        if (!$chatId) {
            return;
        }

        $student = Student::where('parent_telegram_id', (string) $chatId)->first();

        switch ($data) {
            case 'menu_status':
                if ($student) {
                    $this->telegramService->sendMessage(
                        $chatId,
                        "📊 *Status Akun Anda*\n\n" .
                        "✅ *Akun Terhubung*\n\n" .
                        "👤 Nama Orang Tua: *{$student->parent_name}*\n\n" .
                        "📚 *Data Siswa:*\n" .
                        "• Nama: *{$student->name}*\n" .
                        "• NIS: `{$student->nis}`\n" .
                        "• Kelas: *" . ($student->classGroup ? $student->classGroup->name : '-') . "*\n\n" .
                        "🔔 *Notifikasi:* Aktif\n" .
                        "📱 Chat ID: `$chatId`\n\n" .
                        "Anda akan menerima notifikasi presensi secara real-time.",
                        'Markdown',
                        $this->telegramService->createInlineKeyboard([
                            [['text' => '🔙 Kembali ke Menu', 'callback_data' => 'menu_main']]
                        ])
                    );
                }
                break;

            case 'menu_register':
                $this->telegramService->sendMessage(
                    $chatId,
                    "📝 *Cara Pendaftaran*\n\n" .
                    "*Metode 1: Via Telegram (Cepat)*\n" .
                    "Kirim pesan dengan format:\n" .
                    "`/daftar NIS_SISWA`\n\n" .
                    "Contoh: `/daftar 12345`\n\n" .
                    "*Metode 2: Via Website*\n" .
                    "1. Login ke Website Admin Presensi\n" .
                    "2. Buka menu *Data Siswa*\n" .
                    "3. Klik tombol *QR Code*\n" .
                    "4. Scan QR Code yang muncul\n\n" .
                    "💡 Metode Telegram lebih cepat dan praktis!",
                    'Markdown',
                    $this->telegramService->createInlineKeyboard([
                        [['text' => '🔙 Kembali ke Menu', 'callback_data' => 'menu_main']]
                    ])
                );
                break;

            case 'menu_help':
                $helpText = "❓ *Bantuan Bot Presensi SMPIT*\n\n";
                
                if ($student) {
                    $helpText .= "✅ Status: *Terhubung*\n\n" .
                        "*Perintah yang Tersedia:*\n" .
                        "• `/start` - Lihat status akun\n" .
                        "• `/menu` - Tampilkan menu navigasi\n" .
                        "• `/help` - Bantuan ini\n\n" .
                        "*Notifikasi Otomatis:*\n" .
                        "Anda akan menerima notifikasi untuk:\n" .
                        "• Presensi masuk siswa\n" .
                        "• Presensi pulang siswa\n" .
                        "• Update status kehadiran\n\n";
                } else {
                    $helpText .= "⚠️ Status: *Belum Terhubung*\n\n" .
                        "*Perintah yang Tersedia:*\n" .
                        "• `/start` - Informasi awal\n" .
                        "• `/menu` - Menu navigasi\n" .
                        "• `/daftar NIS` - Pendaftaran\n" .
                        "• `/help` - Bantuan ini\n\n" .
                        "*Cara Mendaftar:*\n" .
                        "Gunakan command `/daftar` diikuti NIS siswa\n" .
                        "Contoh: `/daftar 12345`\n\n";
                }
                
                $helpText .= "📞 *Butuh Bantuan?*\n" .
                    "Hubungi admin sekolah untuk bantuan lebih lanjut.";
                
                $this->telegramService->sendMessage(
                    $chatId,
                    $helpText,
                    'Markdown',
                    $this->telegramService->createInlineKeyboard([
                        [['text' => '🔙 Kembali ke Menu', 'callback_data' => 'menu_main']]
                    ])
                );
                break;

            case 'menu_main':
                $keyboard = $this->telegramService->createInlineKeyboard($this->getMainMenu($student ? true : false));
                $this->telegramService->sendMessage(
                    $chatId,
                    "🎯 *Menu Utama*\n\n" .
                    "Pilih menu di bawah ini untuk navigasi.",
                    'Markdown',
                    $keyboard
                );
                break;
        }

        // Answer callback query to remove loading state
        if ($callbackId) {
            try {
                Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/answerCallbackQuery", [
                    'callback_query_id' => $callbackId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to answer callback query', ['error' => $e->getMessage()]);
            }
        }
    }
}
