<?php

namespace App\Http\Controllers;

use App\Domain\Support\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminSupportTicketController extends Controller
{
    public function index()
    {
        return view('admin.support-tickets.index', [
            'tickets' => SupportTicket::query()->with('user')->latest()->paginate(20),
        ]);
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);
        $supportTicket->update($data);

        return back()->with('success', 'Support ticket status updated.');
    }
}
