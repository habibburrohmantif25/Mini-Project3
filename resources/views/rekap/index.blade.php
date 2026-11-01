@extends('layouts.app')

@section('title', 'Rekap Laporan Kejadian - Sistem Pemantauan MBG')

@section('content')
<div style="margin-bottom: 15px;">
    <h2 style="margin: 0;">Rekap Laporan Kejadian</h2>
    <p style="margin: 3px 0 0; color: #666; font-size: 13px;">
        Ringkasan data laporan masuk berdasarkan filter yang dipilih.
    </p>
</div>

<!-- Filter Form -->
<div class="card">
    <form method="GET" action="{{ route('rekap.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 160px;">
            <label style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Sekolah</label>
            <select name="school_id">
                <option value="">-- Semua Sekolah --</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}">
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}">
        </div>

        <div style="margin-bottom: 12px; display: flex; gap: 5px;">
            <button type="submit" class="btn">Terapkan Filter</button>
            <a href="{{ route('rekap.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
    <div class="card" style="flex: 1; min-width: 160px; text-align: center; padding: 20px;">
        <div style="font-size: 36px; font-weight: bold; color: #333;">{{ $totalLaporan }}</div>
        <div style="font-size: 13px; color: #666; margin-top: 5px;">Total Laporan Kejadian</div>
    </div>

    <div class="card" style="flex: 1; min-width: 160px; text-align: center; padding: 20px;">
        <div style="font-size: 36px; font-weight: bold; color: #e67e22;">{{ $dalamProses }}</div>
        <div style="font-size: 13px; color: #666; margin-top: 5px;">Dalam Proses (Draft + Reviewed)</div>
    </div>

    <div class="card" style="flex: 1; min-width: 160px; text-align: center; padding: 20px;">
        <div style="font-size: 36px; font-weight: bold; color: #27ae60;">{{ $selesai }}</div>
        <div style="font-size: 13px; color: #666; margin-top: 5px;">Tindak Lanjut (Escalated + Resolved)</div>
    </div>
</div>

<!-- Rekap Table -->
<div class="card">
    <h3 style="margin-top: 0;">Daftar Laporan Ter-filter</h3>
    <table>
        <thead>
            <tr>
                <th>Kode Laporan</th>
                <th>Sekolah</th>
                <th>Pelapor</th>
                <th>Tanggal Kejadian</th>
                <th>Siswa Terdampak</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>
                        <a href="{{ route('reports.show', $report) }}" style="color: #007bff; font-weight: bold; text-decoration: none;">
                            {{ $report->report_code }}
                        </a>
                    </td>
                    <td>{{ $report->school->name }}</td>
                    <td>{{ $report->user->name }}</td>
                    <td>{{ $report->incident_date->format('d/m/Y H:i') }}</td>
                    <td>{{ $report->affected_count }} siswa</td>
                    <td><span class="badge">{{ ucfirst($report->status) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #777; padding: 25px;">
                        Tidak ada laporan yang cocok dengan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 15px;">
        {{ $reports->links() }}
    </div>
</div>
@endsection
