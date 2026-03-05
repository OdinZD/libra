<?php

namespace App\Jobs;

use App\Models\StudentSchedule;
use App\Observers\StudentScheduleObserver;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncScheduleToGoogle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private StudentSchedule $schedule,
        private string $action,
    ) {}

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->isConnected()) {
            return;
        }

        try {
            if ($this->action === 'created' || ($this->action === 'updated' && ! $this->schedule->google_event_id)) {
                $googleEventId = $service->createEvent($this->schedule);

                StudentScheduleObserver::$isSyncing = true;
                $this->schedule->updateQuietly([
                    'google_event_id' => $googleEventId,
                    'last_synced_at' => now(),
                ]);
                StudentScheduleObserver::$isSyncing = false;
            } elseif ($this->action === 'updated') {
                $service->updateEvent($this->schedule);

                StudentScheduleObserver::$isSyncing = true;
                $this->schedule->updateQuietly([
                    'last_synced_at' => now(),
                ]);
                StudentScheduleObserver::$isSyncing = false;
            }
        } catch (\Exception $e) {
            StudentScheduleObserver::$isSyncing = false;
            Log::error('Google Calendar sync failed', [
                'schedule_id' => $this->schedule->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
