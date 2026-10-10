<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Priority: session > authenticated user preference > cookie > Accept-Language > config default
        $locale = $request->session()->get('locale');

        if (! $locale && $request->user() && ! empty($request->user()->locale)) {
            $locale = $request->user()->locale;
        }

        if (! $locale) {
            $locale = $request->cookie('tradeon_locale');
        }

        if (! $locale) {
            $accept = (string) $request->header('Accept-Language', '');
            // e.g. "ar-EG,ar;q=0.9,en;q=0.8" -> pick first supported
            foreach (preg_split('/[,\s;]+/', $accept) as $part) {
                $short = strtolower(substr($part, 0, 2));
                if (in_array($short, ['en', 'ar'], true)) {
                    $locale = $short;
                    break;
                }
            }
        }

        if (! $locale) {
            $locale = config('app.locale', 'en');
        }

        if (! in_array($locale, ['en', 'ar'], true)) {
            $locale = 'en';
        }
        app()->setLocale($locale);

        /** @var \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Persist the FINAL locale (controller may have just switched it via /locale).
        // Use app()->getLocale() after $next so we never overwrite a fresh switch with a stale value.
        $finalLocale = app()->getLocale();
        if (! in_array($finalLocale, ['en', 'ar'], true)) {
            $finalLocale = 'en';
        }

        if (method_exists($response, 'withCookie')) {
            $response->withCookie(cookie('tradeon_locale', $finalLocale, 60 * 24 * 365, '/', null, false, false));
        }

        return $response;
    }
}
