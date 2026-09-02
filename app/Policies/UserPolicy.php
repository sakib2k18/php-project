<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /** Nobody edits somebody else's profile — not even the administrator. */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * The administrator may deactivate members, but never themselves and never
     * another administrator.
     */
    public function toggleActive(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id && ! $model->isAdmin();
    }

    /**
     * Deleting is limited to non-admin accounts other than the current user, so
     * the single administrator account can never be removed.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id && ! $model->isAdmin();
    }
}
