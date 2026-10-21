<?php

namespace App\Http\Controllers;

use App\Domain\Support\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerSupportController extends Controller
{
    public function index(Request $request)
    {
        return view('storefront.account.support', [
            'tickets' => $request->user()->supportTickets()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:Order support,Payment,Returns and refunds,Product question,General inquiry'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        $request->user()->supportTickets()->create($data);

        return back()->with('success', 'Your support ticket has been submitted.');
    }
}
