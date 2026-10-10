<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Connection;
use App\Models\SavedItem;
use App\Services\StorageQuotaService;
use App\Support\BusinessTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function onboarding(): Response
    {
        abort_if(auth()->user()->company()->exists(), 409, 'Company profile already exists.');

        return Inertia::render('Onboarding/Company', [
            'businessTypes' => session('onboarding.business_types', []),
            'industry' => session('onboarding.industry', ''),
            'options' => [
                'companySizes' => BusinessTaxonomy::COMPANY_SIZES,
                'businessTypes' => BusinessTaxonomy::businessTypes(),
                'industries' => BusinessTaxonomy::industries(),
            ],
        ]);
    }

    public function storeOnboarding(Request $request)
    {
        abort_if($request->user()->company()->exists(), 409, 'Company profile already exists.');

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'company_size' => ['nullable', Rule::in(BusinessTaxonomy::COMPANY_SIZES)],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.now()->year],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            // Fallback for accounts without registration session data (e.g. seeded users):
            // validated strictly against the taxonomy, same as registration.
            'business_types' => ['nullable', 'array', 'min:1', 'max:5'],
            'business_types.*' => ['string', Rule::in(BusinessTaxonomy::businessTypes())],
            'industry' => ['nullable', 'string', Rule::in(BusinessTaxonomy::industries())],
            'logo_file' => ['nullable', 'file', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
            'cover_file' => ['nullable', 'file', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $businessTypes = array_values(array_intersect(
            array_filter(session('onboarding.business_types', [])),
            BusinessTaxonomy::businessTypes()
        ));
        $industry = session('onboarding.industry');

        if (empty($businessTypes) || ! in_array($industry, BusinessTaxonomy::industries(), true)) {
            // Fall back to values submitted directly on the onboarding form.
            $businessTypes = array_values(array_intersect(
                $validated['business_types'] ?? [],
                BusinessTaxonomy::businessTypes()
            ));
            $industry = in_array($validated['industry'] ?? null, BusinessTaxonomy::industries(), true)
                ? $validated['industry']
                : null;
        }

        if (empty($businessTypes) || ! $industry) {
            return redirect()->route('register')->withErrors([
                'business_types' => 'Choose at least one business type and industry before continuing.',
            ]);
        }

        $company = Company::create([
            'user_id' => $request->user()->id,
            'name' => $validated['company_name'],
            'slug' => Str::slug($validated['company_name']).'-'.Str::lower(Str::random(6)),
            'country' => $validated['country'],
            'city' => $validated['city'],
            'business_types' => $businessTypes,
            'industry' => $industry,
            'company_size' => $validated['company_size'] ?? null,
            'founded_year' => $validated['founded_year'] ?? null,
            'website' => $validated['website'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Company logo/cover uploads are stored on our disk and count
        // against the company storage quota.
        $quota = app(StorageQuotaService::class);

        foreach (['logo_file' => 'logo', 'cover_file' => 'cover'] as $field => $column) {
            if (! isset($validated[$field])) {
                continue;
            }

            $file = $validated[$field];
            $path = $file->storeAs(
                'media/'.$company->id,
                Str::uuid().'.'.$file->extension(),
                'public'
            );

            $quota->recordUpload(
                $company,
                $request->user(),
                $path,
                'public',
                (int) $file->getSize(),
                $file->getMimeType()
            );

            $company->update([$column => Storage::disk('public')->url($path)]);
        }

        $request->session()->forget('onboarding');

        return redirect()->route('welcome')->with('success', __('flash.profile_ready'));
    }

    /**
     * Display all companies directory.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('q');
        $country = $request->query('country');
        $industry = $request->query('industry');
        $businessType = $request->query('business_type');
        $verified = $request->query('verified');

        $query = Company::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        if ($country) {
            $query->where('country', $country);
        }

        if ($industry) {
            $query->where('industry', $industry);
        }

        if ($businessType) {
            $query->whereJsonContains('business_types', $businessType);
        }

        if ($verified) {
            $query->where('verified_tier', '!=', 'unverified');
        }

        $companies = $query->latest()->paginate(12);
        $this->decorateViewerState($companies->getCollection());

        return Inertia::render('Businesses/Index', [
            'companies' => $companies,
            'filters' => compact('search', 'country', 'industry', 'businessType', 'verified'),
        ]);
    }

    /**
     * Dedicated Manufacturer Directory.
     */
    public function manufacturers(Request $request): Response
    {
        $search = $request->query('q');
        $country = $request->query('country');
        $industry = $request->query('industry');
        $moq = $request->query('moq');

        $query = Company::whereJsonContains('business_types', 'Manufacturer')->with('factory');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($country) {
            $query->where('country', $country);
        }
        if ($industry) {
            $query->where('industry', $industry);
        }
        if ($moq !== null && $moq !== '') {
            $query->where('moq', 'like', '%'.$moq.'%');
        }

        $manufacturers = $query->latest()->paginate(12);
        $this->decorateViewerState($manufacturers->getCollection());

        return Inertia::render('Manufacturers/Index', [
            'manufacturers' => $manufacturers,
            'filters' => compact('search', 'country', 'industry', 'moq'),
        ]);
    }

    /**
     * Dedicated Supplier Directory.
     */
    public function suppliers(Request $request): Response
    {
        $search = $request->query('q');
        $country = $request->query('country');
        $industry = $request->query('industry');

        $query = Company::whereJsonContains('business_types', 'Supplier');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($country) {
            $query->where('country', $country);
        }
        if ($industry) {
            $query->where('industry', $industry);
        }

        $suppliers = $query->latest()->paginate(12);
        $this->decorateViewerState($suppliers->getCollection());

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => compact('search', 'country', 'industry'),
        ]);
    }

    /**
     * Display Company Profile Page with 9 interactive tabs.
     */
    private function decorateViewerState($companies): void
    {
        if (! auth()->check() || $companies->isEmpty()) {
            return;
        }

        $companyIds = $companies->pluck('id');
        $connections = Connection::where('user_id', auth()->id())
            ->whereIn('target_company_id', $companyIds)
            ->whereIn('type', ['follow', 'connection'])
            ->get(['target_company_id', 'type'])
            ->groupBy('target_company_id');

        $saved = SavedItem::where('user_id', auth()->id())
            ->where('item_type', 'company')
            ->whereIn('item_id', $companyIds)
            ->pluck('item_id')
            ->flip();

        $companies->each(function ($company) use ($connections, $saved) {
            $state = $connections->get($company->id, collect());
            $company->setAttribute('viewer_following', $state->contains('type', 'follow'));
            $company->setAttribute('viewer_connected', $state->contains('type', 'connection'));
            $company->setAttribute('viewer_saved', $saved->has($company->id));
        });
    }

    public function show(string $slug): Response
    {
        $company = Company::where('slug', $slug)
            ->with(['factory', 'productShowcases', 'posts', 'businessRequests', 'events', 'user'])
            ->firstOrFail();

        $isConnected = false;
        $isFollowing = false;

        if (! auth()->check() || auth()->id() !== $company->user_id) {
            $viewKey = 'company_viewed_'.$company->id;
            if (! session()->has($viewKey) || session($viewKey) < now()->subHour()->timestamp) {
                $company->increment('profile_views');
                session()->put($viewKey, now()->timestamp);
                $company->refresh();
            }
        }

        if (auth()->check()) {
            $isConnected = Connection::where('user_id', auth()->id())
                ->where('target_company_id', $company->id)
                ->where('type', 'connection')
                ->exists();

            $isFollowing = Connection::where('user_id', auth()->id())
                ->where('target_company_id', $company->id)
                ->where('type', 'follow')
                ->exists();
        }

        return Inertia::render('Companies/Show', [
            'company' => $company,
            'isConnected' => $isConnected,
            'isFollowing' => $isFollowing,
            'isOwner' => auth()->check() && auth()->id() === $company->user_id,
        ]);
    }

    public function follow($id)
    {
        $company = Company::findOrFail($id);

        $following = DB::transaction(function () use ($company) {
            $existing = Connection::where('user_id', auth()->id())
                ->where('target_company_id', $company->id)
                ->where('type', 'follow')
                ->first();

            if ($existing) {
                $existing->delete();
                Company::whereKey($company->id)->where('followers_count', '>', 0)->decrement('followers_count');

                return false;
            }

            Connection::firstOrCreate([
                'user_id' => auth()->id(),
                'target_company_id' => $company->id,
                'type' => 'follow',
            ], ['status' => 'accepted']);
            $company->increment('followers_count');

            return true;
        });

        return back()->with('success', $following ? __('flash.company_followed') : __('flash.company_unfollowed'));
    }

    public function connect($id)
    {
        $company = Company::findOrFail($id);
        abort_if($company->user_id === auth()->id(), 422, 'You cannot connect with your own company.');

        $created = DB::transaction(function () use ($company) {
            $existing = Connection::where('user_id', auth()->id())
                ->where('target_company_id', $company->id)
                ->where('type', 'connection')
                ->first();

            if ($existing) {
                return false;
            }

            Connection::firstOrCreate([
                'user_id' => auth()->id(),
                'target_company_id' => $company->id,
                'type' => 'connection',
            ], ['status' => 'accepted']);
            $company->increment('connections_count');

            return true;
        });

        return back()->with('success', $created ? __('flash.connection_created') : __('flash.connection_exists'));
    }

    public function save($id)
    {
        $company = Company::findOrFail($id);

        $removed = DB::transaction(function () use ($company) {
            $existing = SavedItem::where('user_id', auth()->id())
                ->where('item_type', 'company')
                ->where('item_id', $company->id)
                ->first();

            if ($existing) {
                $existing->delete();

                return true;
            }

            SavedItem::firstOrCreate([
                'user_id' => auth()->id(),
                'item_type' => 'company',
                'item_id' => $company->id,
            ], ['collection_name' => 'Default']);

            return false;
        });

        return back()->with('success', $removed ? __('flash.unsaved') : __('flash.saved'));
    }
}
