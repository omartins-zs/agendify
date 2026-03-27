<?php

namespace App\Http\Controllers;

use App\Actions\CreateAppointmentAction;
use App\Actions\GenerateAvailableSlotsAction;
use App\Actions\UpdateAppointmentStatusAction;
use App\Enums\AppointmentStatus;
use App\Http\Requests\GenerateSlotsRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->company()->with('settings')->firstOrFail();

        $appointmentsQuery = Appointment::query()
            ->forCompany($company->id)
            ->with(['service:id,name', 'client:id,name,phone'])
            ->orderBy('starts_at');

        if ($request->filled('status')) {
            $appointmentsQuery->where('status', $request->string('status')->toString());
        }

        if ($request->filled('date')) {
            $appointmentsQuery->whereDate('starts_at', $request->string('date')->toString());
        }

        $appointments = $appointmentsQuery->get()->map(function (Appointment $appointment) use ($company): array {
            $timezone = $company->settings?->timezone ?? $company->timezone ?? config('app.timezone');

            return [
                'id' => $appointment->id,
                'service' => $appointment->service?->name,
                'client' => $appointment->client?->name,
                'phone' => $appointment->client?->phone,
                'starts_at' => $appointment->starts_at->setTimezone($timezone)->toIso8601String(),
                'starts_at_label' => $appointment->starts_at->setTimezone($timezone)->format('d/m/Y H:i'),
                'status' => $appointment->status?->value,
                'status_label' => $appointment->status?->label(),
                'notes' => $appointment->notes,
            ];
        });

        $services = Service::query()
            ->forCompany($company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes']);

        $clients = Client::query()
            ->forCompany($company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email']);

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'services' => $services,
            'clients' => $clients,
            'statuses' => array_map(
                static fn (AppointmentStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ],
                AppointmentStatus::cases(),
            ),
            'filters' => [
                'status' => $request->input('status'),
                'date' => $request->input('date'),
            ],
        ]);
    }

    public function slots(GenerateSlotsRequest $request, GenerateAvailableSlotsAction $action): JsonResponse
    {
        $company = $request->user()->company()->with('settings')->firstOrFail();

        return response()->json([
            'slots' => $action->execute(
                $company,
                (int) $request->input('service_id'),
                $request->string('date')->toString(),
            ),
        ]);
    }

    public function store(StoreAppointmentRequest $request, CreateAppointmentAction $action): RedirectResponse
    {
        $company = $request->user()->company()->with('settings')->firstOrFail();

        $service = Service::query()
            ->forCompany($company->id)
            ->where('is_active', true)
            ->findOrFail((int) $request->input('service_id'));

        $client = $request->filled('client_id')
            ? Client::query()->forCompany($company->id)->findOrFail((int) $request->input('client_id'))
            : Client::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'phone' => $request->string('client_phone')->toString(),
                ],
                [
                    'name' => $request->string('client_name')->toString(),
                    'email' => $request->string('client_email')->toString() ?: null,
                ],
            );

        $action->execute(
            company: $company,
            service: $service,
            client: $client,
            startsAt: $request->string('starts_at')->toString(),
            bookedBy: $request->user(),
            source: 'dashboard',
            notes: $request->string('notes')->toString() ?: null,
        );

        return back()->with('success', 'Agendamento criado com sucesso.');
    }

    public function update(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment,
        UpdateAppointmentStatusAction $action,
    ): RedirectResponse {
        $this->authorize('update', $appointment);

        $action->execute(
            appointment: $appointment,
            nextStatus: AppointmentStatus::from($request->string('status')->toString()),
            changedBy: $request->user(),
            reason: $request->string('reason')->toString() ?: null,
        );

        return back()->with('success', 'Status do agendamento atualizado com sucesso.');
    }

    public function destroy(Request $request, Appointment $appointment, UpdateAppointmentStatusAction $action): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $action->execute(
            appointment: $appointment,
            nextStatus: AppointmentStatus::Cancelled,
            changedBy: $request->user(),
            reason: 'Cancelado manualmente',
        );

        return back()->with('success', 'Agendamento cancelado com sucesso.');
    }
}
