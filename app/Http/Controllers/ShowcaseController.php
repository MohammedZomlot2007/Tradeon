<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\ProductShowcase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShowcaseController extends Controller
{
    private function ownedCompany(): Company
    {
        return Company::where('user_id', auth()->id())->firstOrFail();
    }

    public function store(Request $request)
    {
        $company = $this->ownedCompany();

        $validated = $this->validateShowcase($request);

        $showcase = $company->productShowcases()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title']),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'showcase.created',
            'auditable_type' => ProductShowcase::class,
            'auditable_id' => $showcase->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.showcase_published'));
    }

    public function update(Request $request, ProductShowcase $showcase)
    {
        abort_unless($showcase->company_id === $this->ownedCompany()->id, 403);

        $validated = $this->validateShowcase($request);

        $showcase->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'showcase.updated',
            'auditable_type' => ProductShowcase::class,
            'auditable_id' => $showcase->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.showcase_updated'));
    }

    public function destroy(Request $request, ProductShowcase $showcase)
    {
        abort_unless($showcase->company_id === $this->ownedCompany()->id, 403);

        $showcase->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'showcase.deleted',
            'auditable_type' => ProductShowcase::class,
            'auditable_id' => $showcase->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.showcase_removed'));
    }

    private function validateShowcase(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'industry' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:150'],
            'specifications' => ['nullable', 'array', 'max:50'],
            'moq' => ['nullable', 'string', 'max:150'],
            'available_markets' => ['nullable', 'array', 'max:50'],
            'available_markets.*' => ['string', 'max:150'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['string', 'url', 'max:2048'],
            'lead_time' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'showcase';
        $slug = mb_substr($base, 0, 60);
        $counter = 1;

        while (ProductShowcase::where('slug', $slug)->exists()) {
            $slug = mb_substr($base, 0, 55).'-'.(++$counter);
        }

        return $slug;
    }
}
