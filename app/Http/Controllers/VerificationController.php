<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Verification;
use App\Rules\SafeUpload;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    public function index(): Response
    {
        $company = Company::where('user_id', auth()->id())->first();

        $verifications = $company
            ? Verification::where('company_id', $company->id)->latest()->get()
            : collect();

        return Inertia::render('Verification/Index', [
            'company' => $company,
            'verifications' => $verifications,
            'options' => [
                'types' => ['identity', 'company', 'factory'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'verification_type' => ['required', 'in:identity,company,factory'],
            'document_type' => ['required', 'string', 'max:100'],
            'document' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf', new SafeUpload],
        ]);

        $company = Company::where('user_id', auth()->id())->firstOrFail();

        abort_if(
            Verification::where('company_id', $company->id)
                ->where('verification_type', $validated['verification_type'])
                ->where('status', 'pending')
                ->exists(),
            409,
            'A pending verification of this type already exists.'
        );

        $file = $validated['document'];
        $path = $file->storeAs(
            'verifications/'.$company->id,
            Str::uuid().'.'.$file->extension(),
            'local'
        );

        // The document bytes count against the company storage quota.
        app(StorageQuotaService::class)->recordUpload(
            $company,
            $request->user(),
            $path,
            'local',
            (int) $file->getSize(),
            $file->getMimeType()
        );

        $verification = Verification::create([
            'company_id' => $company->id,
            'verification_type' => $validated['verification_type'],
            'document_type' => $validated['document_type'],
            'document_url' => $path,
            'status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'verification.submitted',
            'auditable_type' => Verification::class,
            'auditable_id' => $verification->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', __('flash.verification_submitted'));
    }

    public function download(Verification $verification): StreamedResponse
    {
        Gate::authorize('download', $verification);

        abort_unless(Storage::disk('local')->exists($verification->document_url), 404);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'verification.document_accessed',
            'auditable_type' => Verification::class,
            'auditable_id' => $verification->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('local')->download($verification->document_url);
    }
}
