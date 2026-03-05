<?php

namespace App\Observers;

use App\Jobs\DeleteGoogleCalendarEvent;
use App\Jobs\SyncScheduleToGoogle;
use App\Models\StudentSchedule;
use App\Models\User;
use App\Notifications\ScheduleChangedNotification;
use Illuminate\Support\Facades\Log;

class StudentScheduleObserver
{
    public static bool $isSyncing = false;

    public function created(StudentSchedule $schedule): void
    {
        if (static::$isSyncing) {
            return;
        }

        SyncScheduleToGoogle::dispatch($schedule, 'created');
        $this->notifyOtherTutor($schedule, 'created');
    }

    public function updated(StudentSchedule $schedule): void
    {
        if (static::$isSyncing) {
            return;
        }

        $meaningfulFields = [
            'student_first_name', 'student_last_name', 'subject',
            'note', 'scheduled_date', 'scheduled_time', 'color',
        ];

        $hasMeaningfulChange = false;
        foreach ($meaningfulFields as $field) {
            if ($schedule->isDirty($field)) {
                $hasMeaningfulChange = true;
                break;
            }
        }

        SyncScheduleToGoogle::dispatch($schedule, 'updated');

        if ($hasMeaningfulChange) {
            $this->notifyOtherTutor($schedule, 'updated');
        }
    }

    public function deleted(StudentSchedule $schedule): void
    {
        if (static::$isSyncing) {
            return;
        }

        if ($schedule->google_event_id) {
            DeleteGoogleCalendarEvent::dispatch($schedule->google_event_id);
        }

        $this->notifyOtherTutor($schedule, 'deleted');
    }

    private function notifyOtherTutor(StudentSchedule $schedule, string $action): void
    {
        $tutorUserIds = config('services.google_calendar.tutor_user_ids');
        $currentColor = $schedule->color;
        $tutorName = $schedule->tutorName();

        $scheduleData = [
            'student_first_name' => $schedule->student_first_name,
            'student_last_name' => $schedule->student_last_name,
            'subject' => $schedule->subject,
            'scheduled_date' => $schedule->scheduled_date?->format('d.m.Y.') ?? '',
            'scheduled_time' => $schedule->scheduled_time,
        ];

        $otherColor = $currentColor === 'coral' ? 'purple' : 'coral';
        $otherUserId = $tutorUserIds[$otherColor] ?? null;

        if (! $otherUserId) {
            return;
        }

        $otherUser = User::find($otherUserId);
        if (! $otherUser) {
            return;
        }

        try {
            $otherUser->notify(new ScheduleChangedNotification($scheduleData, $action, $tutorName));
        } catch (\Exception $e) {
            Log::warning('Failed to send schedule notification', [
                'user_id' => $otherUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
