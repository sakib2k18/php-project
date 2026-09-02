<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('donation')) ?? false;
    }

    /**
     * Only a decision and an optional note are accepted here. The amount, the
     * campaign and the donor can never be changed through the review endpoint.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject', 'pending'])],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
