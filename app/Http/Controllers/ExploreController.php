<?php

namespace App\Http\Controllers;

use App\Models\BusinessRequest;
use App\Models\Company;
use App\Models\Post;
use App\Models\ProductShowcase;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    public function explore(): Response
    {
        $trendingCompanies = Company::latest()->take(6)->get();
        $trendingProducts = ProductShowcase::with('company')->latest()->take(6)->get();
        $recommendedManufacturers = Company::whereJsonContains('business_types', 'Manufacturer')->take(4)->get();
        $popularBusinessRequests = BusinessRequest::with('company')->latest()->take(4)->get();
        $popularPosts = Post::with(['company', 'user'])->latest()->take(5)->get();

        return Inertia::render('Explore', [
            'trendingCompanies' => $trendingCompanies,
            'trendingProducts' => $trendingProducts,
            'recommendedManufacturers' => $recommendedManufacturers,
            'popularBusinessRequests' => $popularBusinessRequests,
            'popularPosts' => $popularPosts,
        ]);
    }

    public function worldMap(): Response
    {
        $companies = Company::select(['id', 'name', 'slug', 'country', 'city', 'industry', 'business_types', 'verified_tier'])
            ->get();

        return Inertia::render('WorldMap', [
            'companies' => $companies,
        ]);
    }
}
