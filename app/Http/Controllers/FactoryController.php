<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Factory;
use Illuminate\Http\Request;

class FactoryController extends Controller
{
    private function ownedCompany(): Company
    {
        return Company::where('user_id', auth()->id())->firstOrFail();
    }

    public function store(Request $request)
    {
        $company = $this->ownedCompany();

        abort_if($company->factory()->exists(), 409, 'This company already has a factory profile.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'factory_type' => ['nullable', 'string', 'max:100'],
            'location_address' => ['nullable', 'string', 'max:500'],
            'production_lines' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'annual_output' => ['nullable', 'string', 'max:255'],
            'machinery' => ['nullable', 'array', 'max:50'],
            'machinery.*' => ['string', 'max:255'],
            'certifications' => ['nullable', 'array', 'max:50'],
            'certifications.*' => ['string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['string', 'url', 'max:2048'],
            'manufacturing_process' => ['nullable', 'string', 'max:10000'],
        ]);

        $factory = $company->factory()->create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'factory.created',
            'auditable_type' => Factory::class,
            'auditable_id' => $factory->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.factory_created'));
    }

    public function update(Request $request, Factory $factory)
    {
        abort_unless($factory->company_id === $this->ownedCompany()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'factory_type' => ['nullable', 'string', 'max:100'],
            'location_address' => ['nullable', 'string', 'max:500'],
            'production_lines' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'annual_output' => ['nullable', 'string', 'max:255'],
            'machinery' => ['nullable', 'array', 'max:50'],
            'machinery.*' => ['string', 'max:255'],
            'certifications' => ['nullable', 'array', 'max:50'],
            'certifications.*' => ['string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['string', 'url', 'max:2048'],
            'manufacturing_process' => ['nullable', 'string', 'max:10000'],
        ]);

        $factory->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'factory.updated',
            'auditable_type' => Factory::class,
            'auditable_id' => $factory->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.factory_updated'));
    }

    public function destroy(Request $request, Factory $factory)
    {
        abort_unless($factory->company_id === $this->ownedCompany()->id, 403);

        $factory->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'factory.deleted',
            'auditable_type' => Factory::class,
            'auditable_id' => $factory->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.factory_removed'));
    }
}
