<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyCanPublish
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $company = $user?->company;

        if (!$user?->hasVerifiedEmail()) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Confirme o email antes de publicar vagas.');
        }

        if (!$company?->isApproved()) {
            return redirect()->route('company.dashboard')
                ->with('error', 'A empresa precisa de ser aprovada antes de publicar vagas.');
        }

        return $next($request);
    }
}
