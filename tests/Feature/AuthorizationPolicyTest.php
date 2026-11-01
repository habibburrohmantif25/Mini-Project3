<?php

namespace Tests\Feature;

use App\Models\IncidentReport;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelapor;
    protected User $otherPelapor;
    protected User $pemantau;
    protected User $koordinator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->pelapor = User::where('email', 'pelapor@mbg.test')->first();
        $this->pemantau = User::where('email', 'pemantau@mbg.test')->first();
        $this->koordinator = User::where('email', 'koordinator@mbg.test')->first();

        $this->otherPelapor = User::create([
            'name' => 'Pelapor B',
            'email' => 'pelapor.b@mbg.test',
            'password' => bcrypt('password'),
            'role' => 'pelapor',
        ]);
    }

    public function test_role_middleware_restricts_rekap_access_for_pelapor_ac_07(): void
    {
        // AC-07: Pelapor accessing /rekap must get 403 Forbidden
        $response = $this->actingAs($this->pelapor)->get('/rekap');
        $response->assertStatus(403);

        // Pemantau & Koordinator can access /rekap
        $this->actingAs($this->pemantau)->get('/rekap')->assertStatus(200);
        $this->actingAs($this->koordinator)->get('/rekap')->assertStatus(200);
    }

    public function test_role_middleware_restricts_report_creation_to_pelapor_only(): void
    {
        // Pelapor can access /reports/create
        $this->actingAs($this->pelapor)->get('/reports/create')->assertStatus(200);

        // Pemantau and Koordinator are denied (403) from creating reports
        $this->actingAs($this->pemantau)->get('/reports/create')->assertStatus(403);
        $this->actingAs($this->koordinator)->get('/reports/create')->assertStatus(403);
    }

    public function test_incident_report_policy_view_ac_05(): void
    {
        $reportOfPelaporA = IncidentReport::where('user_id', $this->pelapor->id)->first();

        // Owner can view
        $this->assertTrue(Gate::forUser($this->pelapor)->allows('view', $reportOfPelaporA));

        // Other pelapor CANNOT view (Ownership privacy / AC-05)
        $this->assertFalse(Gate::forUser($this->otherPelapor)->allows('view', $reportOfPelaporA));

        // Pemantau and Koordinator can view
        $this->assertTrue(Gate::forUser($this->pemantau)->allows('view', $reportOfPelaporA));
        $this->assertTrue(Gate::forUser($this->koordinator)->allows('view', $reportOfPelaporA));
    }

    public function test_incident_report_policy_update_locking_br_01(): void
    {
        $draftReport = IncidentReport::where('status', 'draft')->first();
        $reviewedReport = IncidentReport::where('status', 'reviewed')->first();
        $escalatedReport = IncidentReport::where('status', 'escalated')->first();
        $resolvedReport = IncidentReport::where('status', 'resolved')->first();

        // Pelapor owner can only update if status is 'draft'
        $this->assertTrue(Gate::forUser($this->pelapor)->allows('update', $draftReport));

        // BR-01: Update is locked and disallowed when status is reviewed, escalated, or resolved
        $this->assertFalse(Gate::forUser($this->pelapor)->allows('update', $reviewedReport));
        $this->assertFalse(Gate::forUser($this->pelapor)->allows('update', $escalatedReport));
        $this->assertFalse(Gate::forUser($this->pelapor)->allows('update', $resolvedReport));

        // Non-owner pelapor cannot update even if draft
        $this->assertFalse(Gate::forUser($this->otherPelapor)->allows('update', $draftReport));
    }

    public function test_incident_report_policy_review_and_resolve_privileges(): void
    {
        $report = IncidentReport::first();

        // Review is allowed for pemantau and koordinator, but disallowed for pelapor
        $this->assertTrue(Gate::forUser($this->pemantau)->allows('review', $report));
        $this->assertTrue(Gate::forUser($this->koordinator)->allows('review', $report));
        $this->assertFalse(Gate::forUser($this->pelapor)->allows('review', $report));

        // Resolve is strictly for koordinator
        $this->assertTrue(Gate::forUser($this->koordinator)->allows('resolve', $report));
        $this->assertFalse(Gate::forUser($this->pemantau)->allows('resolve', $report));
        $this->assertFalse(Gate::forUser($this->pelapor)->allows('resolve', $report));
    }
}
