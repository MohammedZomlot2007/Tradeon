<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeUpload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('The uploaded file is invalid.');

            return;
        }
        if ($value->getSize() > 10 * 1024 * 1024) {
            $fail('The uploaded file is too large.');

            return;
        }
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (! in_array(strtolower($value->extension()), $allowed, true)) {
            $fail('This file type is not allowed.');
        }
        $originalName = $value->getClientOriginalName();
        if (str_contains($originalName, '..') || str_contains($originalName, '/') || str_contains($originalName, '\\')) {
            $fail('The file name is invalid.');
        }
    }
}
