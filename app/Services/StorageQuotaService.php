<?php

namespace App\Services;

use App\Models\Company;
use App\Models\MediaUpload;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageQuotaService
{
    /**
     * Quota in bytes for the given user, or null when unlimited.
     *
     * Users with an active subscription use their plan's
     * `limits.storage_mb` (-1 means unlimited). Everyone else gets the
     * free quota from config (30 MB by default, granted at registration).
     */
    public function quotaBytes(User $user): ?int
    {
        $subscription = $user->activeSubscription()->with('plan')->first();

        $limitMb = $subscription?->plan?->limits['storage_mb'] ?? null;

        if ($limitMb === null) {
            $limitMb = (int) config('billing.free_storage_mb', 30);
        }

        if ((int) $limitMb < 0) {
            return null;
        }

        return (int) $limitMb * 1024 * 1024;
    }

    public function companyFor(User $user): ?Company
    {
        return Company::where('user_id', $user->id)->first();
    }

    /**
     * Real bytes the company stores on our disks: tracked media uploads
     * plus verification documents (private disk). Missing files are
     * ignored so a deleted file never blocks publishing.
     */
    public function usedBytes(?Company $company): int
    {
        if (! $company) {
            return 0;
        }

        $used = (int) MediaUpload::where('company_id', $company->id)->sum('bytes');

        $verifications = Verification::where('company_id', $company->id)->pluck('document_url');

        foreach ($verifications as $path) {
            if (! $path) {
                continue;
            }

            try {
                if (Storage::disk('local')->exists($path)) {
                    $used += (int) Storage::disk('local')->size($path);
                }
            } catch (\Throwable) {
                // Ignore unreadable files when measuring quota usage.
            }
        }

        return $used;
    }

    public function remainingBytes(User $user, ?Company $company = null): ?int
    {
        $quota = $this->quotaBytes($user);

        if ($quota === null) {
            return null;
        }

        $company ??= $this->companyFor($user);

        return max(0, $quota - $this->usedBytes($company));
    }

    public function canStore(User $user, int $incomingBytes, ?Company $company = null): bool
    {
        $quota = $this->quotaBytes($user);

        if ($quota === null) {
            return true;
        }

        $company ??= $this->companyFor($user);

        return ($this->usedBytes($company) + $incomingBytes) <= $quota;
    }

    /**
     * Sum of the real uploaded file sizes inside the request.
     * URL-string fields (external media) count as zero bytes.
     */
    public static function incomingBytes(Request $request): int
    {
        $total = 0;
        $files = $request->allFiles();

        array_walk_recursive($files, function ($file) use (&$total) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $total += (int) $file->getSize();
            }
        });

        return $total;
    }

    public function recordUpload(Company $company, User $user, string $path, string $disk, int $bytes, ?string $mime = null): MediaUpload
    {
        return MediaUpload::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'disk' => $disk,
            'path' => $path,
            'bytes' => max(0, $bytes),
            'mime' => $mime,
        ]);
    }

    /**
     * Usage summary for the billing page / UI.
     */
    public function usageFor(User $user): array
    {
        $company = $this->companyFor($user);
        $quota = $this->quotaBytes($user);
        $used = $this->usedBytes($company);

        return [
            'has_company' => $company !== null,
            'used_bytes' => $used,
            'quota_bytes' => $quota,
            'used_mb' => round($used / 1024 / 1024, 2),
            'quota_mb' => $quota === null ? null : round($quota / 1024 / 1024, 2),
            'unlimited' => $quota === null,
            'percent' => $quota === null || $quota <= 0 ? 0 : (int) min(100, floor(($used / $quota) * 100)),
        ];
    }
}
