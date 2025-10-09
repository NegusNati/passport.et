<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\TestHorizonNotifications;

class TestNotifications extends Command
{
    protected $signature = 'notifications:test {type=all : Type of notification to test (all|success|failure|long)}';

    protected $description = 'Test Horizon Telegram notifications';

    public function handle(): void
    {
        $type = $this->argument('type');

        $this->info('Testing Horizon Telegram notifications...');

        switch ($type) {
            case 'success':
                $this->testSuccessNotifications();
                break;
            case 'failure':
                $this->testFailureNotifications();
                break;
            case 'long':
                $this->testLongRunningJob();
                break;
            case 'all':
            default:
                $this->testSuccessNotifications();
                $this->testFailureNotifications();
                $this->testLongRunningJob();
                break;
        }

        $this->info('Test jobs dispatched. Check your Telegram for notifications!');
    }

    private function testSuccessNotifications(): void
    {
        $this->info('Dispatching successful test jobs...');

        for ($i = 1; $i <= 3; $i++) {
            TestHorizonNotifications::dispatch('success', 1)
                ->onQueue('default');
            $this->line("- Dispatched success test job #{$i}");
        }
    }

    private function testFailureNotifications(): void
    {
        $this->info('Dispatching failing test jobs...');

        for ($i = 1; $i <= 2; $i++) {
            TestHorizonNotifications::dispatch('failure', 1)
                ->onQueue('default');
            $this->line("- Dispatched failure test job #{$i}");
        }
    }

    private function testLongRunningJob(): void
    {
        $this->info('Dispatching long-running test job (10 seconds)...');

        TestHorizonNotifications::dispatch('success', 10)
            ->onQueue('default');
        $this->line('- Dispatched long-running test job');
    }
}
