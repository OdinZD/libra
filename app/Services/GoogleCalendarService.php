<?php

namespace App\Services;

use App\Models\GoogleCalendarToken;
use App\Models\StudentSchedule;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private GoogleClient $client;
    private ?GoogleCalendarToken $token;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google_calendar.client_id'));
        $this->client->setClientSecret(config('services.google_calendar.client_secret'));
        $this->client->setRedirectUri(config('services.google_calendar.redirect_uri'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        $this->token = GoogleCalendarToken::current();
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function handleAuthCallback(string $code): void
    {
        $tokenData = $this->client->fetchAccessTokenWithAuthCode($code);

        GoogleCalendarToken::query()->delete();

        GoogleCalendarToken::create([
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? '',
            'expires_at' => Carbon::now()->addSeconds($tokenData['expires_in']),
        ]);
    }

    public function isConnected(): bool
    {
        return $this->token !== null && $this->token->refresh_token !== '';
    }

    public function createEvent(StudentSchedule $schedule): string
    {
        $service = $this->getCalendarService();
        $event = $this->buildEventFromSchedule($schedule);

        $createdEvent = $service->events->insert(
            config('services.google_calendar.calendar_id'),
            $event
        );

        return $createdEvent->getId();
    }

    public function updateEvent(StudentSchedule $schedule): void
    {
        if (! $schedule->google_event_id) {
            return;
        }

        $service = $this->getCalendarService();
        $event = $this->buildEventFromSchedule($schedule);

        try {
            $service->events->update(
                config('services.google_calendar.calendar_id'),
                $schedule->google_event_id,
                $event
            );
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 404) {
                $newId = $this->createEvent($schedule);
                $schedule->updateQuietly(['google_event_id' => $newId, 'last_synced_at' => now()]);
            } else {
                throw $e;
            }
        }
    }

    public function deleteEvent(string $googleEventId): void
    {
        $service = $this->getCalendarService();

        try {
            $service->events->delete(
                config('services.google_calendar.calendar_id'),
                $googleEventId
            );
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() !== 404 && $e->getCode() !== 410) {
                throw $e;
            }
        }
    }

    public function syncInbound(): array
    {
        $service = $this->getCalendarService();
        $calendarId = config('services.google_calendar.calendar_id');
        $counts = ['created' => 0, 'updated' => 0, 'deleted' => 0];

        $params = [
            'singleEvents' => true,
            'showDeleted' => true,
        ];

        if ($this->token->sync_token) {
            $params['syncToken'] = $this->token->sync_token;
        } else {
            $params['timeMin'] = Carbon::now()->subMonths(3)->toRfc3339String();
        }

        try {
            $pageToken = null;
            do {
                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }

                $events = $service->events->listEvents($calendarId, $params);

                foreach ($events->getItems() as $event) {
                    $result = $this->processInboundEvent($event);
                    if ($result) {
                        $counts[$result]++;
                    }
                }

                $pageToken = $events->getNextPageToken();
            } while ($pageToken);

            if ($nextSyncToken = $events->getNextSyncToken()) {
                $this->token->update(['sync_token' => $nextSyncToken]);
            }
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 410) {
                $this->token->update(['sync_token' => null]);
                return $this->syncInbound();
            }
            throw $e;
        }

        return $counts;
    }

    private function processInboundEvent(Event $event): ?string
    {
        $existing = StudentSchedule::where('google_event_id', $event->getId())->first();

        if ($event->getStatus() === 'cancelled') {
            if ($existing) {
                $existing->delete();
                return 'deleted';
            }
            return null;
        }

        $data = $this->parseEventToScheduleData($event);
        if (! $data) {
            return null;
        }

        if ($existing) {
            if ($existing->last_synced_at && $existing->updated_at > $existing->last_synced_at) {
                return null;
            }

            $existing->updateQuietly(array_merge($data, ['last_synced_at' => now()]));
            return 'updated';
        }

        $colorToTutor = config('services.google_calendar.tutor_user_ids');
        $userId = $this->mapGoogleColorToUserId($event->getColorId(), $colorToTutor);

        StudentSchedule::withoutEvents(function () use ($data, $userId, $event) {
            StudentSchedule::create(array_merge($data, [
                'user_id' => $userId,
                'google_event_id' => $event->getId(),
                'last_synced_at' => now(),
            ]));
        });

        return 'created';
    }

    public function parseEventToScheduleData(Event $event): ?array
    {
        $summary = $event->getSummary();
        if (! $summary) {
            return null;
        }

        // Strip tutor prefix like "[Marina] " or "[Valentina] "
        $summary = preg_replace('/^\[.*?\]\s*/', '', $summary);

        $parts = explode(' - ', $summary, 2);
        $nameParts = explode(' ', trim($parts[0]), 2);

        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        if (! $firstName) {
            return null;
        }

        $subject = $parts[1] ?? null;
        $note = $event->getDescription();

        $start = $event->getStart();
        $startDateTime = $start->getDateTime() ?? $start->getDate();
        $carbon = Carbon::parse($startDateTime);

        $googleColorId = $event->getColorId();
        $color = $this->mapGoogleColorIdToAppColor($googleColorId);

        return [
            'student_first_name' => $firstName,
            'student_last_name' => $lastName,
            'subject' => $subject,
            'note' => $note,
            'scheduled_date' => $carbon->toDateString(),
            'scheduled_time' => $carbon->format('H:i'),
            'color' => $color,
        ];
    }

    public function watchCalendar(string $webhookUrl): array
    {
        $service = $this->getCalendarService();
        $calendarId = config('services.google_calendar.calendar_id');

        $channel = new \Google\Service\Calendar\Channel();
        $channel->setId('libra-calendar-' . uniqid());
        $channel->setType('web_hook');
        $channel->setAddress($webhookUrl);

        $response = $service->events->watch($calendarId, $channel);

        return [
            'id' => $response->getId(),
            'resourceId' => $response->getResourceId(),
            'expiration' => $response->getExpiration(),
        ];
    }

    public function stopWatching(string $channelId, string $resourceId): void
    {
        $service = $this->getCalendarService();

        $channel = new \Google\Service\Calendar\Channel();
        $channel->setId($channelId);
        $channel->setResourceId($resourceId);

        $service->channels->stop($channel);
    }

    private function getCalendarService(): Calendar
    {
        $this->refreshTokenIfNeeded();

        $this->client->setAccessToken([
            'access_token' => $this->token->access_token,
            'refresh_token' => $this->token->refresh_token,
            'expires_in' => $this->token->expires_at->diffInSeconds(now()),
        ]);

        return new Calendar($this->client);
    }

    private function refreshTokenIfNeeded(): void
    {
        if (! $this->token || $this->token->expires_at->isFuture()) {
            return;
        }

        $this->client->fetchAccessTokenWithRefreshToken($this->token->refresh_token);
        $newToken = $this->client->getAccessToken();

        $this->token->update([
            'access_token' => $newToken['access_token'],
            'expires_at' => Carbon::now()->addSeconds($newToken['expires_in']),
            'refresh_token' => $newToken['refresh_token'] ?? $this->token->refresh_token,
        ]);
    }

    private function buildEventFromSchedule(StudentSchedule $schedule): Event
    {
        $event = new Event();

        $title = '[' . $schedule->tutorName() . '] ' . $schedule->studentFullName();
        if ($schedule->subject) {
            $title .= ' - ' . $schedule->subject;
        }
        $event->setSummary($title);

        if ($schedule->note) {
            $event->setDescription($schedule->note);
        }

        $startDt = Carbon::parse($schedule->scheduled_date->toDateString() . ' ' . $schedule->scheduled_time);
        $endDt = $startDt->copy()->addHour();

        $start = new EventDateTime();
        $start->setDateTime($startDt->toRfc3339String());
        $start->setTimeZone('Europe/Zagreb');
        $event->setStart($start);

        $end = new EventDateTime();
        $end->setDateTime($endDt->toRfc3339String());
        $end->setTimeZone('Europe/Zagreb');
        $event->setEnd($end);

        $event->setColorId($this->mapColorToGoogleColorId($schedule->color));

        return $event;
    }

    private function mapColorToGoogleColorId(string $color): string
    {
        return match ($color) {
            'coral' => '4',   // flamingo
            'purple' => '1',  // lavender
            default => '4',
        };
    }

    private function mapGoogleColorIdToAppColor(?string $googleColorId): string
    {
        return match ($googleColorId) {
            '1' => 'purple',  // lavender → Valentina
            default => 'coral', // everything else → Marina
        };
    }

    private function mapGoogleColorToUserId(?string $googleColorId, array $colorToTutor): int
    {
        $appColor = $this->mapGoogleColorIdToAppColor($googleColorId);

        return $colorToTutor[$appColor] ?? $colorToTutor['coral'];
    }
}
