<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Company;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    public function index(): Response
    {
        $communities = Community::all();

        return Inertia::render('Communities/Index', [
            'communities' => $communities,
        ]);
    }

    public function show(string $slug): Response
    {
        $community = Community::where('slug', $slug)->firstOrFail();
        $posts = Post::where('industry', 'like', "%{$community->industry}%")
            ->with(['company', 'user'])
            ->latest()
            ->paginate(10);

        $companies = Company::where('industry', 'like', "%{$community->industry}%")
            ->take(6)
            ->get();

        return Inertia::render('Communities/Show', [
            'community' => $community,
            'posts' => $posts,
            'companies' => $companies,
        ]);
    }
}
