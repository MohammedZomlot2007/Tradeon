<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SmartMatchController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('SmartMatch/Index');
    }

    public function match(Request $request)
    {
        $validated = $request->validate([
            'need_type' => 'required|string|max:100',
            'industry' => 'nullable|string|max:150',
            'target_country' => 'nullable|string|max:150',
            'moq_requirement' => 'nullable|string|max:150',
            'certifications' => 'nullable|array|max:20',
            'required_capacity' => 'nullable|string|max:150',
            'prompt' => 'nullable|string|max:2000',
        ]);

        $companies = Company::query()->with('factory')->latest()->limit(300)->get();
        $promptTerms = $this->keywords($validated['prompt'] ?? '');
        $wantedCerts = collect($validated['certifications'] ?? [])->map(fn ($v) => mb_strtolower((string) $v));
        $requiredMoq = $this->extractNumber($validated['moq_requirement'] ?? '');
        $requiredCapacity = $this->extractNumber($validated['required_capacity'] ?? '');

        $weights = [
            'business_type' => 25, 'industry' => 20, 'market' => 15, 'products' => 15,
            'moq' => 8, 'certification' => 7, 'capacity' => 5, 'trust' => 3, 'response' => 2,
        ];

        $matches = $companies->map(function (Company $company) use ($validated, $promptTerms, $wantedCerts, $requiredMoq, $requiredCapacity, $weights) {
            $score = 0;
            $reasons = [];
            $types = collect($company->business_types ?? [])->map(fn ($v) => mb_strtolower((string) $v));
            $needType = mb_strtolower($validated['need_type']);
            if ($types->contains($needType)) {
                $score += $weights['business_type'];
                $reasons[] = 'Business type matches your need.';
            }

            $industry = mb_strtolower((string) ($validated['industry'] ?? ''));
            $companyIndustry = mb_strtolower((string) $company->industry);
            if ($industry && (str_contains($companyIndustry, $industry) || collect($company->products_summary ?? [])->contains(fn ($v) => mb_stripos((string) $v, $industry) !== false))) {
                $score += $weights['industry'];
                $reasons[] = 'Industry/capability matches.';
            }

            $targetCountry = mb_strtolower((string) ($validated['target_country'] ?? ''));
            $markets = collect(array_merge([$company->country], $company->export_markets ?? [], $company->import_markets ?? [], $company->markets_served ?? []))->map(fn ($v) => mb_strtolower((string) $v));
            if ($targetCountry && $markets->contains($targetCountry)) {
                $score += $weights['market'];
                $reasons[] = 'Target market is covered.';
            }

            $searchable = mb_strtolower(implode(' ', array_filter([$company->name, $company->industry, $company->description, ...($company->products_summary ?? []), ...($company->services_summary ?? [])])));
            $termMatches = collect($promptTerms)->filter(fn ($term) => mb_strpos($searchable, $term) !== false)->count();
            if ($termMatches) {
                $bonus = min($weights['products'], max(2, $termMatches * 3));
                $score += $bonus;
                $reasons[] = 'Products/services match request keywords.';
            }

            $companyMoq = $this->extractNumber($company->moq);
            if ($requiredMoq !== null && $companyMoq !== null) {
                if ($companyMoq <= $requiredMoq) {
                    $score += $weights['moq'];
                    $reasons[] = 'MOQ fits your requirement.';
                } else {
                    $reasons[] = 'MOQ is above your stated requirement.';
                }
            }

            $companyCerts = collect(array_merge($company->certifications ?? [], $company->factory?->certifications ?? []))->map(fn ($v) => mb_strtolower((string) $v));
            if ($wantedCerts->isNotEmpty()) {
                $matchedCerts = $wantedCerts->filter(fn ($c) => $companyCerts->contains(fn ($x) => str_contains($x, $c)));
                if ($matchedCerts->isNotEmpty()) {
                    $score += $weights['certification'];
                    $reasons[] = 'Required certifications are present.';
                }
            }

            if ($requiredCapacity !== null) {
                $capacity = $this->extractNumber(($company->production_capacity ?? '').' '.($company->factory?->annual_output ?? ''));
                if ($capacity !== null && $capacity >= $requiredCapacity) {
                    $score += $weights['capacity'];
                    $reasons[] = 'Production capacity meets the request.';
                }
            }

            if ($company->verified_tier !== 'unverified') {
                $score += $weights['trust'];
                $reasons[] = 'Company has a verification tier.';
            }
            $responseRate = max(0, min(100, (int) $company->response_rate));
            $responseBonus = (int) round(($responseRate / 100) * $weights['response']);
            $score += $responseBonus;
            if ($responseRate >= 90) {
                $reasons[] = 'High response rate.';
            }

            return ['company' => $company, 'match_score' => min(100, $score), 'match_reasons' => array_values(array_unique($reasons))];
        })->filter(fn ($m) => $m['match_score'] >= 20)->sortByDesc('match_score')->take(12)->values();

        return response()->json(['matches' => $matches, 'parsed_criteria' => $validated, 'scoring_weights' => $weights]);
    }

    private function keywords(string $text): array
    {
        return collect(preg_split('/[^\pL\pN]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY))->filter(fn ($t) => mb_strlen($t) >= 3)->unique()->take(30)->values()->all();
    }

    private function extractNumber(?string $value): ?float
    {
        if (! $value) {
            return null;
        } preg_match('/(\d+(?:[.,]\d+)?)/', str_replace(',', '', $value), $m);

        return isset($m[1]) ? (float) $m[1] : null;
    }
}
