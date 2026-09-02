<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(protected ActivityLogger $activity) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', Rule::in(['read', 'unread'])],
        ]);

        return view('admin.messages.index', [
            'messages' => ContactMessage::query()
                ->search($filters['q'] ?? null)
                ->when(($filters['state'] ?? null) === 'unread', fn ($q) => $q->where('is_read', false))
                ->when(($filters['state'] ?? null) === 'read', fn ($q) => $q->where('is_read', true))
                ->latest()
                ->paginate(config('site.pagination.admin'))
                ->withQueryString(),
            'filters' => $filters,
            'counts' => [
                'total' => ContactMessage::query()->count(),
                'unread' => ContactMessage::query()->unread()->count(),
            ],
        ]);
    }

    public function show(ContactMessage $message): View
    {
        // Opening a message marks it read.
        $message->markAsRead();

        return view('admin.messages.show', compact('message'));
    }

    public function toggleRead(ContactMessage $message): RedirectResponse
    {
        $message->is_read ? $message->markAsUnread() : $message->markAsRead();

        return back()->with('success', $message->is_read ? 'Marked as read.' : 'Marked as unread.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $subject = $message->subject;
        $message->delete();

        $this->activity->log('contact_message.deleted', "Deleted contact message \"{$subject}\"");

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
