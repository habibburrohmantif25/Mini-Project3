<?php

namespace Tests\Feature;

use App\Models\IncidentReport;
use App\Models\MealDistribution;
use App\Models\School;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsyncAndStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelapor;
    protected User $pemantau;
    protected User $koordinator;
    protected School $smpHarapan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->pelapor = User::where('email', 'pelapor@mbg.test')->first();
        $this->pemantau = User::where('email', 'pemantau@mbg.test')->first();
        $this->koordinator = User::where('email', 'koordinator@mbg.test')->first();
        $this->smpHarapan = School::where('name', 'SMP Harapan')->first();
    }

    public function test_async_fetch_school_distributions_endpoint_ac_09(): void
    {
        // As an authenticated user, fetching distributions for a school
        $response = $this->actingAs($this->pelapor)->getJson(
            route('api.schools.distributions', $this->smpHarapan)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'school_id',
                'distribution_date',
                'formatted_date',
                'menu_description',
                'batch_code',
            ],
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals($this->smpHarapan->id, $item['school_id']);
        }
    }

    public function test_pemantau_can_transition_status_from_draft_to_reviewed_ac_06(): void
    {
        $report = IncidentReport::where('status', 'draft')->first();
        $this->assertNotNull($report);

        $response = $this->actingAs($this->pemantau)->patch(route('reports.update-status', $report), [
            'status' => 'reviewed',
            'community_notes' => 'Catatan hasil verifikasi awal oleh pemantau.',
        ]);

        $response->assertRedirect(route('reports.show', $report));
        $report->refresh();
        $this->assertEquals('reviewed', $report->status);
        $this->assertEquals('Catatan hasil verifikasi awal oleh pemantau.', $report->community_notes);
    }

    public function test_pemantau_can_transition_status_from_reviewed_to_escalated_br_02(): void
    {
        $report = IncidentReport::where('status', 'reviewed')->first();
        $this->assertNotNull($report);

        $response = $this->actingAs($this->pemantau)->patch(route('reports.update-status', $report), [
            'status' => 'escalated',
            'escalation_notes' => 'Diteruskan ke pihak penyedia katering.',
        ]);

        $response->assertRedirect(route('reports.show', $report));
        $report->refresh();
        $this->assertEquals('escalated', $report->status);
        $this->assertEquals('Diteruskan ke pihak penyedia katering.', $report->escalation_notes);
    }

    public function test_koordinator_can_transition_status_from_escalated_to_resolved_br_02(): void
    {
        $report = IncidentReport::where('status', 'escalated')->first();
        $this->assertNotNull($report);

        $response = $this->actingAs($this->koordinator)->patch(route('reports.update-status', $report), [
            'status' => 'resolved',
            'escalation_notes' => 'Konfirmasi penanganan selesai dan siswa pulih.',
        ]);

        $response->assertRedirect(route('reports.show', $report));
        $report->refresh();
        $this->assertEquals('resolved', $report->status);
    }

    public function test_pelapor_cannot_update_report_status_ac_06(): void
    {
        $report = IncidentReport::where('status', 'draft')->first();

        // Pelapor attempts to change status to reviewed
        $response = $this->actingAs($this->pelapor)->patch(route('reports.update-status', $report), [
            'status' => 'reviewed',
        ]);

        $response->assertStatus(403);
    }

    public function test_invalid_status_jumping_is_rejected_with_422_br_02(): void
    {
        $draftReport = IncidentReport::where('status', 'draft')->first();

        // Pemantau attempts to skip steps (draft directly to resolved)
        $response = $this->actingAs($this->pemantau)->patch(route('reports.update-status', $draftReport), [
            'status' => 'resolved',
        ]);

        $response->assertStatus(422);
    }

    public function test_pemantau_cannot_resolve_escalated_report_br_02(): void
    {
        $escalatedReport = IncidentReport::where('status', 'escalated')->first();

        // Pemantau cannot resolve (only Koordinator can resolve)
        $response = $this->actingAs($this->pemantau)->patch(route('reports.update-status', $escalatedReport), [
            'status' => 'resolved',
        ]);

        $response->assertStatus(403);
    }
}
