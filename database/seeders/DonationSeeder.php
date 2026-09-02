<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Services\DonationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DonationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->admins()->first();
        $members = User::query()->members()->get();
        $campaigns = Campaign::query()->published()->get();

        if ($members->isEmpty() || $campaigns->isEmpty()) {
            return;
        }

        $methods = array_keys(config('site.donation_methods'));
        $sequence = 1;

        foreach ($campaigns as $campaign) {
            // Older campaigns carry more history than ones opened last week.
            $ageInDays = max(1, (int) $campaign->start_date->diffInDays(now()));
            $count = min(28, max(4, (int) round($ageInDays / 7) + 3));

            for ($i = 0; $i < $count; $i++) {
                $member = $members->random();
                $donatedOn = fake()->dateTimeBetween(
                    $campaign->start_date->toDateString(),
                    'now'
                );

                // Roughly 72% approved / 18% pending / 10% rejected.
                $roll = fake()->numberBetween(1, 100);
                $status = match (true) {
                    $roll <= 72 => Donation::STATUS_APPROVED,
                    $roll <= 90 => Donation::STATUS_PENDING,
                    default => Donation::STATUS_REJECTED,
                };

                $anonymous = fake()->boolean(15);

                $donation = new Donation([
                    'reference' => 'KT-'.now()->format('Y').'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
                    'user_id' => $member->id,
                    'campaign_id' => $campaign->id,
                    'donor_name' => $member->name,
                    'donor_email' => $member->email,
                    'donor_phone' => $member->phone,
                    'amount' => $this->amount(),
                    'method' => fake()->randomElement($methods),
                    'transaction_reference' => Str::upper(Str::random(10)),
                    'is_anonymous' => $anonymous,
                    'message' => fake()->boolean(35) ? $this->message() : null,
                    'donated_on' => $donatedOn,
                ]);

                $donation->status = $status;
                $donation->counted_in_campaign = false;

                if ($status !== Donation::STATUS_PENDING) {
                    $donation->reviewed_by = $admin?->id;
                    $donation->reviewed_at = $donatedOn;
                }

                if ($status === Donation::STATUS_REJECTED) {
                    $donation->admin_note = 'Transaction reference could not be matched against the bank statement.';
                }

                $donation->save();
                $sequence++;
            }
        }

        // Rebuild every campaign total from the approved records, using exactly
        // the same code path the admin panel uses.
        app(DonationService::class)->recalculateCampaignTotals();
    }

    /** Donation sizes that look like real giving: many small, a few large. */
    protected function amount(): int
    {
        $roll = fake()->numberBetween(1, 100);

        return match (true) {
            $roll <= 45 => fake()->randomElement([500, 700, 1000, 1200, 1500]),
            $roll <= 78 => fake()->randomElement([2000, 2500, 3000, 5000]),
            $roll <= 94 => fake()->randomElement([7500, 10000, 12000, 15000]),
            default => fake()->randomElement([25000, 30000, 50000]),
        };
    }

    protected function message(): string
    {
        return fake()->randomElement([
            'Please use it where it is needed most.',
            'From my family, with prayers for those affected.',
            'A small amount, sorry I cannot do more this month.',
            'Keep publishing the reports — that is why I give.',
            'For the children\'s school supplies specifically if possible.',
            'On behalf of my late father.',
            'Thank you for going out at night in this cold.',
        ]);
    }
}
