<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $appointmentId)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = Appointment::query()->with(['company.settings', 'service', 'client'])->findOrFail($this->appointmentId);
        $timezone = $appointment->company->settings?->timezone ?? $appointment->company->timezone ?? config('app.timezone');
        $start = $appointment->starts_at->setTimezone($timezone);

        return (new MailMessage)
            ->subject('Seu agendamento no Agendify')
            ->greeting('Ola, '.$appointment->client?->name.'!')
            ->line('Seu agendamento foi registrado com sucesso.')
            ->line('Servico: '.$appointment->service?->name)
            ->line('Data e hora: '.$start->format('d/m/Y H:i'))
            ->line('Status inicial: '.$appointment->status?->label())
            ->line('Se precisar, responda este e-mail para solicitar alteracoes.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointmentId,
            'type' => 'created',
        ];
    }
}
