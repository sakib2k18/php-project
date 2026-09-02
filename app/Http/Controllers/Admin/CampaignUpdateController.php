<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampaignUpdateRequest;
use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;

class CampaignUpdateController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function store(CampaignUpdateRequest $request, Campaign $campaign): RedirectResponse
    {
        $update = new CampaignUpdate($request->safe()->except('image'));
        $update->campaign_id = $campaign->id;
        $update->created_by = $request->user()->id;
        $update->image = $this->images->store($request->file('image'), 'campaign-updates');
        $update->save();

        $this->activity->log(
            'campaign_update.created',
            "Posted an update on \"{$campaign->title}\"",
            $campaign
        );

        return back()->with('success', 'Campaign update published.');
    }

    public function destroy(Campaign $campaign, CampaignUpdate $update): RedirectResponse
    {
        $this->authorize('update', $campaign);

        abort_if($update->campaign_id !== $campaign->id, 404);

        $this->images->delete($update->image);
        $update->delete();

        $this->activity->log('campaign_update.deleted', "Removed an update from \"{$campaign->title}\"", $campaign);

        return back()->with('success', 'Campaign update removed.');
    }
}
