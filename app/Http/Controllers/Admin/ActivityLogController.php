<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:60'],
        ]);

        return view('admin.activity.index', [
            'logs' => ActivityLog::query()
                ->with('user:id,name')
                ->search($filters['q'] ?? null)
                ->action($filters['action'] ?? null)
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
            'actions' => ActivityLog::query()
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
        ]);
    }
}
