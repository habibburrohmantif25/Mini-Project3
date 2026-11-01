<?php

namespace Tests\Feature;

use App\Models\EvidenceFile;
use App\Models\IncidentReport;
use App\Models\MealDistribution;
use App\Models\ReportObserverLog;
use App\Models\School;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_seeded_users_exist_with_correct_roles(): void
    {
        $pelapor = User::where('email', 'pelapor@mbg.test')->first();
        $this->assertNotNull($pelapor);
        $this->assertTrue($pelapor->isPelapor());
        $this->assertFalse($pelapor->isPemantau());
        $this->assertFalse($pelapor->isKoordinator());

        $pemantau = User::where('email', 'pemantau@mbg.test')->first();
        $this->assertNotNull($pemantau);
        $this->assertTrue($pemantau->isPemantau());
        $this->assertTrue($pemantau->isCommunityStaff());

        $koordinator = User::where('email', 'koordinator@mbg.test')->first();
        $this->assertNotNull($koordinator);
        $this->assertTrue($koordinator->isKoordinator());
        $this->assertTrue($koordinator->isCommunityStaff());
    }

    public function test_school_and_distribution_relationships(): void
    {
        $school = School::where('name', 'SMP Harapan')->first();
        $this->assertNotNull($school);
        $this->assertGreaterThanOrEqual(2, $school->mealDistributions->count());

        $distribution = $school->mealDistributions->first();
        $this->assertEquals($school->id, $distribution->school->id);
    }

    public function test_incident_report_relationships(): void
    {
        $report = IncidentReport::where('status', 'escalated')->first();
        $this->assertNotNull($report);

        // BelongsTo relationships
        $this->assertInstanceOf(User::class, $report->user);
        $this->assertInstanceOf(School::class, $report->school);
        $this->assertInstanceOf(MealDistribution::class, $report->mealDistribution);

        // HasOne relationship
        $this->assertInstanceOf(EvidenceFile::class, $report->evidenceFile);
        $this->assertEquals($report->id, $report->evidenceFile->incidentReport->id);

        // HasMany relationship
        $this->assertGreaterThanOrEqual(1, $report->observerLogs->count());
        $firstLog = $report->observerLogs->first();
        $this->assertInstanceOf(ReportObserverLog::class, $firstLog);
        $this->assertInstanceOf(User::class, $firstLog->changedBy);
    }
}
