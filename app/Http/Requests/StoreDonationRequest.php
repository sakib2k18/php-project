<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Donation::class) ?? false;
    }

    /**
     * Note what is *absent*: `status`, `reviewed_by` and `counted_in_campaign`
     * are never accepted from the request. A supporter can only ever create a
     * pending record; the administrator decides the rest.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'campaign_id' => [
                'required', 'integer',
                // Only campaigns that are open for donations may be selected.
                Rule::exists('campaigns', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', Campaign::STATUS_ACTIVE),
            ],
            'donor_name' => ['required', 'string', 'min:3', 'max:120'],
            'donor_email' => ['required', 'email:rfc', 'max:150'],
            'donor_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'amount' => ['required', 'numeric', 'min:50', 'max:10000000'],
            'method' => ['required', Rule::in(array_keys(config('site.donation_methods')))],
            'transaction_reference' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-\/ ]+$/'],
            'is_anonymous' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
            'donated_on' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subYear()->toDateString()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'campaign_id.exists' => 'Please choose a campaign that is currently accepting donations.',
            'amount.min' => 'The minimum donation record is '.money(50).'.',
            'donated_on.before_or_equal' => 'The donation date cannot be in the future.',
            'donated_on.after_or_equal' => 'Please record donations made within the last year.',
            'transaction_reference.regex' => 'The transaction reference may only contain letters, numbers, spaces, - and /.',
            'donor_phone.regex' => 'Enter a valid phone number (digits, spaces, + and - only).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'campaign_id' => 'campaign',
            'donor_name' => 'full name',
            'donor_email' => 'email address',
            'donor_phone' => 'phone number',
            'donated_on' => 'donation date',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => $this->boolean('is_anonymous'),
        ]);
    }
}
