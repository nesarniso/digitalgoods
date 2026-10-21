<?php

namespace App\Http\Controllers;

use App\Domain\Notifications\Models\NotificationCampaign;
use App\Domain\Notifications\Models\NotificationDelivery;
use App\Domain\Notifications\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminNotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index()
    {
        return view('admin.notifications.index', [
            'campaigns' => NotificationCampaign::withCount(['deliveries as clicks' => fn ($q) => $q->whereNotNull('clicked_at')])->latest()->paginate(15),
            'deliveryStats' => [
                'sent' => NotificationDelivery::where('status', 'sent')->count(),
                'failed' => NotificationDelivery::where('status', 'failed')->count(),
                'clicked' => NotificationDelivery::whereNotNull('clicked_at')->count(),
            ],
            'scheduledCampaigns' => NotificationCampaign::where('status', 'scheduled')->orderBy('scheduled_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'body' => ['required', 'string', 'max:5000'], 'url' => ['nullable', 'url', 'max:500'], 'audience' => ['required', 'in:all,customers'], 'scheduled_at' => ['nullable', 'date', 'after:now']]);
        $scheduled = ! empty($data['scheduled_at']);
        $campaign = NotificationCampaign::create([...$data, 'created_by' => $request->user()->id, 'status' => $scheduled ? 'scheduled' : 'queued', 'queued_at' => now()]);
        if (! $scheduled) $this->notifications->broadcast($campaign);
        return back()->with('success', $scheduled ? 'Notification scheduled.' : 'Notification queued for delivery.');
    }

    public function preview(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'body' => ['required', 'string', 'max:5000'], 'url' => ['nullable', 'url', 'max:500']]);
        return response()->json(['title' => $data['title'], 'body' => $data['body'], 'url' => $data['url'] ?? url('/')]);
    }

    public function testSend(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'body' => ['required', 'string', 'max:5000'], 'url' => ['nullable', 'url', 'max:500']]);
        $count = $this->notifications->queueForUser($request->user(), $data['title'], $data['body'], $data['url'] ?? url('/'));
        return back()->with($count ? 'success' : 'error', $count ? "Test notification queued to {$count} device(s)." : 'No active subscription found for your account.');
    }

    public function export(Request $request): StreamedResponse
    {
        $from = Carbon::parse($request->input('from', now()->subDays(30)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        $deliveries = NotificationDelivery::whereBetween('created_at', [$from, $to])->latest()->cursor();
        return response()->streamDownload(function () use ($deliveries): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['date', 'title', 'status', 'sent_at', 'clicked_at', 'error']);
            foreach ($deliveries as $delivery) fputcsv($out, [$delivery->created_at, $delivery->title, $delivery->status, $delivery->sent_at, $delivery->clicked_at, $delivery->error]);
            fclose($out);
        }, 'push-notifications.csv', ['Content-Type' => 'text/csv']);
    }
}
