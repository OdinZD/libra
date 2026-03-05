<?php

namespace App\Jobs;

use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DeleteGoogleCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public function __construct(
        private string $googleEventId,
    ) {}

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->isConnected()) {
            return;
        }

        try {
            $service->deleteEvent($this->googleEventId);
        } catch (\Exception $e) {
            Log::error('Google Calendar event deletion failed', [
                'google_event_id' => $this->googleEventId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
