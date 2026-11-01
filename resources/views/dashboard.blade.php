@extends('layouts.app')

@section('title', 'Dashboard - Sistem Pemantauan MBG')

@section('content')
<div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <div>
        <h2 style="margin: 0;">Halo, {{ auth()->user()->name }}</h2>
        <p style="margin: 5px 0 0; color: #666; font-size: 14px;">
            Peran: <span class="badge">{{ ucfirst(auth()->user()->role) }}</span>
        </p>
    </div>

    <div>
        @if(auth()->user()->isPelapor())
            <a href="{{ route('reports.create') }}" class="btn">+ Buat Laporan Baru</a>
        @else
            <a href="{{ route('reports.index') }}" class="btn">Tinjau Laporan</a>
        @endif
    </div>
</div>

<div class="stats-grid">
    <div class="stat-item">
        <div style="font-size: 13px; color: #666;">{{ auth()->user()->isPelapor() ? 'Laporan Saya' : 'Total Laporan' }}</div>
        <div class="stat-num">{{ auth()->user()->isPelapor() ? $stats['my_reports'] : $stats['total_reports'] }}</div>
    </div>
    <div class="stat-item">
        <div style="font-size: 13px; color: #666;">Draft</div>
        <div class="stat-num">{{ $stats['draft_reports'] }}</div>
    </div>
    <div class="stat-item">
        <div style="font-size: 13px; color: #666;">Ditinjau</div>
        <div class="stat-num">{{ $stats['reviewed_reports'] }}</div>
    </div>
    <div class="stat-item">
        <div style="font-size: 13px; color: #666;">Diteruskan / Selesai</div>
        <div class="stat-num">{{ $stats['escalated_reports'] + $stats['resolved_reports'] }}</div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h3 style="margin: 0;">{{ auth()->user()->isPelapor() ? 'Laporan Terkini Anda' : 'Laporan Masuk Terkini' }}</h3>
        <a href="{{ route('reports.index') }}" style="font-size: 13px;">Lihat Semua</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Sekolah</th>
                <th>Tanggal</th>
                <th>Gejala</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentReports as $report)
                <tr>
                    <td><strong>{{ $report->report_code }}</strong></td>
                    <td>{{ $report->school->name }}</td>
                    <td>{{ $report->incident_date->format('d/m/Y H:i') }}</td>
                    <td>{{ $report->symptom_description }}</td>
                    <td><span class="badge">{{ ucfirst($report->status) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #777; padding: 20px;">
                        Belum ada laporan kejadian yang tercatat.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
