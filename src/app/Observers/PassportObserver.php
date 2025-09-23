<?php

namespace App\Observers;

use App\Domain\Passport\Models\Passport;
use Illuminate\Support\Facades\Cache;

class PassportObserver
{
    public function created(Passport $passport): void
    {
        $this->flushPassportCaches();
    }

    public function updated(Passport $passport): void
    {
        $this->flushPassportCaches();
    }

    public function deleted(Passport $passport): void
    {
        $this->flushPassportCaches();
    }

    public function restored(Passport $passport): void
    {
        $this->flushPassportCaches();
    }

    protected function flushPassportCaches(): void
    {
        Cache::tags(['passports'])->flush();
    }
}
