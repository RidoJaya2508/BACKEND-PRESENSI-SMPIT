<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateWebhook extends Command
{
    protected $signature = 'telegram:simulate-webhook {text=/start} {--chat-id=123456789}';
    protected $description = 'Simulate a Telegram webhook request locally';

    public function handle()
    {
        $text = $this->argument('text');
        $chatId = $this->option('chat-id');

        $this->info('===========================================');
        $this->info('Simulating Telegram Webhook');
        $this->info('===========================================');
        $this->line("Text: {$text}");
        $this->line("Chat ID: {$chatId}");
        $this->newLine();

        $payload = [
            'update_id' => rand(100000, 999999),
            'message' => [
                'message_id' => rand(1, 1000),
                'from' => [
                    'id' => (int)$chatId,
                    'is_bot' => false,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'language_code' => 'id'
                ],
                'chat' => [
                    'id' => (int)$chatId,
                    'first_name' => 'Test User',
                    'username' => 'testuser',
                    'type' => 'private'
                ],
                'date' => time(),
                'text' => $text
            ]
        ];

        $this->info('Sending request to webhook endpoint...');
        $this->newLine();

        try {
            $response = Http::post('http://localhost:8000/api/telegram/webhook', $payload);

            if ($response->successful()) {
                $this->info('✓ Webhook responded successfully!');
                $this->line('Response: ' . $response->body());
                $this->newLine();
                $this->info('Check the logs for processing details:');
                $this->line('  tail -f storage/logs/laravel.log');
                $this->line('  or');
                $this->line('  Get-Content storage\\logs\\laravel.log -Tail 20 -Wait');
            } else {
                $this->error('✗ Webhook returned error: ' . $response->status());
                $this->line($response->body());
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Make sure Laravel server is running:');
            $this->line('  php artisan serve');
        }

        $this->newLine();
        return 0;
    }
}
