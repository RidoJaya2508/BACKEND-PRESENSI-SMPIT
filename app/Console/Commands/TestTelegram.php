<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;

class TestTelegram extends Command
{
    protected $signature = 'telegram:test {--chat-id=}';
    protected $description = 'Test Telegram bot functionality';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $this->info('===========================================');
        $this->info('Testing Telegram Bot');
        $this->info('===========================================');
        $this->newLine();

        $botToken = env('TELEGRAM_BOT_TOKEN');

        if (!$botToken) {
            $this->error('❌ TELEGRAM_BOT_TOKEN not found in .env');
            return 1;
        }

        $this->info('✓ Bot token found: ' . substr($botToken, 0, 20) . '...');
        $this->newLine();

        // Test 1: Get Bot Info
        $this->info('Test 1: Getting bot information...');
        try {
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getMe");
            if ($response->successful() && $response->json('ok')) {
                $bot = $response->json('result');
                $this->info("✓ Bot is active!");
                $this->line("  Name: {$bot['first_name']}");
                $this->line("  Username: @{$bot['username']}");
                $this->line("  ID: {$bot['id']}");
            } else {
                $this->error('✗ Failed to get bot info');
                $this->line($response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // Test 2: Get Webhook Info
        $this->info('Test 2: Checking webhook status...');
        try {
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getWebhookInfo");
            if ($response->successful()) {
                $webhook = $response->json('result');
                if (!empty($webhook['url'])) {
                    $this->info("✓ Webhook is set: {$webhook['url']}");
                    $this->line("  Pending updates: {$webhook['pending_update_count']}");
                    if (!empty($webhook['last_error_message'])) {
                        $this->warn("  Last error: {$webhook['last_error_message']}");
                        $this->warn("  Last error date: " . date('Y-m-d H:i:s', $webhook['last_error_date']));
                    }
                } else {
                    $this->warn('⚠ Webhook is not set');
                    $this->line('  Use setup_webhook.bat to register webhook');
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 3: Get Recent Updates
        $this->info('Test 3: Getting recent updates...');
        try {
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getUpdates?limit=5");
            if ($response->successful() && $response->json('ok')) {
                $updates = $response->json('result');
                if (count($updates) > 0) {
                    $this->info("✓ Found " . count($updates) . " recent update(s)");
                    foreach ($updates as $update) {
                        if (isset($update['message'])) {
                            $msg = $update['message'];
                            $this->line("  Update #{$update['update_id']}:");
                            $this->line("    From: {$msg['from']['first_name']} (@{$msg['from']['username']})");
                            $this->line("    Chat ID: {$msg['chat']['id']}");
                            $this->line("    Text: {$msg['text']}");
                        }
                    }
                } else {
                    $this->warn('⚠ No recent updates found');
                    $this->line('  Send /start to the bot first');
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
        }
        $this->newLine();

        // Test 4: Send Message (if chat ID provided)
        if ($chatId = $this->option('chat-id')) {
            $this->info("Test 4: Sending test message to Chat ID: {$chatId}...");
            $success = $this->telegramService->sendMessage(
                $chatId,
                "🧪 *Test Message*\n\n" .
                "Jika Anda menerima pesan ini, bot berfungsi dengan baik!\n\n" .
                "⏰ Waktu: " . now()->format('Y-m-d H:i:s'),
                'Markdown'
            );

            if ($success) {
                $this->info('✓ Test message sent successfully!');
            } else {
                $this->error('✗ Failed to send test message');
                $this->line('  Check storage/logs/laravel.log for details');
            }
        } else {
            $this->info('Test 4: Skipped (no --chat-id provided)');
            $this->line('  To test sending message, run:');
            $this->line('  php artisan telegram:test --chat-id=YOUR_CHAT_ID');
        }

        $this->newLine();
        $this->info('===========================================');
        $this->info('Test Complete!');
        $this->info('===========================================');

        return 0;
    }
}
