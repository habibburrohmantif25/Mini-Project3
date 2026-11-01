<?php

namespace Tests\Feature;

use App\Models\EvidenceFile;
use App\Models\IncidentReport;
use App\Models\MealDistribution;
use App\Models\School;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncidentReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelapor;
    protected User $otherPelapor;
    protected School $smpHarapan;
    protected School $sdCendekia;
    protected MealDistribution $distSmp;
    protected MealDistribution $distSd;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->pelapor = User::where('email', 'pelapor@mbg.test')->first();
        $this->otherPelapor = User::create([
            'name' => 'Pelapor B',
            'email' => 'pelapor.b@mbg.test',
            'password' => bcrypt('password'),
            'role' => 'pelapor',
        ]);

        $this->smpHarapan = School::where('name', 'SMP Harapan')->first();
        $this->sdCendekia = School::where('name', 'SD Negeri 01 Cendekia')->first();

        $this->distSmp = MealDistribution::where('school_id', $this->smpHarapan->id)->first();
        $this->distSd = MealDistribution::where('school_id', $this->sdCendekia->id)->first();
    }

    public function test_pelapor_can_view_create_report_form(): void
    {
        $response = $this->actingAs($this->pelapor)->get(route('reports.create'));
        $response->assertStatus(200);
        $response->assertSee('Form Pelaporan Kejadian MBG');
    }

    public function test_pelapor_can_submit_valid_incident_report_ac_02(): void
    {
        Storage::fake('public');

        $evidence = UploadedFile::fake()->image('bukti_makanan.jpg', 600, 400)->size(500); // 500 KB

        $response = $this->actingAs($this->pelapor)->post(route('reports.store'), [
            'school_id' => $this->smpHarapan->id,
            'meal_distribution_id' => $this->distSmp->id,
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'symptom_description' => 'Mual, pusing, dan muntah berulang setelah makan siang katering sekolah.',
            'affected_count' => 3,
            'evidence' => $evidence,
        ]);

        $report = IncidentReport::where('user_id', $this->pelapor->id)->latest('id')->first();
        $this->assertNotNull($report);
        $this->assertEquals('draft', $report->status);
        $this->assertStringStartsWith('REP-', $report->report_code);

        $response->assertRedirect(route('reports.show', $report));

        // Evidence file check (AC-04)
        $this->assertNotNull($report->evidenceFile);
        $this->assertEquals('bukti_makanan.jpg', $report->evidenceFile->original_filename);
        Storage::disk('public')->assertExists($report->evidenceFile->file_path);
    }

    public function test_validation_fails_for_empty_data_or_short_symptoms_ac_03(): void
    {
        $response = $this->actingAs($this->pelapor)->post(route('reports.store'), [
            'school_id' => '',
            'meal_distribution_id' => '',
            'incident_date' => '',
            'symptom_description' => 'pendek', // Less than 10 characters
            'affected_count' => 0,
        ]);

        $response->assertSessionHasErrors(['school_id', 'meal_distribution_id', 'incident_date', 'symptom_description', 'affected_count']);
    }

    public function test_evidence_upload_rejects_invalid_file_type_and_oversize_ac_04(): void
    {
        Storage::fake('public');

        // Test 1: Disallowed file type (.exe)
        $badFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');
        $response = $this->actingAs($this->pelapor)->post(route('reports.store'), [
            'school_id' => $this->smpHarapan->id,
            'meal_distribution_id' => $this->distSmp->id,
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'symptom_description' => 'Mual dan sakit kepala berat setelah makan siang.',
            'affected_count' => 1,
            'evidence' => $badFile,
        ]);
        $response->assertSessionHasErrors('evidence');

        // Test 2: Oversize file (> 2048 KB / 2MB)
        $oversizedPdf = UploadedFile::fake()->create('dokumen_besar.pdf', 3000, 'application/pdf'); // 3MB
        $response2 = $this->actingAs($this->pelapor)->post(route('reports.store'), [
            'school_id' => $this->smpHarapan->id,
            'meal_distribution_id' => $this->distSmp->id,
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'symptom_description' => 'Mual dan sakit kepala berat setelah makan siang.',
            'affected_count' => 1,
            'evidence' => $oversizedPdf,
        ]);
        $response2->assertSessionHasErrors('evidence');
    }

    public function test_business_rule_br_03_mismatched_school_and_distribution_rejected(): void
    {
        // Select SMP Harapan, but submit distribution ID belonging to SD Cendekia
        $response = $this->actingAs($this->pelapor)->post(route('reports.store'), [
            'school_id' => $this->smpHarapan->id,
            'meal_distribution_id' => $this->distSd->id,
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'symptom_description' => 'Sakit perut hebat setelah makan siang katering.',
            'affected_count' => 2,
        ]);

        $response->assertSessionHasErrors('meal_distribution_id');
    }

    public function test_pelapor_can_update_draft_report_br_01(): void
    {
        $draftReport = IncidentReport::where('user_id', $this->pelapor->id)
            ->where('status', 'draft')
            ->first();

        $this->assertNotNull($draftReport);

        $response = $this->actingAs($this->pelapor)->put(route('reports.update', $draftReport), [
            'school_id' => $draftReport->school_id,
            'meal_distribution_id' => $draftReport->meal_distribution_id,
            'incident_date' => $draftReport->incident_date->format('Y-m-d H:i:s'),
            'symptom_description' => 'Deskripsi gejala yang telah diperbarui dan lebih detail oleh pelapor.',
            'affected_count' => 4,
        ]);

        $response->assertRedirect(route('reports.show', $draftReport));
        $draftReport->refresh();
        $this->assertEquals(4, $draftReport->affected_count);
        $this->assertStringContainsString('diperbarui', $draftReport->symptom_description);
    }

    public function test_pelapor_cannot_update_reviewed_report_br_01_ac_08(): void
    {
        $reviewedReport = IncidentReport::where('status', 'reviewed')->first();
        $this->assertNotNull($reviewedReport);

        // Even if Pelapor is the owner, once status is reviewed, edit must be rejected (403 Forbidden)
        $response = $this->actingAs($this->pelapor)->put(route('reports.update', $reviewedReport), [
            'school_id' => $reviewedReport->school_id,
            'meal_distribution_id' => $reviewedReport->meal_distribution_id,
            'incident_date' => $reviewedReport->incident_date->format('Y-m-d H:i:s'),
            'symptom_description' => 'Mencoba mengedit laporan yang sudah direview.',
            'affected_count' => 2,
        ]);

        $response->assertStatus(403);
    }

    public function test_ownership_privacy_pelapor_cannot_access_other_pelapor_report_ac_05(): void
    {
        $reportOfPelaporA = IncidentReport::where('user_id', $this->pelapor->id)->first();

        // Pelapor B tries to view report of Pelapor A
        $response = $this->actingAs($this->otherPelapor)->get(route('reports.show', $reportOfPelaporA));
        $response->assertStatus(403);

        // Pelapor B tries to edit report of Pelapor A
        $editResponse = $this->actingAs($this->otherPelapor)->get(route('reports.edit', $reportOfPelaporA));
        $editResponse->assertStatus(403);
    }
}
