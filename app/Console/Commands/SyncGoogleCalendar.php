<?php

namespace App\Console\Commands;

use App\Observers\StudentScheduleObserver;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;

class SyncGoogleCalendar extends Command
{
    protected $signature = 'google:sync';
    protected $description = 'Sync changes from Google Calendar to Libra';

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->isConnected()) {
            $this->warn('Google Calendar is not connected. Run: php artisan google:connect');
            return;
        }

        $this->info('Syncing from Google Calendar...');

        StudentScheduleObserver::$isSyncing = true;

        try {
            $counts = $service->syncInbound();
            StudentScheduleObserver::$isSyncing = false;

            $this->info("Sync complete: {$counts['created']} created, {$counts['updated']} updated, {$counts['deleted']} deleted.");
        } catch (\Exception $e) {
            StudentScheduleObserver::$isSyncing = false;
            $this->error('Sync failed: ' . $e->getMessage());
        }
    }
}
