<?php

namespace App\Notifications;

use App\Enums\ReminderType;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $appointmentId,
        private readonly string $reminderType,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = ReminderType::from($this->reminderType);
        $appointment = Appointment::query()->with(['company.settings', 'service', 'client'])->findOrFail($this->appointmentId);
        $timezone = $appointment->company->settings?->timezone ?? $appointment->company->timezone ?? config('app.timezone');
        $start = $appointment->starts_at->setTimezone($timezone);

        return (new MailMessage)
            ->subject('Lembrete de agendamento - '.$appointment->company->name)
            ->greeting('Ola, '.$appointment->client?->name.'!')
            ->line('Este e um lembrete '.$type->label().' do seu atendimento.')
            ->line('Servico: '.$appointment->service?->name)
            ->line('Data e hora: '.$start->format('d/m/Y H:i'))
            ->line('Nos vemos em breve!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointmentId,
            'type' => 'reminder',
            'reminder_type' => $this->reminderType,
        ];
    }
}
