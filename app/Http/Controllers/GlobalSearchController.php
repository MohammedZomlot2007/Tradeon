<?php

namespace App\Http\Controllers;

use App\Models\BusinessRequest;
use App\Models\Company;
use App\Models\ProductShowcase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSearchController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'in:all,companies,manufacturers,products,requests'],
            'country' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'verified' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $type = $validated['type'] ?? 'all';
        $like = '%'.$q.'%';
        $perPage = (int) ($validated['per_page'] ?? 15);
        // Use MySQL fulltext in production when the query is meaningful; fall back to LIKE otherwise (SQLite/tests).
        $useFulltext = $q !== ''
            && mb_strlen($q) >= 3
            && \Illuminate\Support\Facades\DB::getDriverName() === 'mysql';

        $companies = collect();
        $products = collect();
        $requests = collect();
        if ($type === 'all' || $type === 'companies' || $type === 'manufacturers') {
            $query = Company::query()->select(['id', 'user_id', 'name', 'slug', 'logo', 'country', 'city', 'industry', 'business_types', 'verified_tier'])
                ->when($q, function ($query) use ($q, $like, $useFulltext) {
                    if ($useFulltext) {
                        $query->whereRaw('MATCH(name, description, industry) AGAINST(? IN NATURAL LANGUAGE MODE)', [$q]);
                    } else {
                        $query->where(fn ($x) => $x->where('name', 'like', $like)->orWhere('description', 'like', $like)->orWhere('industry', 'like', $like));
                    }
                })
                ->when($validated['country'] ?? null, fn ($query, $country) => $query->where('country', $country))
                ->when($validated['industry'] ?? null, fn ($query, $industry) => $query->where('industry', $industry))
                ->when(($validated['verified'] ?? null) === true, fn ($query) => $query->where('verified_tier', '!=', 'unverified'))
                ->when($type === 'manufacturers', fn ($query) => $query->whereJsonContains('business_types', 'Manufacturer'));
            $companies = $query->latest()->paginate($perPage, ['*'], 'companies_page')->withQueryString();
        }

        if ($type === 'all' || $type === 'products') {
            $products = ProductShowcase::query()->with('company:id,name,slug,logo,country,industry')
                ->when($q, function ($query) use ($q, $like, $useFulltext) {
                    if ($useFulltext) {
                        $query->whereRaw('MATCH(title, description, category) AGAINST(? IN NATURAL LANGUAGE MODE)', [$q]);
                    } else {
                        $query->where(fn ($x) => $x->where('title', 'like', $like)->orWhere('description', 'like', $like)->orWhere('category', 'like', $like));
                    }
                })
                ->when($validated['industry'] ?? null, fn ($query, $industry) => $query->where('industry', $industry))
                ->latest()->paginate($perPage, ['*'], 'products_page')->withQueryString();
        }

        if ($type === 'all' || $type === 'requests') {
            $requests = BusinessRequest::query()->with('company:id,name,slug,logo,country,industry')
                ->where('status', 'open')
                ->when($q, function ($query) use ($q, $like, $useFulltext) {
                    if ($useFulltext) {
                        $query->whereRaw('MATCH(title, description, industry) AGAINST(? IN NATURAL LANGUAGE MODE)', [$q]);
                    } else {
                        $query->where(fn ($x) => $x->where('title', 'like', $like)->orWhere('description', 'like', $like)->orWhere('industry', 'like', $like));
                    }
                })
                ->when($validated['industry'] ?? null, fn ($query, $industry) => $query->where('industry', $industry))
                ->latest()->paginate($perPage, ['*'], 'requests_page')->withQueryString();
        }

        return Inertia::render('Search/Index', [
            'results' => compact('companies', 'products', 'requests'),
            'filters' => $validated,
            'query' => $q,
        ]);
    }
}
