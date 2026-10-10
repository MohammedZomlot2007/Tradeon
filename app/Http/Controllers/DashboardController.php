<?php

namespace App\Http\Controllers;

use App\Models\BusinessRequest;
use App\Models\Company;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $company = Company::where('user_id', auth()->id())->first();

        if (! $company) {
            return Inertia::render('Dashboard/Index', [
                'hasCompany' => false,
                'metrics' => null,
            ]);
        }

        $postsCount = Post::where('company_id', $company->id)->count();
        $requestsCount = BusinessRequest::where('company_id', $company->id)->count();

        $metrics = [
            'profile_views' => (int) $company->profile_views,
            'company_followers' => $company->followers_count,
            'connections_count' => $company->connections_count,
            'posts_count' => $postsCount,
            'requests_count' => $requestsCount,
            'response_rate' => $company->response_rate,
            'response_time' => $company->response_time,
            'completeness' => $this->profileCompleteness($company),
        ];

        return Inertia::render('Dashboard/Index', [
            'hasCompany' => true,
            'company' => $company,
            'metrics' => $metrics,
        ]);
    }

    private function profileCompleteness(Company $company): int
    {
        $fields = [
            $company->name,
            $company->country,
            $company->city,
            $company->industry,
            $company->company_size,
            $company->founded_year,
            $company->website,
            $company->description,
            $company->logo,
            $company->cover,
            $company->products_summary,
            $company->services_summary,
            $company->markets_served,
        ];

        $filled = collect($fields)->filter(function ($value) {
            return ! is_null($value) && $value !== '' && $value !== [];
        })->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}
