<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardMetricsService $metricsService): Response
    {
        $company = $request->user()->company()->with('settings')->firstOrFail();

        return Inertia::render('Dashboard', [
            'company' => [
                'name' => $company->name,
                'slug' => $company->slug,
            ],
            ...$metricsService->build($company),
        ]);
    }
}
