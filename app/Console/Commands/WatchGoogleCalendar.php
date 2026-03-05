<?php

namespace App\Console\Commands;

use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;

class WatchGoogleCalendar extends Command
{
    protected $signature = 'google:watch {--url= : The webhook URL for push notifications}';
    protected $description = 'Register Google Calendar push notifications (for deployed environments)';

    public function handle(GoogleCalendarService $service): void
    {
        if (! $service->isConnected()) {
            $this->warn('Google Calendar is not connected. Run: php artisan google:connect');
            return;
        }

        $webhookUrl = $this->option('url');
        if (! $webhookUrl) {
            $webhookUrl = url('/api/webhooks/google-calendar');
        }

        $this->info("Registering webhook: {$webhookUrl}");

        try {
            $result = $service->watchCalendar($webhookUrl);
            $this->info('Webhook registered successfully!');
            $this->table(['Key', 'Value'], [
                ['Channel ID', $result['id']],
                ['Resource ID', $result['resourceId']],
                ['Expires', date('Y-m-d H:i:s', $result['expiration'] / 1000)],
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to register webhook: ' . $e->getMessage());
        }
    }
}
