<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(protected ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        $filters = $request->validate([
            'priority' => ['nullable', Rule::in(array_keys(config('site.announcement_priorities')))],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
        ]);

        return view('admin.announcements.index', [
            'announcements' => Announcement::query()
                ->priority($filters['priority'] ?? null)
                ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
                ->latest()
                ->paginate(config('site.pagination.admin'))
                ->withQueryString(),
            'filters' => $filters,
            'priorities' => config('site.announcement_priorities'),
            'activeCount' => Announcement::query()->active()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('admin.announcements.create', [
            'announcement' => new Announcement([
                'priority' => 'normal',
                'status' => 'published',
                'published_at' => now()->format('Y-m-d\TH:i'),
            ]),
            'priorities' => config('site.announcement_priorities'),
        ]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $announcement = Announcement::create($request->validated());

        $this->activity->created($announcement, "announcement \"{$announcement->title}\"");

        if ($request->boolean('notify_users') && $announcement->status === Announcement::STATUS_PUBLISHED) {
            $this->notifyMembers($announcement);
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'priorities' => config('site.announcement_priorities'),
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($request->validated());

        $this->activity->updated($announcement, "announcement \"{$announcement->title}\"");

        if ($request->boolean('notify_users') && $announcement->status === Announcement::STATUS_PUBLISHED) {
            $this->notifyMembers($announcement);
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $title = $announcement->title;
        $announcement->delete();

        $this->activity->log('announcement.deleted', "Deleted announcement \"{$title}\"");

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }

    /** Push the announcement into every supporter's notification list. */
    protected function notifyMembers(Announcement $announcement): void
    {
        User::query()->members()->where('is_active', true)->chunkById(200, function ($users) use ($announcement) {
            Notification::send($users, new AnnouncementPublished($announcement));
        });
    }
}
