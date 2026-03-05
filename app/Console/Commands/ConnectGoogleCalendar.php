<?php

namespace App\Console\Commands;

use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;

class ConnectGoogleCalendar extends Command
{
    protected $signature = 'google:connect';
    protected $description = 'Get the Google Calendar OAuth URL to authorize access';

    public function handle(GoogleCalendarService $service): void
    {
        if ($service->isConnected()) {
            $this->info('Google Calendar is already connected.');
            if (! $this->confirm('Do you want to re-authorize?')) {
                return;
            }
        }

        $url = $service->getAuthUrl();

        $this->newLine();
        $this->info('Open this URL in your browser to authorize Google Calendar:');
        $this->newLine();
        $this->line($url);
        $this->newLine();
        $this->info('After authorizing, you will be redirected to your Libra app.');
    }
}
