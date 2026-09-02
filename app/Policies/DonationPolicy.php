<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

class DonationPolicy
{
    /**
     * Anything an administrator asks for on donations is allowed; the checks
     * below then describe what a *supporter* may do with their own records.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** A supporter may list donations — the query is scoped to their own. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** A supporter may only ever open a donation they submitted. */
    public function view(User $user, Donation $donation): bool
    {
        return $donation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Supporters never edit a submitted donation: amount and status are
     * financial data reviewed by the administrator.
     */
    public function update(User $user, Donation $donation): bool
    {
        return false;
    }

    public function delete(User $user, Donation $donation): bool
    {
        return false;
    }

    /** Only the administrator reviews donations (covered by before()). */
    public function review(User $user, Donation $donation): bool
    {
        return false;
    }

    /** A receipt exists only once the donation has been approved. */
    public function downloadReceipt(User $user, Donation $donation): bool
    {
        return $donation->user_id === $user->id && $donation->is_approved;
    }
}
