<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Flash message shown when an authenticated user without an active
     * subscription tries to reach a protected application route.
     */
    public const FLASH_MESSAGE = 'لازم يكون عندك اشتراك فعال للوصول إلى هذه الصفحة.';

    /**
     * JSON message returned to API requests made without an active subscription.
     */
    public const JSON_MESSAGE = 'You need an active subscription to access this resource.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Unauthenticated requests are left for the auth middleware.
        if (! $user) {
            return $next($request);
        }

        if ($this->hasActiveSubscription($user)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => self::JSON_MESSAGE], 403);
        }

        return redirect()->route('billing.pricing')
            ->with('error', self::FLASH_MESSAGE);
    }

    /**
     * The subscription belongs to the authenticated user, who owns the
     * company/tenant record. Checking the user's subscription therefore
     * applies to the current company only — one company's expired
     * subscription can never lock another company's backend.
     *
     * Only the exact "active" status unlocks the backend. pending,
     * trialing, past_due, paused, cancelled and any other state keep
     * the company locked.
     */
    private function hasActiveSubscription($user): bool
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->exists();
    }
}
