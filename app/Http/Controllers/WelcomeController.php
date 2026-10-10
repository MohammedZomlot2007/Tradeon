<?php

namespace App\Http\Controllers;

use App\Models\BusinessRequest;
use App\Models\Company;
use App\Models\Connection;
use App\Models\Rfq;
use App\Models\Verification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $company = Company::where('user_id', auth()->id())->first();

        if (! $company) {
            return redirect()->route('onboarding.company');
        }

        $following = Connection::where('user_id', auth()->id())->where('type', 'follow')->count();
        $connections = Connection::where('user_id', auth()->id())->where('type', 'connection')->count();

        return Inertia::render('Welcome/Index', [
            'company' => $company,
            'progress' => [
                'profile' => $this->profileCompleteness($company),
                'verified' => $company->verified_tier !== 'unverified',
                'verification_status' => Verification::where('company_id', $company->id)->latest()->value('status'),
                'products' => $company->productShowcases()->count(),
                'network' => $following + $connections,
                'rfqs' => Rfq::where('user_id', auth()->id())->count(),
                'requests' => BusinessRequest::where('user_id', auth()->id())->count(),
            ],
        ]);
    }

    private function profileCompleteness(Company $company): int
    {
        $fields = [
            $company->name, $company->country, $company->city, $company->industry,
            $company->company_size, $company->founded_year, $company->website,
            $company->description, $company->logo, $company->cover,
            $company->products_summary, $company->services_summary, $company->markets_served,
        ];

        $filled = collect($fields)->filter(fn ($value) => ! is_null($value) && $value !== '' && $value !== [])->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}
