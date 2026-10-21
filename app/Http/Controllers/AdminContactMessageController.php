<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class AdminContactMessageController extends Controller
{
    public function index()
    {
        return view('admin.contact-messages.index', [
            'messages' => ContactMessage::query()->latest()->paginate(20),
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        $contactMessage->update(['read_at' => now()]);

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return back()->with('success', 'Contact message deleted.');
    }
}
