<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->account_type !== 'company' || !$user->company) {
            abort(403, 'Esta área é exclusiva para empresas.');
        }

        return $next($request);
    }
}
