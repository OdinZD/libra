<?php

namespace App\Jobs;

use App\Observers\StudentScheduleObserver;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SyncGoogleToLibra
{
    use Dispatchable;

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->isConnected()) {
            return;
        }

        StudentScheduleObserver::$isSyncing = true;

        try {
            $counts = $service->syncInbound();
            Log::info('Google Calendar inbound sync completed', $counts);
        } catch (\Exception $e) {
            Log::error('Google Calendar inbound sync failed', ['error' => $e->getMessage()]);
            // Swallowed intentionally: an inbound sync failure must never break the request.
        } finally {
            StudentScheduleObserver::$isSyncing = false;
        }
    }
}
