<?php

namespace Database\Seeders;

use App\Models\EvidenceFile;
use App\Models\IncidentReport;
use App\Models\MealDistribution;
use App\Models\ReportObserverLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $pelapor = User::query()->firstOrCreate(
            ['email' => 'pelapor@mbg.test'],
            [
                'name' => 'Budi Santoso (Pelapor)',
                'password' => Hash::make('password'),
                'role' => 'pelapor',
            ]
        );

        $pemantau = User::query()->firstOrCreate(
            ['email' => 'pemantau@mbg.test'],
            [
                'name' => 'Siti Rahma (Pemantau Komunitas)',
                'password' => Hash::make('password'),
                'role' => 'pemantau',
            ]
        );

        $koordinator = User::query()->firstOrCreate(
            ['email' => 'koordinator@mbg.test'],
            [
                'name' => 'Ahmad Fauzi (Koordinator Komunitas)',
                'password' => Hash::make('password'),
                'role' => 'koordinator',
            ]
        );

        $smpHarapan = School::query()->firstOrCreate(
            ['name' => 'SMP Harapan'],
            [
                'address' => 'Jl. Pendidikan No. 12',
                'contact_person' => 'Drs. Supriyadi',
                'phone' => '081234567890',
            ]
        );

        $sdCendekia = School::query()->firstOrCreate(
            ['name' => 'SD Negeri 01 Cendekia'],
            [
                'address' => 'Jl. Merdeka No. 5',
                'contact_person' => 'Hj. Nuraini, S.Pd',
                'phone' => '081298765432',
            ]
        );

        $distSmp1 = MealDistribution::query()->firstOrCreate(
            [
                'school_id' => $smpHarapan->id,
                'distribution_date' => '2026-10-28',
            ],
            [
                'menu_description' => 'Nasi, Ayam Goreng, Sayur Sop, Pisang',
                'batch_code' => 'BATCH-SMP-20261028-01',
            ]
        );

        $distSmp2 = MealDistribution::query()->firstOrCreate(
            [
                'school_id' => $smpHarapan->id,
                'distribution_date' => '2026-10-30',
            ],
            [
                'menu_description' => 'Nasi, Semur Daging, Tumis Buncis, Apel',
                'batch_code' => 'BATCH-SMP-20261030-02',
            ]
        );

        $distSd1 = MealDistribution::query()->firstOrCreate(
            [
                'school_id' => $sdCendekia->id,
                'distribution_date' => '2026-10-29',
            ],
            [
                'menu_description' => 'Nasi, Ikan Bakar, Capcay, Jeruk',
                'batch_code' => 'BATCH-SD-20261029-01',
            ]
        );

        $reportDraft = IncidentReport::query()->firstOrCreate(
            ['report_code' => 'REP-20261030-001'],
            [
                'user_id' => $pelapor->id,
                'school_id' => $smpHarapan->id,
                'meal_distribution_id' => $distSmp2->id,
                'incident_date' => '2026-10-30 13:30:00',
                'symptom_description' => 'Siswa mengeluhkan rasa mual dan pusing setelah mengonsumsi menu semur daging saat jam istirahat kedua.',
                'affected_count' => 2,
                'status' => 'draft',
                'community_notes' => null,
                'escalation_notes' => null,
            ]
        );

        $reportReviewed = IncidentReport::query()->firstOrCreate(
            ['report_code' => 'REP-20261028-001'],
            [
                'user_id' => $pelapor->id,
                'school_id' => $smpHarapan->id,
                'meal_distribution_id' => $distSmp1->id,
                'incident_date' => '2026-10-28 14:15:00',
                'symptom_description' => 'Perut mulas disertai diare berulang sekitar 2 jam setelah menyantap makanan katering.',
                'affected_count' => 3,
                'status' => 'reviewed',
                'community_notes' => 'Laporan telah diverifikasi oleh Pemantau Komunitas. Ditemukan keluhan serupa pada 3 siswa kelas 8.',
                'escalation_notes' => null,
            ]
        );

        ReportObserverLog::query()->firstOrCreate(
            [
                'incident_report_id' => $reportReviewed->id,
                'new_value' => 'reviewed',
            ],
            [
                'changed_by_user_id' => $pemantau->id,
                'field_changed' => 'status',
                'old_value' => 'draft',
                'created_at' => now()->subHours(20),
                'updated_at' => now()->subHours(20),
            ]
        );

        $reportEscalated = IncidentReport::query()->firstOrCreate(
            ['report_code' => 'REP-20261028-002'],
            [
                'user_id' => $pelapor->id,
                'school_id' => $smpHarapan->id,
                'meal_distribution_id' => $distSmp1->id,
                'incident_date' => '2026-10-28 15:00:00',
                'symptom_description' => 'Muntah-muntah dan demam ringan dialami oleh beberapa siswa pasca konsumsi lauk ayam goreng.',
                'affected_count' => 5,
                'status' => 'escalated',
                'community_notes' => 'Terindikasi potensi masalah kualitas makanan pada batch distribusi yang sama.',
                'escalation_notes' => 'Informasi telah diteruskan secara resmi ke pihak SPPG katering dan Puskesmas untuk investigasi sampel makanan.',
            ]
        );

        ReportObserverLog::query()->firstOrCreate(
            [
                'incident_report_id' => $reportEscalated->id,
                'new_value' => 'reviewed',
            ],
            [
                'changed_by_user_id' => $pemantau->id,
                'field_changed' => 'status',
                'old_value' => 'draft',
                'created_at' => now()->subHours(18),
                'updated_at' => now()->subHours(18),
            ]
        );

        ReportObserverLog::query()->firstOrCreate(
            [
                'incident_report_id' => $reportEscalated->id,
                'new_value' => 'escalated',
            ],
            [
                'changed_by_user_id' => $pemantau->id,
                'field_changed' => 'status',
                'old_value' => 'reviewed',
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ]
        );

        EvidenceFile::query()->firstOrCreate(
            ['incident_report_id' => $reportEscalated->id],
            [
                'file_path' => 'evidences/sample_ayam_goreng.jpg',
                'original_filename' => 'foto_ayam_goreng_curiga_basi.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 245760,
            ]
        );

        $reportResolved = IncidentReport::query()->firstOrCreate(
            ['report_code' => 'REP-20261029-001'],
            [
                'user_id' => $pelapor->id,
                'school_id' => $sdCendekia->id,
                'meal_distribution_id' => $distSd1->id,
                'incident_date' => '2026-10-29 13:15:00',
                'symptom_description' => 'Gatal-gatal dan kemerahan pada kulit setelah mengonsumsi menu ikan bakar.',
                'affected_count' => 1,
                'status' => 'resolved',
                'community_notes' => 'Gejala telah dikonfirmasi sebagai reaksi alergi spesifik makanan laut.',
                'escalation_notes' => 'Pihak sekolah telah berkoordinasi dengan wali murid dan faskes terdekat. Penanganan selesai dan kondisi siswa membaik.',
            ]
        );

        ReportObserverLog::query()->firstOrCreate(
            [
                'incident_report_id' => $reportResolved->id,
                'new_value' => 'reviewed',
            ],
            [
                'changed_by_user_id' => $pemantau->id,
                'field_changed' => 'status',
                'old_value' => 'draft',
                'created_at' => now()->subHours(15),
                'updated_at' => now()->subHours(15),
            ]
        );

        ReportObserverLog::query()->firstOrCreate(
            [
                'incident_report_id' => $reportResolved->id,
                'new_value' => 'escalated',
            ],
            [
                'changed_by_user_id' => $pemantau->id,
                'field_changed' => 'status',
                'old_value' => 'reviewed',
                'created_at' => now()->subHours(8),
                'updated_at' => now()->subHours(8),
            ]
        );

        ReportObserverLog::query()->firstOrCreate(
            [
                'incident_report_id' => $reportResolved->id,
                'new_value' => 'resolved',
            ],
            [
                'changed_by_user_id' => $koordinator->id,
                'field_changed' => 'status',
                'old_value' => 'escalated',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]
        );
    }
}
