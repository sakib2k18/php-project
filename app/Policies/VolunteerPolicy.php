<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Volunteer;

class VolunteerPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Listing every application is administrator-only. The method has to exist
     * even though before() short-circuits it: Laravel skips the policy entirely
     * when the ability method is missing, so before() would never be consulted.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Volunteer $volunteer): bool
    {
        return $volunteer->user_id === $user->id;
    }

    /**
     * One volunteer application per supporter.
     *
     * This queries the table rather than reading `$user->volunteer`, because a
     * lazily-loaded relation caches its first result on the model instance and
     * would go stale within a single request.
     */
    public function create(User $user): bool
    {
        return $user->is_active
            && ! Volunteer::query()->where('user_id', $user->id)->exists();
    }

    /** A supporter may correct their own application while it is still pending. */
    public function update(User $user, Volunteer $volunteer): bool
    {
        return $volunteer->user_id === $user->id
            && $volunteer->status === Volunteer::STATUS_PENDING;
    }

    public function review(User $user, Volunteer $volunteer): bool
    {
        return false;
    }

    public function delete(User $user, Volunteer $volunteer): bool
    {
        return false;
    }
}
