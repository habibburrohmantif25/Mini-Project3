<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'total_reports' => IncidentReport::count(),
            'my_reports' => $user->incidentReports()->count(),
            'draft_reports' => IncidentReport::where('status', 'draft')->count(),
            'reviewed_reports' => IncidentReport::where('status', 'reviewed')->count(),
            'escalated_reports' => IncidentReport::where('status', 'escalated')->count(),
            'resolved_reports' => IncidentReport::where('status', 'resolved')->count(),
            'total_schools' => School::count(),
        ];

        $recentReports = $user->isPelapor()
            ? $user->incidentReports()->with(['school', 'mealDistribution'])->latest()->take(5)->get()
            : IncidentReport::with(['user', 'school', 'mealDistribution'])->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentReports'));
    }
}
