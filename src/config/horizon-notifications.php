<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Telegram Notifications
    |--------------------------------------------------------------------------
    |
    | Configure which Horizon events should trigger Telegram notifications.
    | You can disable noisy notifications like job processing/completion
    | in production while keeping critical alerts enabled.
    |
    */

    'enabled' => env('HORIZON_TELEGRAM_NOTIFICATIONS', true),

    'events' => [
        'job_failed' => env('HORIZON_NOTIFY_JOB_FAILED', true),
        'job_processing' => env('HORIZON_NOTIFY_JOB_PROCESSING', false),
        'job_processed' => env('HORIZON_NOTIFY_JOB_PROCESSED', false),
        'long_wait_detected' => env('HORIZON_NOTIFY_LONG_WAIT', true),
        'worker_stopping' => env('HORIZON_NOTIFY_WORKER_STOPPING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Filtering
    |--------------------------------------------------------------------------
    |
    | Only notify for specific job classes or queues if needed.
    | Leave empty arrays to notify for all jobs/queues.
    |
    */

    'job_classes' => [
        // Example: 'App\Jobs\ImportantJob',
        // Only these job classes will trigger notifications
    ],

    'queues' => [
        // Example: 'high', 'critical'
        // Only these queues will trigger notifications
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Prevent spam by limiting notifications per minute for each event type.
    |
    */

    'rate_limits' => [
        'job_failed' => 10,        // Max 10 job failure notifications per minute
        'job_processing' => 20,    // Max 20 job processing notifications per minute
        'job_processed' => 20,     // Max 20 job completion notifications per minute
        'long_wait_detected' => 5, // Max 5 long wait notifications per minute
        'worker_stopping' => 5,    // Max 5 worker stopping notifications per minute
    ],
];
