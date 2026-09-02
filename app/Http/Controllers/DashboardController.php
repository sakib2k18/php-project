<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // One grouped query instead of three counts.
        $summary = Donation::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount),0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('dashboard.index', [
            'stats' => [
                'total' => (int) $summary->sum('c'),
                'approved_count' => (int) ($summary[Donation::STATUS_APPROVED]->c ?? 0),
                'pending_count' => (int) ($summary[Donation::STATUS_PENDING]->c ?? 0),
                'rejected_count' => (int) ($summary[Donation::STATUS_REJECTED]->c ?? 0),
                'approved_amount' => (float) ($summary[Donation::STATUS_APPROVED]->total ?? 0),
                'pending_amount' => (float) ($summary[Donation::STATUS_PENDING]->total ?? 0),
            ],
            'recentDonations' => Donation::query()
                ->where('user_id', $user->id)
                ->with('campaign:id,title,slug')
                ->latest('donated_on')
                ->limit(5)
                ->get(),
            'campaigns' => Campaign::query()
                ->active()
                ->orderByDesc('is_emergency')
                ->orderByDesc('featured')
                ->limit(3)
                ->get(),
            'events' => Event::query()
                ->published()
                ->upcoming()
                ->orderBy('event_date')
                ->limit(3)
                ->get(),
            'announcements' => Announcement::query()
                ->active()
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                ->limit(2)
                ->get(),
            'volunteer' => $user->volunteer,
            'unreadNotifications' => $user->unreadNotifications()->take(4)->get(),
        ]);
    }
}
