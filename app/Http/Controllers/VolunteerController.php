<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVolunteerRequest;
use App\Http\Requests\UpdateVolunteerRequest;
use App\Models\Volunteer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerController extends Controller
{
    /** Shows either the application form or the status of an existing one. */
    public function index(Request $request): View
    {
        $volunteer = $request->user()->volunteer;

        return view('dashboard.volunteer', [
            'volunteer' => $volunteer,
            'availability' => config('site.volunteer_availability'),
            'activities' => $this->activities(),
            'approvedCount' => Volunteer::query()->approved()->count(),
        ]);
    }

    public function store(StoreVolunteerRequest $request): RedirectResponse
    {
        $volunteer = new Volunteer($request->validated());
        $volunteer->user_id = $request->user()->id;
        $volunteer->status = Volunteer::STATUS_PENDING; // never from input
        $volunteer->save();

        return redirect()
            ->route('volunteer.index')
            ->with('success', 'Thank you for stepping forward. Your volunteer application is under review.');
    }

    public function update(UpdateVolunteerRequest $request, Volunteer $volunteer): RedirectResponse
    {
        // Authorisation is enforced by UpdateVolunteerRequest::authorize(),
        // which delegates to VolunteerPolicy::update (owner + still pending).
        $volunteer->update($request->validated());

        return redirect()->route('volunteer.index')->with('success', 'Your volunteer application has been updated.');
    }

    /**
     * @return array<int, string>
     */
    protected function activities(): array
    {
        return [
            'Relief distribution',
            'Fundraising',
            'Teaching & tutoring',
            'Medical camp support',
            'Event management',
            'Photography & media',
            'Logistics & transport',
            'Administration',
        ];
    }
}
