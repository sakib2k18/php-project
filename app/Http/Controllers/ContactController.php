<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact');
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create(
            $request->safe()->only(['name', 'email', 'phone', 'subject', 'message'])
                + ['ip_address' => $request->ip()]
        );

        // Flash the reference into the session so the thank-you panel can show it.
        return redirect()
            ->route('contact.create')
            ->with('success', 'Thank you for reaching out. We usually reply within two working days.')
            ->with('contact_reference', 'MSG-'.str_pad((string) $message->id, 5, '0', STR_PAD_LEFT));
    }
}
