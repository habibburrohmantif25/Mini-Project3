@extends('layouts.app')

@section('title', 'Detail Laporan ' . $report->report_code . ' - Sistem Pemantauan MBG')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0;">Laporan: {{ $report->report_code }}</h2>
        <p style="margin: 3px 0 0; color: #666; font-size: 13px;">
            Status: <span class="badge">{{ ucfirst($report->status) }}</span>
        </p>
    </div>

    <div>
        @if(auth()->user()->id === $report->user_id && $report->status === 'draft')
            <a href="{{ route('reports.edit', $report) }}" class="btn">Edit Laporan</a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>

<!-- Informasi Utama Laporan -->
<div class="card">
    <h3>Informasi Kejadian</h3>
    <table>
        <tr>
            <th style="width: 200px;">Kode Laporan</th>
            <td><strong>{{ $report->report_code }}</strong></td>
        </tr>
        <tr>
            <th>Pelapor</th>
            <td>{{ $report->user->name }} ({{ $report->user->email }})</td>
        </tr>
        <tr>
            <th>Sekolah Terkait</th>
            <td>{{ $report->school->name }} - {{ $report->school->address }}</td>
        </tr>
        <tr>
            <th>Distribusi Makanan</th>
            <td>
                @if($report->mealDistribution)
                    Tanggal: {{ $report->mealDistribution->distribution_date->format('d/m/Y') }}<br>
                    Menu: {{ $report->mealDistribution->menu_description }}<br>
                    Batch: {{ $report->mealDistribution->batch_code ?? '-' }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>Waktu Kejadian Gejala</th>
            <td>{{ $report->incident_date->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Jumlah Siswa Terdampak</th>
            <td>{{ $report->affected_count }} siswa</td>
        </tr>
        <tr>
            <th>Deskripsi Gejala</th>
            <td>{{ $report->symptom_description }}</td>
        </tr>
    </table>
</div>

<!-- Bukti Pendukung -->
<div class="card">
    <h3>Bukti Pendukung</h3>
    @if($report->evidenceFile)
        <div style="margin-bottom: 10px; font-size: 14px;">
            <div><strong>Nama File:</strong> {{ $report->evidenceFile->original_filename }}</div>
            <div><strong>Ukuran:</strong> {{ round($report->evidenceFile->file_size / 1024, 1) }} KB</div>
            <div><strong>Tipe:</strong> {{ $report->evidenceFile->mime_type }}</div>
        </div>

        <div style="margin-top: 10px;">
            @if(str_starts_with($report->evidenceFile->mime_type, 'image/'))
                <img src="{{ Storage::url($report->evidenceFile->file_path) }}" alt="Bukti Kejadian" style="max-width: 100%; max-height: 400px; border: 1px solid #ddd; padding: 4px;">
            @else
                <a href="{{ Storage::url($report->evidenceFile->file_path) }}" target="_blank" class="btn btn-secondary btn-sm">
                    Buka Dokumen PDF Bukti
                </a>
            @endif
        </div>
    @else
        <p style="color: #777; font-size: 14px; margin: 0;">Tidak ada file bukti pendukung yang dilampirkan.</p>
    @endif
</div>

<!-- Catatan Peninjauan & Tindak Lanjut -->
<div class="card">
    <h3>Catatan Peninjauan & Tindak Lanjut</h3>
    <table>
        <tr>
            <th style="width: 250px;">Catatan Pemantau Komunitas</th>
            <td>{{ $report->community_notes ?? 'Belum ada catatan dari Pemantau Komunitas.' }}</td>
        </tr>
        <tr>
            <th>Catatan Eskalasi Koordinator</th>
            <td>{{ $report->escalation_notes ?? 'Belum ada catatan tindak lanjut dari Koordinator.' }}</td>
        </tr>
    </table>
</div>

<!-- Log Riwayat Status (Audit Observer) -->
<div class="card">
    <h3>Riwayat Perubahan Status (Audit Log)</h3>
    @if($report->observerLogs->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Diubah Oleh</th>
                    <th>Perubahan Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->observerLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $log->changedBy ? $log->changedBy->name . ' (' . ucfirst($log->changedBy->role) . ')' : 'Sistem' }}</td>
                        <td>
                            <span class="badge">{{ $log->old_value ?? 'none' }}</span> &rarr;
                            <span class="badge">{{ $log->new_value }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #777; font-size: 14px; margin: 0;">Belum ada riwayat perubahan status pada laporan ini.</p>
    @endif
</div>
@endsection
