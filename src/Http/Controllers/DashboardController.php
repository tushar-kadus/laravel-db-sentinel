<?php

namespace Atmos\DbSentinel\Http\Controllers;

use Atmos\DbSentinel\Models\SentinelLog;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the query log dashboard.
     */
    public function index(Request $request): View
    {
        // Get counts for the UI metrics
        $counts = [
            'all' => SentinelLog::count(),
            'pending' => SentinelLog::where('status', 'pending')->count(),
            'analyzed' => SentinelLog::where('status', 'analyzed')->count(),
            'failed' => SentinelLog::where('status', 'failed')->count(),
        ];

        $query = SentinelLog::query();

        // Apply Filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('db-sentinel::dashboard.index', compact('logs', 'counts'));
    }

    public function show($id): View
    {
        $log = SentinelLog::findOrFail($id);

        return view('db-sentinel::dashboard.show', compact('log'));
    }
}
