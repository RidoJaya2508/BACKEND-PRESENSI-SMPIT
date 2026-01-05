<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Http\Request;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll {--once : Process updates once and exit}';
    protected $description = 'Poll Telegram for updates (alternative to webhook for local development)';

    public function handle()
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        
        if (!$botToken) {
            $this->error('TELEGRAM_BOT_TOKEN not found in .env');
            return 1;
        }

        $this->info('Starting Telegram polling...');
        $this->info('Bot: @' . env('VITE_TELEGRAM_BOT_USERNAME', 'Bot'));
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        $offset = 0;
        $controller = app(TelegramWebhookController::class);

        do {
            try {
                // Get updates from Telegram with increased timeout and retry
                $response = Http::timeout(60)->retry(3, 100)->get("https://api.telegram.org/bot{$botToken}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30,
                    'limit' => 100
                ]);

                if (!$response->successful()) {
                    $this->warn('Failed to get updates: ' . $response->status() . '. Retrying...');
                    sleep(2);
                    continue;
                }

                $updates = $response->json('result', []);

                foreach ($updates as $update) {
                    $updateId = $update['update_id'];
                    $offset = $updateId + 1;

                    if (isset($update['message'])) {
                        $message = $update['message'];
                        $chatId = $message['chat']['id'] ?? null;
                        $text = $message['text'] ?? '';
                        $from = $message['from']['first_name'] ?? 'User';

                        $this->line("[{$chatId}] {$from}: {$text}");

                        // Process the update through webhook controller
                        $request = new Request();
                        $request->merge($update);
                        $controller->handle($request);

                        $this->info("  ✓ Processed");
                    }
                }

                if (count($updates) === 0) {
                    $this->comment('Waiting for messages...');
                }

                // If --once flag is set, exit after processing
                if ($this->option('once')) {
                    break;
                }

                sleep(1); // Small delay to prevent excessive API calls

            } catch (\Exception $e) {
                $this->warn('Network error: ' . $e->getMessage());
                $this->comment('Retrying in 3 seconds...');
                sleep(3); // Wait before retry
                // Don't increment offset, retry the same request
            }

        } while (!$this->option('once'));

        $this->info('Polling stopped.');
        return 0;
    }
}
