<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Http\Request;

class PollTelegramUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Telegram updates manually (for local development without ngrok)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env');
            return;
        }

        $this->info('Starting Telegram Long Polling...');
        $this->info('Press Ctrl+C to stop.');

        // First, delete webhook to enable getUpdates
        Http::get("https://api.telegram.org/bot{$token}/deleteWebhook");

        $offset = 0;
        $controller = app(TelegramWebhookController::class);

        while (true) {
            try {
                $response = Http::timeout(60)->get("https://api.telegram.org/bot{$token}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 50, // Long polling timeout
                ]);

                if ($response->successful()) {
                    $updates = $response->json()['result'] ?? [];

                    foreach ($updates as $update) {
                        $this->info('Received update ID: ' . $update['update_id']);
                        
                        // Create a mock request to pass to the controller
                        $request = new Request();
                        $request->replace($update);

                        // Process the update using the existing controller logic
                        $controller->handle($request);

                        $offset = $update['update_id'] + 1;
                    }
                } else {
                    $this->error('Error fetching updates: ' . $response->body());
                    sleep(5);
                }
            } catch (\Exception $e) {
                $this->error('Exception: ' . $e->getMessage());
                sleep(5);
            }
        }
    }
}
