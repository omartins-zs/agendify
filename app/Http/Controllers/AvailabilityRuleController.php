<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilityRuleRequest;
use App\Http\Requests\UpdateAvailabilityRuleRequest;
use App\Models\AvailabilityRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityRuleController extends Controller
{
    public function index(Request $request): Response
    {
        $rules = AvailabilityRule::query()
            ->forCompany($request->user()->company_id)
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get()
            ->map(fn (AvailabilityRule $rule): array => [
                'id' => $rule->id,
                'weekday' => $rule->weekday,
                'start_time' => $rule->start_time,
                'end_time' => $rule->end_time,
                'slot_interval_minutes' => $rule->slot_interval_minutes,
                'is_active' => $rule->is_active,
            ]);

        return Inertia::render('Availability/Index', [
            'rules' => $rules,
        ]);
    }

    public function store(StoreAvailabilityRuleRequest $request): RedirectResponse
    {
        AvailabilityRule::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Regra de horario criada com sucesso.');
    }

    public function update(UpdateAvailabilityRuleRequest $request, AvailabilityRule $availabilityRule): RedirectResponse
    {
        abort_unless($availabilityRule->company_id === $request->user()->company_id, 404);

        $availabilityRule->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Regra de horario atualizada com sucesso.');
    }

    public function destroy(Request $request, AvailabilityRule $availabilityRule): RedirectResponse
    {
        abort_unless($availabilityRule->company_id === $request->user()->company_id, 404);

        $availabilityRule->delete();

        return back()->with('success', 'Regra de horario removida com sucesso.');
    }
}
