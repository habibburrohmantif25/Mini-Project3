<?php

namespace App\Http\Controllers;

use App\Models\EvidenceFile;
use App\Models\IncidentReport;
use App\Models\MealDistribution;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IncidentReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = $user->isPelapor()
            ? $user->incidentReports()
            : IncidentReport::query();

        $query->with(['school', 'mealDistribution', 'evidenceFile', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('report_code', 'like', "%{$search}%")
                    ->orWhere('symptom_description', 'like', "%{$search}%");
            });
        }

        if ($schoolId = $request->input('school_id')) {
            $query->where('school_id', $schoolId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reports = $query->latest('incident_date')->paginate(10)->withQueryString();
        $schools = School::orderBy('name')->get();

        return view('reports.index', compact('reports', 'schools'));
    }

    public function create(): View
    {
        Gate::authorize('create', IncidentReport::class);

        $schools = School::with('mealDistributions')->orderBy('name')->get();

        return view('reports.create', compact('schools'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', IncidentReport::class);

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'meal_distribution_id' => ['required', 'exists:meal_distributions,id'],
            'incident_date' => ['required', 'date'],
            'symptom_description' => ['required', 'string', 'min:10'],
            'affected_count' => ['required', 'integer', 'min:1'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $isValidDistribution = MealDistribution::where('id', $validated['meal_distribution_id'])
            ->where('school_id', $validated['school_id'])
            ->exists();

        if (! $isValidDistribution) {
            return back()->withErrors([
                'meal_distribution_id' => 'Distribusi makanan yang dipilih tidak terdaftar untuk sekolah ini.',
            ])->withInput();
        }

        $report = IncidentReport::create([
            'report_code' => IncidentReport::generateReportCode(),
            'user_id' => $request->user()->id,
            'school_id' => $validated['school_id'],
            'meal_distribution_id' => $validated['meal_distribution_id'],
            'incident_date' => $validated['incident_date'],
            'symptom_description' => $validated['symptom_description'],
            'affected_count' => $validated['affected_count'],
            'status' => 'draft',
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            $filePath = $file->store('evidences', 'public');

            EvidenceFile::create([
                'incident_report_id' => $report->id,
                'file_path' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('reports.show', $report)->with(
            'success',
            'Laporan kejadian berhasil dibuat dengan kode: ' . $report->report_code
        );
    }

    public function show(IncidentReport $report): View
    {
        Gate::authorize('view', $report);

        $report->load(['user', 'school', 'mealDistribution', 'evidenceFile', 'observerLogs.changedBy']);

        return view('reports.show', compact('report'));
    }

    public function edit(IncidentReport $report): View
    {
        Gate::authorize('update', $report);

        $schools = School::with('mealDistributions')->orderBy('name')->get();

        return view('reports.edit', compact('report', 'schools'));
    }

    public function update(Request $request, IncidentReport $report): RedirectResponse
    {
        Gate::authorize('update', $report);

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'meal_distribution_id' => ['required', 'exists:meal_distributions,id'],
            'incident_date' => ['required', 'date'],
            'symptom_description' => ['required', 'string', 'min:10'],
            'affected_count' => ['required', 'integer', 'min:1'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $isValidDistribution = MealDistribution::where('id', $validated['meal_distribution_id'])
            ->where('school_id', $validated['school_id'])
            ->exists();

        if (! $isValidDistribution) {
            return back()->withErrors([
                'meal_distribution_id' => 'Distribusi makanan yang dipilih tidak terdaftar untuk sekolah ini.',
            ])->withInput();
        }

        $report->update([
            'school_id' => $validated['school_id'],
            'meal_distribution_id' => $validated['meal_distribution_id'],
            'incident_date' => $validated['incident_date'],
            'symptom_description' => $validated['symptom_description'],
            'affected_count' => $validated['affected_count'],
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');

            if ($report->evidenceFile) {
                Storage::disk('public')->delete($report->evidenceFile->file_path);
                $filePath = $file->store('evidences', 'public');
                $report->evidenceFile->update([
                    'file_path' => $filePath,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            } else {
                $filePath = $file->store('evidences', 'public');
                EvidenceFile::create([
                    'incident_report_id' => $report->id,
                    'file_path' => $filePath,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('reports.show', $report)->with('success', 'Laporan kejadian berhasil diperbarui.');
    }

    public function updateStatus(Request $request, IncidentReport $report): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'status' => ['required', 'in:draft,reviewed,escalated,resolved'],
            'community_notes' => ['nullable', 'string'],
            'escalation_notes' => ['nullable', 'string'],
        ]);

        $currentStatus = $report->status;
        $targetStatus = $validated['status'];

        if ($currentStatus === 'draft' && $targetStatus === 'reviewed') {
            if (! $user->isCommunityStaff()) {
                abort(403, 'Hanya Pemantau atau Koordinator yang dapat meninjau laporan.');
            }
        } elseif ($currentStatus === 'reviewed' && $targetStatus === 'escalated') {
            if (! $user->isCommunityStaff()) {
                abort(403, 'Hanya Pemantau atau Koordinator yang dapat meneruskan laporan.');
            }
        } elseif ($currentStatus === 'escalated' && $targetStatus === 'resolved') {
            if (! $user->isKoordinator()) {
                abort(403, 'Hanya Koordinator Komunitas yang dapat menyelesaikan laporan.');
            }
        } else {
            abort(422, 'Transisi status tidak sah menurut alur penanganan laporan (BR-02).');
        }

        $updateData = ['status' => $targetStatus];

        if ($request->filled('community_notes')) {
            $updateData['community_notes'] = $validated['community_notes'];
        }

        if ($request->filled('escalation_notes')) {
            $updateData['escalation_notes'] = $validated['escalation_notes'];
        }

        $report->update($updateData);

        return redirect()->route('reports.show', $report)->with('success', 'Status laporan berhasil diubah menjadi ' . ucfirst($targetStatus) . '.');
    }
}
