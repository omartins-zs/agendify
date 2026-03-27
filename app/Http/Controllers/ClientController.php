<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $clients = Client::query()
            ->forCompany($request->user()->company_id)
            ->withCount('appointments')
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'notes' => $client->notes,
                'appointments_count' => $client->appointments_count,
                'last_visit_at' => $client->last_visit_at?->toIso8601String(),
            ]);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
        ]);

        return back()->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return back()->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return back()->with('success', 'Cliente removido com sucesso.');
    }
}
