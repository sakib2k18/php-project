<?php

namespace App\Http\Requests;

/**
 * Same field rules as the application form, different authorisation: editing is
 * allowed for the owner while the application is still pending (VolunteerPolicy),
 * whereas creating requires that no application exists yet.
 */
class UpdateVolunteerRequest extends StoreVolunteerRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('volunteer')) ?? false;
    }
}
