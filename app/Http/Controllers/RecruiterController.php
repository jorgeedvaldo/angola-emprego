<?php

namespace App\Http\Controllers;

use App\Models\Company;

/**
 * Página de entrada para quem contrata: reúne num só sítio o registo da empresa,
 * a lista de empresas e o analisador de CVs.
 */
class RecruiterController extends Controller
{
    public function index()
    {
        $companiesCount = Company::where('approval_status', 'approved')
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->count();

        $latestCompanies = Company::where('approval_status', 'approved')
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('recruiters.index', compact('companiesCount', 'latestCompanies'));
    }
}
