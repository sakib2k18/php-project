<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamMember;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeamMember::class);

        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120']]);

        return view('admin.team.index', [
            'members' => TeamMember::query()
                ->when($filters['q'] ?? null, fn ($q, $t) => $q->where('name', 'like', "%{$t}%")->orWhere('position', 'like', "%{$t}%"))
                ->ordered()
                ->paginate(config('site.pagination.admin'))
                ->withQueryString(),
            'filters' => $filters,
            'activeCount' => TeamMember::query()->active()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TeamMember::class);

        return view('admin.team.create', [
            'member' => new TeamMember([
                'is_active' => true,
                'sort_order' => (int) TeamMember::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        $member = new TeamMember($request->safe()->except('photo'));
        $member->photo = $this->images->store($request->file('photo'), 'team');
        $member->save();

        $this->activity->created($member, "team member \"{$member->name}\"");

        return redirect()->route('admin.team.index')->with('success', 'Team member added.');
    }

    public function edit(TeamMember $teamMember): View
    {
        $this->authorize('update', $teamMember);

        return view('admin.team.edit', ['member' => $teamMember]);
    }

    public function update(TeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $teamMember->fill($request->safe()->except('photo'));

        if ($request->hasFile('photo')) {
            $teamMember->photo = $this->images->store($request->file('photo'), 'team', $teamMember->photo);
        }

        $teamMember->save();

        $this->activity->updated($teamMember, "team member \"{$teamMember->name}\"");

        return redirect()->route('admin.team.index')->with('success', 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $this->authorize('delete', $teamMember);

        $name = $teamMember->name;
        $this->images->delete($teamMember->photo);
        $teamMember->delete();

        $this->activity->log('team_member.deleted', "Removed team member \"{$name}\"");

        return redirect()->route('admin.team.index')->with('success', 'Team member removed.');
    }
}
