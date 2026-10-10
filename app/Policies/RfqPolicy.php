<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Rfq;
use App\Models\User;

class RfqPolicy
{
    public function update(User $user, Rfq $rfq): bool
    {
        return $user->id === $rfq->user_id || ($rfq->company && $rfq->company->user_id === $user->id);
    }

    public function delete(User $user, Rfq $rfq): bool
    {
        return $this->update($user, $rfq);
    }

    public function quote(User $user, Rfq $rfq): bool
    {
        if ($rfq->status !== 'active') {
            return false;
        }

        if ($user->id === $rfq->user_id) {
            return false;
        }

        $company = Company::where('user_id', $user->id)->first();

        if (! $company) {
            return false;
        }

        if ($rfq->company_id && (int) $rfq->company_id === (int) $company->id) {
            return false;
        }

        return ! $rfq->quotes()->where('company_id', $company->id)->exists();
    }
}
