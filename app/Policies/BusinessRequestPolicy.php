<?php

namespace App\Policies;

use App\Models\BusinessRequest;
use App\Models\User;

class BusinessRequestPolicy
{
    public function update(User $user, BusinessRequest $request): bool
    {
        return $user->id === $request->user_id || ($request->company && $request->company->user_id === $user->id);
    }

    public function delete(User $user, BusinessRequest $request): bool
    {
        return $this->update($user, $request);
    }

    public function quote(User $user, BusinessRequest $request): bool
    {
        return $user->id !== $request->user_id;
    }
}
