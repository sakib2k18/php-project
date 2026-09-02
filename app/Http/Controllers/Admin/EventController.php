<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Services\ActivityLogger;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected ImageUploadService $images,
        protected ActivityLogger $activity,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(config('site.event_statuses')))],
            'when' => ['nullable', Rule::in(['upcoming', 'past'])],
            'trashed' => ['nullable', 'boolean'],
        ]);

        $events = Event::query()
            ->when($request->boolean('trashed'), fn ($q) => $q->onlyTrashed())
            ->search($filters['q'] ?? null)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when(($filters['when'] ?? null) === 'upcoming', fn ($q) => $q->upcoming())
            ->when(($filters['when'] ?? null) === 'past', fn ($q) => $q->past())
            ->orderByDesc('event_date')
            ->paginate(config('site.pagination.admin'))
            ->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'filters' => $filters,
            'statuses' => config('site.event_statuses'),
            'trashedCount' => Event::onlyTrashed()->count(),
            'upcomingCount' => Event::query()->published()->upcoming()->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('admin.events.create', [
            'event' => new Event([
                'status' => Event::STATUS_PUBLISHED,
                'event_date' => now()->addWeek()->toDateString(),
            ]),
            'statuses' => config('site.event_statuses'),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = new Event($request->safe()->except('image'));
        $event->image = $this->images->store($request->file('image'), 'events');
        $event->save();

        $this->activity->created($event, "event \"{$event->title}\"");

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('admin.events.edit', [
            'event' => $event,
            'statuses' => config('site.event_statuses'),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->fill($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $event->image = $this->images->store($request->file('image'), 'events', $event->image);
        }

        $event->save();

        $this->activity->updated($event, "event \"{$event->title}\"");

        return redirect()->route('admin.events.edit', $event)->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $title = $event->title;
        $event->delete();

        $this->activity->log('event.deleted', "Deleted event \"{$title}\"");

        return redirect()->route('admin.events.index')->with('success', "Event \"{$title}\" moved to the archive.");
    }

    public function restore(int $id): RedirectResponse
    {
        $event = Event::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $event);

        $event->restore();
        $this->activity->log('event.restored', "Restored event \"{$event->title}\"", $event);

        return redirect()->route('admin.events.index')->with('success', 'Event restored.');
    }
}
