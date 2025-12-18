<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:set-webhook {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the Telegram Bot Webhook URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $token = env('TELEGRAM_BOT_TOKEN');

        if (!$token) {
            $this->error('TELEGRAM_BOT_TOKEN is not set in .env');
            return;
        }

        $webhookUrl = $url . '/api/telegram/webhook';
        $this->info("Setting webhook to: $webhookUrl");

        $response = Http::get("https://api.telegram.org/bot{$token}/setWebhook?url={$webhookUrl}");

        if ($response->successful()) {
            $this->info('Webhook set successfully!');
            $this->info($response->body());
        } else {
            $this->error('Failed to set webhook.');
            $this->error($response->body());
        }
    }
}
