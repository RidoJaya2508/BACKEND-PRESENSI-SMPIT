<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ServeWithBot extends Command
{
    protected $signature = 'serve:bot {--host=127.0.0.1} {--port=8000}';
    protected $description = 'Start Laravel development server with Telegram bot polling';

    private $processes = [];

    public function handle()
    {
        $this->info('🚀 Starting Laravel + Telegram Bot...');
        $this->newLine();

        // Clear cache first
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->newLine();

        $host = $this->option('host');
        $port = $this->option('port');

        // Start Laravel server
        $this->info("📡 Starting Laravel server on http://{$host}:{$port}");
        $serverProcess = new Process(['php', 'artisan', 'serve', "--host={$host}", "--port={$port}"]);
        $serverProcess->setTimeout(null);
        $serverProcess->start();
        $this->processes[] = $serverProcess;

        // Wait a bit for server to start
        sleep(2);

        // Start Telegram bot polling
        $this->info("🤖 Starting Telegram bot polling...");
        $this->newLine();
        
        $botProcess = new Process(['php', 'artisan', 'telegram:poll']);
        $botProcess->setTimeout(null);
        $botProcess->start();
        $this->processes[] = $botProcess;

        $this->displayStatus($host, $port);

        // Handle both processes
        $this->monitorProcesses();

        return 0;
    }

    private function displayStatus($host, $port)
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════');
        $this->line('  <fg=green>✓</> Laravel Server: <fg=cyan>http://' . $host . ':' . $port . '</>');
        $this->line('  <fg=green>✓</> Telegram Bot:   <fg=cyan>Polling Active</>');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();
        $this->comment('Press Ctrl+C to stop all services');
        $this->newLine();
    }

    private function monitorProcesses()
    {
        pcntl_async_signals(true);
        
        pcntl_signal(SIGINT, function () {
            $this->stopAllProcesses();
        });

        pcntl_signal(SIGTERM, function () {
            $this->stopAllProcesses();
        });

        while (true) {
            foreach ($this->processes as $process) {
                if (!$process->isRunning()) {
                    $this->error('Process stopped unexpectedly!');
                    $this->stopAllProcesses();
                    return;
                }

                // Output from processes
                $output = $process->getIncrementalOutput();
                if (!empty($output)) {
                    echo $output;
                }

                $errorOutput = $process->getIncrementalErrorOutput();
                if (!empty($errorOutput)) {
                    $this->error($errorOutput);
                }
            }

            usleep(100000); // Sleep for 100ms
        }
    }

    private function stopAllProcesses()
    {
        $this->newLine();
        $this->warn('Stopping all services...');
        
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->stop(3, SIGINT);
            }
        }

        $this->info('All services stopped.');
        exit(0);
    }
}
