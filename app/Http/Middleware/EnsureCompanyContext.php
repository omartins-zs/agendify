<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->company_id || ! $user->isActive()) {
            abort(Response::HTTP_FORBIDDEN, 'Usuario sem empresa ativa.');
        }

        $routeCompany = $request->route('company');

        if ($routeCompany instanceof Company && $routeCompany->id !== $user->company_id) {
            abort(Response::HTTP_FORBIDDEN, 'Acesso negado para esta empresa.');
        }

        return $next($request);
    }
}
