<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Inertia\Inertia;
use Inertia\Response;

class IntelligenceController extends Controller
{
    public function index(): Response
    {
        $countryStats = Company::selectRaw('country, count(*) as total')
            ->groupBy('country')
            ->orderBy('total', 'desc')
            ->get();

        $industryStats = Company::selectRaw('industry, count(*) as total')
            ->groupBy('industry')
            ->orderBy('total', 'desc')
            ->get();

        return Inertia::render('Intelligence/Index', [
            'countryStats' => $countryStats,
            'industryStats' => $industryStats,
        ]);
    }
}
