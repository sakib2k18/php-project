<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Notifications\DonationReviewed;
use Illuminate\Support\Facades\DB;

/**
 * The only place in the application that is allowed to change a donation's
 * status or a campaign's raised_amount.
 *
 * Every state change happens inside a database transaction that also writes the
 * activity log, and `counted_in_campaign` guarantees an amount can never be
 * added to a campaign total twice.
 */
class DonationService
{
    public function __construct(
        protected ActivityLogger $activity,
    ) {}

    /**
     * Approve a pending/rejected donation and roll its amount into the campaign.
     */
    public function approve(Donation $donation, User $admin, ?string $note = null): Donation
    {
        return DB::transaction(function () use ($donation, $admin, $note) {
            // Lock the row so two concurrent approvals cannot both credit the campaign.
            $donation = Donation::query()->lockForUpdate()->findOrFail($donation->getKey());

            if ($donation->status !== Donation::STATUS_APPROVED) {
                $donation->forceFill([
                    'status' => Donation::STATUS_APPROVED,
                    'admin_note' => $note,
                    'reviewed_by' => $admin->getKey(),
                    'reviewed_at' => now(),
                ])->save();
            }

            $this->creditCampaign($donation);

            $this->activity->log(
                'donation.approved',
                "Approved donation {$donation->reference} of ".money($donation->amount),
                $donation
            );

            $this->notifyDonor($donation);

            return $donation->refresh();
        });
    }

    /**
     * Reject a donation, reversing the campaign credit when it had been approved.
     */
    public function reject(Donation $donation, User $admin, ?string $note = null): Donation
    {
        return DB::transaction(function () use ($donation, $admin, $note) {
            $donation = Donation::query()->lockForUpdate()->findOrFail($donation->getKey());

            $this->debitCampaign($donation);

            $donation->forceFill([
                'status' => Donation::STATUS_REJECTED,
                'admin_note' => $note,
                'reviewed_by' => $admin->getKey(),
                'reviewed_at' => now(),
            ])->save();

            $this->activity->log(
                'donation.rejected',
                "Rejected donation {$donation->reference}",
                $donation
            );

            $this->notifyDonor($donation);

            return $donation->refresh();
        });
    }

    /**
     * Send an approved/rejected donation back to the pending queue.
     */
    public function markPending(Donation $donation, User $admin): Donation
    {
        return DB::transaction(function () use ($donation, $admin) {
            $donation = Donation::query()->lockForUpdate()->findOrFail($donation->getKey());

            $this->debitCampaign($donation);

            $donation->forceFill([
                'status' => Donation::STATUS_PENDING,
                'reviewed_by' => $admin->getKey(),
                'reviewed_at' => now(),
            ])->save();

            $this->activity->log(
                'donation.reopened',
                "Moved donation {$donation->reference} back to pending",
                $donation
            );

            return $donation->refresh();
        });
    }

    /**
     * Delete a donation, first reversing any campaign credit it holds.
     */
    public function delete(Donation $donation): void
    {
        DB::transaction(function () use ($donation) {
            $this->debitCampaign($donation);

            $reference = $donation->reference;
            $donation->delete();

            $this->activity->log('donation.deleted', "Deleted donation {$reference}");
        });
    }

    /**
     * Recompute every campaign total from the approved donations on record.
     * Useful after a bulk import or if data was edited outside the app.
     */
    public function recalculateCampaignTotals(): int
    {
        return DB::transaction(function () {
            $totals = Donation::query()
                ->approved()
                ->whereNotNull('campaign_id')
                ->selectRaw('campaign_id, SUM(amount) as total')
                ->groupBy('campaign_id')
                ->pluck('total', 'campaign_id');

            $updated = 0;

            foreach (Campaign::query()->withTrashed()->cursor() as $campaign) {
                $campaign->forceFill([
                    'raised_amount' => (float) ($totals[$campaign->id] ?? 0),
                ])->save();
                $updated++;
            }

            Donation::query()->approved()->whereNotNull('campaign_id')
                ->update(['counted_in_campaign' => true]);
            Donation::query()->where('status', '!=', Donation::STATUS_APPROVED)
                ->update(['counted_in_campaign' => false]);

            return $updated;
        });
    }

    /**
     * Add the donation amount to its campaign exactly once.
     */
    protected function creditCampaign(Donation $donation): void
    {
        if ($donation->counted_in_campaign || ! $donation->campaign_id) {
            return;
        }

        Campaign::query()->whereKey($donation->campaign_id)->increment('raised_amount', (float) $donation->amount);

        $donation->forceFill(['counted_in_campaign' => true])->save();

        $this->autoCompleteCampaign($donation->campaign_id);
    }

    /**
     * Remove a previously credited amount from its campaign exactly once.
     */
    protected function debitCampaign(Donation $donation): void
    {
        if (! $donation->counted_in_campaign || ! $donation->campaign_id) {
            return;
        }

        $campaign = Campaign::query()->lockForUpdate()->find($donation->campaign_id);

        if ($campaign) {
            // max(0, …) keeps the total sane even if data drifted.
            $campaign->forceFill([
                'raised_amount' => max(0, (float) $campaign->raised_amount - (float) $donation->amount),
            ])->save();
        }

        $donation->forceFill(['counted_in_campaign' => false])->save();
    }

    /**
     * A fully funded active campaign is flipped to "completed" automatically.
     */
    protected function autoCompleteCampaign(int $campaignId): void
    {
        $campaign = Campaign::query()->find($campaignId);

        if ($campaign
            && $campaign->status === Campaign::STATUS_ACTIVE
            && (float) $campaign->target_amount > 0
            && (float) $campaign->raised_amount >= (float) $campaign->target_amount
        ) {
            $campaign->forceFill(['status' => Campaign::STATUS_COMPLETED])->save();

            $this->activity->log(
                'campaign.completed',
                "Campaign \"{$campaign->title}\" reached its target and was marked completed",
                $campaign
            );
        }
    }

    protected function notifyDonor(Donation $donation): void
    {
        $donation->loadMissing('user');

        $donation->user?->notify(new DonationReviewed($donation));
    }
}
