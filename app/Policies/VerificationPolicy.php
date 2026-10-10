<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Verification;

class VerificationPolicy
{
    public function view(User $user, Verification $verification): bool
    {
        return $user->isAdmin() || $verification->company?->user_id === $user->id;
    }

    public function download(User $user, Verification $verification): bool
    {
        return $this->view($user, $verification);
    }
}
