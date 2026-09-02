<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'reference' => 'KT-'.now()->format('Y').'-'.Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'campaign_id' => Campaign::factory(),
            'donor_name' => $name,
            'donor_email' => fake()->safeEmail(),
            'donor_phone' => '+8801'.fake()->numerify('#########'),
            'amount' => fake()->numberBetween(5, 400) * 100,
            'method' => fake()->randomElement(array_keys(config('site.donation_methods'))),
            'transaction_reference' => Str::upper(Str::random(10)),
            'is_anonymous' => fake()->boolean(15),
            'message' => fake()->boolean(40) ? fake()->sentence(12) : null,
            'donated_on' => fake()->dateTimeBetween('-6 months', 'now'),
            'status' => Donation::STATUS_PENDING,
            'counted_in_campaign' => false,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => Donation::STATUS_PENDING,
            'counted_in_campaign' => false,
        ]);
    }

    /**
     * Approved donations are marked as counted; the seeder recalculates the
     * campaign totals afterwards so the two always agree.
     */
    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => Donation::STATUS_APPROVED,
            'counted_in_campaign' => true,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => Donation::STATUS_REJECTED,
            'counted_in_campaign' => false,
            'reviewed_at' => now(),
            'admin_note' => 'Transaction reference could not be verified.',
        ]);
    }
}
