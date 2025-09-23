<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisPingCommand extends Command
{
    protected $signature = 'redis:ping';

    protected $description = 'Check connectivity with the configured Redis server';

    public function handle(): int
    {
        try {
            $status = Redis::connection()->ping();
            $this->info("Redis responded with: {$status}");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Redis connection failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
