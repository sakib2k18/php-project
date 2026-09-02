<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $upcoming = Event::query()
            ->published()
            ->upcoming()
            ->search($filters['q'] ?? null)
            ->orderBy('event_date')
            ->get();

        $past = Event::query()
            ->published()
            ->past()
            ->search($filters['q'] ?? null)
            ->orderByDesc('event_date')
            ->paginate(6, ['*'], 'past')
            ->withQueryString();

        return view('events.index', [
            'upcoming' => $upcoming,
            'past' => $past,
            'filters' => $filters,
        ]);
    }

    public function show(Event $event, Request $request): View
    {
        abort_if($event->status !== Event::STATUS_PUBLISHED && ! $request->user()?->isAdmin(), 404);

        return view('events.show', [
            'event' => $event,
            'related' => Event::query()
                ->published()
                ->where('id', '!=', $event->id)
                ->upcoming()
                ->orderBy('event_date')
                ->limit(3)
                ->get(),
        ]);
    }
}
