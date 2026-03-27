<?php

namespace App\Http\Controllers;

use App\Actions\CreateAppointmentAction;
use App\Actions\GenerateAvailableSlotsAction;
use App\Actions\UpdateAppointmentStatusAction;
use App\Enums\AppointmentStatus;
use App\Http\Requests\GenerateSlotsRequest;
use App\Http\Requests\PublicBookingRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Company;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicBookingController extends Controller
{
    public function show(Company $company): Response
    {
        $company->load('settings');

        $services = Service::query()
            ->forCompany($company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service): array => [
                'id' => $service->id,
                'name' => $service->name,
                'duration_minutes' => $service->duration_minutes,
                'price' => $service->price,
            ]);

        return Inertia::render('Booking/Show', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'phone' => $company->phone,
                'timezone' => $company->settings?->timezone ?? $company->timezone,
            ],
            'services' => $services,
            'settings' => [
                'allow_client_cancellation' => $company->settings?->allow_client_cancellation ?? true,
                'cancellation_window_hours' => $company->settings?->cancellation_window_hours ?? 12,
            ],
        ]);
    }

    public function slots(GenerateSlotsRequest $request, Company $company, GenerateAvailableSlotsAction $action): JsonResponse
    {
        return response()->json([
            'slots' => $action->execute(
                $company,
                (int) $request->input('service_id'),
                $request->string('date')->toString(),
            ),
        ]);
    }

    public function store(PublicBookingRequest $request, Company $company, CreateAppointmentAction $action): RedirectResponse
    {
        $service = Service::query()
            ->forCompany($company->id)
            ->where('is_active', true)
            ->findOrFail((int) $request->input('service_id'));

        $client = Client::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'phone' => $request->string('client_phone')->toString(),
            ],
            [
                'name' => $request->string('client_name')->toString(),
                'email' => $request->string('client_email')->toString() ?: null,
            ],
        );

        $appointment = $action->execute(
            company: $company,
            service: $service,
            client: $client,
            startsAt: $request->string('starts_at')->toString(),
            source: 'public_link',
            notes: $request->string('notes')->toString() ?: null,
        );

        return to_route('booking.success', [
            'company' => $company->slug,
            'appointment' => $appointment->id,
        ]);
    }

    public function success(Company $company, int $appointment): Response
    {
        $company->load('settings');

        $appointmentModel = Appointment::query()
            ->forCompany($company->id)
            ->with(['service', 'client'])
            ->findOrFail($appointment);

        $timezone = $company->settings?->timezone ?? $company->timezone ?? config('app.timezone');
        $startsAt = $appointmentModel->starts_at->setTimezone($timezone);
        $hoursUntilAppointment = CarbonImmutable::now($timezone)->diffInHours($startsAt, false);

        return Inertia::render('Booking/Success', [
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
            ],
            'appointment' => [
                'id' => $appointmentModel->id,
                'status' => $appointmentModel->status?->value,
                'status_label' => $appointmentModel->status?->label(),
                'service' => $appointmentModel->service?->name,
                'client' => $appointmentModel->client?->name,
                'starts_at_label' => $startsAt->format('d/m/Y H:i'),
                'token' => $appointmentModel->confirmation_token,
            ],
            'can_cancel' => ($company->settings?->allow_client_cancellation ?? true)
                && $hoursUntilAppointment >= ($company->settings?->cancellation_window_hours ?? 12)
                && in_array($appointmentModel->status, [
                    AppointmentStatus::Scheduled,
                    AppointmentStatus::Confirmed,
                ], true),
        ]);
    }

    public function cancel(
        Request $request,
        Company $company,
        int $appointment,
        UpdateAppointmentStatusAction $action,
    ): RedirectResponse {
        $company->load('settings');

        $appointmentModel = Appointment::query()
            ->forCompany($company->id)
            ->findOrFail($appointment);

        if (! hash_equals((string) $appointmentModel->confirmation_token, (string) $request->input('token'))) {
            abort(403, 'Token de cancelamento invalido.');
        }

        if (! ($company->settings?->allow_client_cancellation ?? true)) {
            return back()->with('error', 'Esta empresa nao permite cancelamento automatico.');
        }

        $timezone = $company->settings?->timezone ?? $company->timezone ?? config('app.timezone');
        $hoursUntilAppointment = CarbonImmutable::now($timezone)
            ->diffInHours($appointmentModel->starts_at->setTimezone($timezone), false);

        if ($hoursUntilAppointment < ($company->settings?->cancellation_window_hours ?? 12)) {
            return back()->with('error', 'Janela de cancelamento ja encerrada.');
        }

        $action->execute(
            appointment: $appointmentModel,
            nextStatus: AppointmentStatus::Cancelled,
            reason: 'Cancelado pelo cliente',
        );

        return back()->with('success', 'Agendamento cancelado com sucesso.');
    }
}
