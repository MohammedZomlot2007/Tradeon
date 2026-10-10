<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\BusinessTaxonomy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'businessTypes' => BusinessTaxonomy::businessTypes(),
            'industries' => BusinessTaxonomy::industries(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_types' => ['required', 'array', 'min:1', 'max:5'],
            'business_types.*' => ['string', Rule::in(BusinessTaxonomy::businessTypes())],
            'industry' => ['required', 'string', Rule::in(BusinessTaxonomy::industries())],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $this->uniqueUsername((string) $request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $this->grantFreePlan($user);

        Auth::login($user);

        $request->session()->put('onboarding.business_types', array_values($request->input('business_types')));
        $request->session()->put('onboarding.industry', $request->input('industry'));

        return redirect(route('onboarding.company', absolute: false));
    }

    /**
     * Give every new account the free plan (limited publishing storage,
     * e.g. 30 MB). When the company exceeds it, the storage middleware
     * sends the owner to the pricing page to upgrade.
     */
    private function grantFreePlan(User $user): void
    {
        if ($user->subscriptions()->where('status', 'active')->exists()) {
            return;
        }

        $plan = SubscriptionPlan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'FREE',
                'description' => 'Free workspace for every business, with limited publishing storage.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'USD',
                'limits' => ['storage_mb' => (int) config('billing.free_storage_mb', 30)],
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'provider' => null,
            'billing_interval' => 'monthly',
            'status' => 'active',
            'starts_at' => now(),
        ]);
    }

    private function uniqueUsername(string $name): string
    {
        $base = Str::slug($name) ?: 'user';
        $base = mb_substr($base, 0, 40);
        $candidate = $base;
        $attempts = 0;

        while (User::where('username', $candidate)->exists() && $attempts < 10) {
            $candidate = $base.'-'.Str::lower(Str::random(4));
            $attempts++;
        }

        return $candidate;
    }
}
