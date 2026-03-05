<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleChangedNotification extends Notification
{
    public function __construct(
        private array $scheduleData,
        private string $action,
        private string $tutorName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionText = match ($this->action) {
            'created' => 'zakazala novu sesiju',
            'updated' => 'uredila sesiju',
            'deleted' => 'obrisala sesiju',
            default => 'promijenila sesiju',
        };

        $studentName = ($this->scheduleData['student_first_name'] ?? '') . ' ' . ($this->scheduleData['student_last_name'] ?? '');
        $subject = $this->scheduleData['subject'] ?? '';
        $date = $this->scheduleData['scheduled_date'] ?? '';
        $time = $this->scheduleData['scheduled_time'] ?? '';

        $mailSubject = "{$this->tutorName} je {$actionText}: {$studentName}";

        $message = (new MailMessage())
            ->subject($mailSubject)
            ->greeting("Hej!")
            ->line("{$this->tutorName} je {$actionText}.")
            ->line("**Učenik:** {$studentName}")
            ->line("**Datum:** {$date}")
            ->line("**Vrijeme:** {$time}");

        if ($subject) {
            $message->line("**Predmet:** {$subject}");
        }

        if ($this->action !== 'deleted') {
            $message->action('Otvori Libra Dashboard', url('/dashboard'));
        }

        return $message->line('Libra - Obavijest o rasporedu');
    }
}
