<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestHorizonNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $type = 'success',
        public int $duration = 2
    ) {
    }

    public function handle(): void
    {
        Log::info("TestHorizonNotifications job started", [
            'type' => $this->type,
            'duration' => $this->duration,
        ]);

        // Simulate work
        sleep($this->duration);

        if ($this->type === 'failure') {
            throw new \Exception('Test job failure for notification testing');
        }

        Log::info("TestHorizonNotifications job completed", [
            'type' => $this->type,
            'duration' => $this->duration,
        ]);
    }
}
