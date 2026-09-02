<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DonationReportController extends Controller
{
    public function __construct(protected StatisticsService $statistics) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Donation::class);

        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'months' => ['nullable', 'integer', 'min:3', 'max:24'],
        ]);

        $from = filled($filters['from'] ?? null) ? Carbon::parse($filters['from']) : null;
        $to = filled($filters['to'] ?? null) ? Carbon::parse($filters['to']) : null;
        $months = (int) ($filters['months'] ?? 12);

        return view('admin.donations.reports', [
            'filters' => ['from' => $from?->toDateString(), 'to' => $to?->toDateString(), 'months' => $months],
            'summary' => $this->statistics->rangeSummary($from, $to),
            'trend' => $this->statistics->donationsByMonth($months),
            'categories' => $this->statistics->donationsByCategory(),
            'statusBreakdown' => $this->statistics->donationStatusBreakdown(),
            'perCampaign' => $this->statistics->donationsPerCampaign(),
            'methods' => Donation::query()
                ->approved()
                ->when($from, fn ($q) => $q->where('donated_on', '>=', $from->toDateString()))
                ->when($to, fn ($q) => $q->where('donated_on', '<=', $to->toDateString()))
                ->selectRaw('method, COUNT(*) as c, COALESCE(SUM(amount),0) as total')
                ->groupBy('method')
                ->orderByDesc('total')
                ->get(),
            'recent' => Donation::query()
                ->with(['campaign:id,title,slug', 'user:id,name'])
                ->when($from, fn ($q) => $q->where('donated_on', '>=', $from->toDateString()))
                ->when($to, fn ($q) => $q->where('donated_on', '<=', $to->toDateString()))
                ->latest('donated_on')
                ->limit(12)
                ->get(),
        ]);
    }
}
