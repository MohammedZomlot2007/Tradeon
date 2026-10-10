<?php

namespace App\Http\Controllers;

use App\Models\MediaUpload;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function __construct(private StorageQuotaService $quota)
    {
    }

    /**
     * Upload a company media file (logo, cover, post/showcase/factory
     * image) to our own disk. The bytes count against the company's
     * storage quota — the `storage.quota` middleware blocks the upload
     * and sends the owner to the pricing page when the quota is full.
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'image', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $company = $this->quota->companyFor($request->user());

        if (! $company) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('flash.company_required')], 403);
            }

            return redirect()->back()->with('error', __('flash.company_required'));
        }

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];

        $path = $file->storeAs(
            'media/'.$company->id,
            Str::uuid().'.'.$file->extension(),
            'public'
        );

        $upload = $this->quota->recordUpload(
            $company,
            $request->user(),
            $path,
            'public',
            (int) $file->getSize(),
            $file->getMimeType()
        );

        $payload = [
            'id' => $upload->id,
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
            'bytes' => $upload->bytes,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, 201);
        }

        return redirect()->back()->with('success', __('flash.media_uploaded'));
    }

    /**
     * Delete a company media file. The freed bytes return to the
     * company's quota immediately.
     */
    public function destroy(Request $request, MediaUpload $mediaUpload): Response
    {
        $user = $request->user();

        abort_unless(
            $mediaUpload->company->user_id === $user->id || $user->isAdmin(),
            403
        );

        try {
            if (Storage::disk($mediaUpload->disk)->exists($mediaUpload->path)) {
                Storage::disk($mediaUpload->disk)->delete($mediaUpload->path);
            }
        } catch (\Throwable) {
            // The DB row is still removed so the quota is freed.
        }

        $mediaUpload->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back()->with('success', __('flash.media_removed'));
    }
}
