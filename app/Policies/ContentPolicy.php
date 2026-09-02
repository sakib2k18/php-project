<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared authorisation for every editorial resource (campaigns, projects,
 * events, stories, posts, announcements, gallery, team members).
 *
 * Content is authored by the organisation, so the rule is simply: the
 * administrator manages it, everyone else reads the published version.
 */
class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, mixed $model = null): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, mixed $model = null): bool
    {
        return $user->isAdmin();
    }
}
