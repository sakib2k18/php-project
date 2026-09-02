<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Gives the dashboard's audit trail some history. In normal operation these
 * rows are written by App\Services\ActivityLogger as the admin works.
 */
class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->admins()->first();

        if (! $admin) {
            return;
        }

        ActivityLog::query()->delete();

        $entries = [];

        foreach (Campaign::query()->latest()->take(4)->get() as $index => $campaign) {
            $entries[] = [
                'action' => 'campaign.created',
                'description' => "Created campaign \"{$campaign->title}\"",
                'subject_type' => Campaign::class,
                'subject_id' => $campaign->id,
                'created_at' => now()->subDays(20 - $index * 3),
            ];
        }

        foreach (Donation::query()->approved()->latest()->take(5)->get() as $index => $donation) {
            $entries[] = [
                'action' => 'donation.approved',
                'description' => "Approved donation {$donation->reference} of ".money($donation->amount),
                'subject_type' => Donation::class,
                'subject_id' => $donation->id,
                'created_at' => now()->subDays(9 - $index),
            ];
        }

        foreach (Donation::query()->rejected()->latest()->take(2)->get() as $index => $donation) {
            $entries[] = [
                'action' => 'donation.rejected',
                'description' => "Rejected donation {$donation->reference}",
                'subject_type' => Donation::class,
                'subject_id' => $donation->id,
                'created_at' => now()->subDays(6 - $index),
            ];
        }

        $entries[] = [
            'action' => 'settings.updated',
            'description' => 'Updated the organisation settings',
            'subject_type' => null,
            'subject_id' => null,
            'created_at' => now()->subDays(2),
        ];

        $entries[] = [
            'action' => 'volunteer.approved',
            'description' => 'Marked a volunteer application as approved',
            'subject_type' => null,
            'subject_id' => null,
            'created_at' => now()->subDay(),
        ];

        foreach ($entries as $entry) {
            ActivityLog::create($entry + [
                'user_id' => $admin->id,
                'ip_address' => '127.0.0.1',
            ]);
        }
    }
}
