<?php

namespace App\Http\Middleware;

use App\Services\StorageQuotaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorageQuota
{
    /**
     * Flash message shown when a company owner tries to publish while
     * their storage quota is full. They are sent to the pricing page
     * to upgrade.
     */
    public const FLASH_MESSAGE = 'وصلت للحد الأقصى لمساحة النشر في خطتك. قم بالترقية لمتابعة النشر.';

    /**
     * JSON message returned to API requests made over the quota.
     */
    public const JSON_MESSAGE = 'Storage quota exceeded. Upgrade your plan to continue publishing.';

    public function __construct(private StorageQuotaService $quota)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Unauthenticated requests are left for the auth middleware.
        if (! $user) {
            return $next($request);
        }

        $company = $this->quota->companyFor($user);

        // Users without a company page store nothing yet.
        if (! $company) {
            return $next($request);
        }

        $quotaBytes = $this->quota->quotaBytes($user);

        // Unlimited plans (e.g. PRO) never hit the paywall.
        if ($quotaBytes === null) {
            return $next($request);
        }

        $incoming = StorageQuotaService::incomingBytes($request);

        if (($this->quota->usedBytes($company) + $incoming) > $quotaBytes) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => self::JSON_MESSAGE,
                    'upgrade_url' => route('billing.pricing'),
                ], 402);
            }

            return redirect()->route('billing.pricing')
                ->with('error', self::FLASH_MESSAGE);
        }

        return $next($request);
    }
}
