<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RekapController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('accessRecap', IncidentReport::class);

        $schools = School::orderBy('name')->get();

        $query = IncidentReport::with(['school', 'user'])->latest('incident_date');

        if ($schoolId = $request->input('school_id')) {
            $query->where('school_id', $schoolId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('incident_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('incident_date', '<=', $dateTo);
        }

        $allFiltered  = $query->get();
        $totalLaporan = $allFiltered->count();
        $dalamProses  = $allFiltered->whereIn('status', ['draft', 'reviewed'])->count();
        $selesai      = $allFiltered->whereIn('status', ['escalated', 'resolved'])->count();

        $reports = IncidentReport::with(['school', 'user'])
            ->latest('incident_date')
            ->when($request->input('school_id'), fn ($q, $v) => $q->where('school_id', $v))
            ->when($request->input('date_from'), fn ($q, $v) => $q->whereDate('incident_date', '>=', $v))
            ->when($request->input('date_to'), fn ($q, $v) => $q->whereDate('incident_date', '<=', $v))
            ->paginate(10)
            ->withQueryString();

        return view('rekap.index', compact(
            'schools',
            'reports',
            'totalLaporan',
            'dalamProses',
            'selesai'
        ));
    }
}
