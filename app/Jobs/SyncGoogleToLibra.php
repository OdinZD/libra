<?php

namespace App\Jobs;

use App\Observers\StudentScheduleObserver;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SyncGoogleToLibra implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $uniqueFor = 30;

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
            throw $e;
        } finally {
            StudentScheduleObserver::$isSyncing = false;
        }
    }
}
