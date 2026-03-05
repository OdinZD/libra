<?php

namespace App\Console\Commands;

use App\Models\StudentSchedule;
use App\Observers\StudentScheduleObserver;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;

class FullSyncGoogleCalendar extends Command
{
    protected $signature = 'google:full-sync {--pull : Also pull events from Google Calendar}';
    protected $description = 'Push all unsynced Libra schedules to Google Calendar';

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->isConnected()) {
            $this->warn('Google Calendar is not connected. Run: php artisan google:connect');
            return;
        }

        $unsynced = StudentSchedule::whereNull('google_event_id')->get();
        $this->info("Found {$unsynced->count()} unsynced schedules to push to Google Calendar.");

        StudentScheduleObserver::$isSyncing = true;

        $pushed = 0;
        $failed = 0;

        foreach ($unsynced as $schedule) {
            try {
                $googleEventId = $service->createEvent($schedule);
                $schedule->updateQuietly([
                    'google_event_id' => $googleEventId,
                    'last_synced_at' => now(),
                ]);
                $pushed++;
                $this->line("  Pushed: {$schedule->studentFullName()} ({$schedule->scheduled_date->format('d.m.Y.')})");
            } catch (\Exception $e) {
                $failed++;
                $this->error("  Failed: {$schedule->studentFullName()} - {$e->getMessage()}");
            }
        }

        $this->info("Push complete: {$pushed} pushed, {$failed} failed.");

        if ($this->option('pull')) {
            $this->newLine();
            $this->info('Pulling from Google Calendar...');
            try {
                $counts = $service->syncInbound();
                $this->info("Pull complete: {$counts['created']} created, {$counts['updated']} updated, {$counts['deleted']} deleted.");
            } catch (\Exception $e) {
                $this->error('Pull failed: ' . $e->getMessage());
            }
        }

        StudentScheduleObserver::$isSyncing = false;
    }
}
