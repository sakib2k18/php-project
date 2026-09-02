<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\Event;
use App\Models\Volunteer;
use App\Services\StatisticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected StatisticsService $statistics) {}

    public function __invoke(): View
    {
        return view('admin.dashboard', [
            // Every figure below is a live MySQL aggregate — nothing hardcoded.
            'stats' => $this->statistics->adminOverview(),
            'donationTrend' => $this->statistics->donationsByMonth(6),
            'statusBreakdown' => $this->statistics->donationStatusBreakdown(),
            'categoryBreakdown' => $this->statistics->donationsByCategory(),
            'userGrowth' => $this->statistics->userGrowth(6),
            'topCampaigns' => $this->statistics->topCampaigns(5),

            'pendingDonations' => Donation::query()
                ->pending()
                ->with(['campaign:id,title,slug', 'user:id,name'])
                ->latest()
                ->limit(5)
                ->get(),

            'pendingVolunteers' => Volunteer::query()
                ->pending()
                ->latest()
                ->limit(5)
                ->get(),

            'unreadMessages' => ContactMessage::query()
                ->unread()
                ->latest()
                ->limit(5)
                ->get(),

            'upcomingEvents' => Event::query()
                ->published()
                ->upcoming()
                ->orderBy('event_date')
                ->limit(4)
                ->get(),

            'activities' => ActivityLog::query()
                ->with('user:id,name')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
