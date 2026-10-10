<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate(['locale' => ['required', 'in:en,ar']]);
        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        // Persist for authenticated users when the column exists (no migration forced)
        if ($request->user()) {
            try {
                $user = $request->user();
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'locale')) {
                    $user->forceFill(['locale' => $locale])->saveQuietly();
                }
            } catch (\Throwable $e) {
                // Never break language switching because of a missing column
            }
        }

        $response = back();

        // Long-lived cookie so guests keep their language (1 year)
        if (method_exists($response, 'withCookie')) {
            $response->withCookie(cookie('tradeon_locale', $locale, 60 * 24 * 365, '/', null, false, false));
        }

        return $response;
    }
}
