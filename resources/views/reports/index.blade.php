@extends('layouts.app')

@section('title', 'Daftar Laporan Kejadian - Sistem Pemantauan MBG')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
    <div>
        <h2 style="margin: 0;">{{ auth()->user()->isPelapor() ? 'Laporan Kejadian Saya' : 'Daftar Seluruh Laporan Masuk' }}</h2>
        <p style="margin: 3px 0 0; color: #666; font-size: 13px;">
            Total: {{ $reports->total() }} laporan
        </p>
    </div>

    @if(auth()->user()->isPelapor())
        <div>
            <a href="{{ route('reports.create') }}" class="btn">+ Buat Laporan Baru</a>
        </div>
    @endif
</div>

<!-- Filter & Search Form -->
<div class="card">
    <form method="GET" action="{{ route('reports.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 200px;">
            <label style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Pencarian</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode laporan atau gejala...">
        </div>

        <div style="flex: 1; min-width: 150px;">
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

        <div style="flex: 1; min-width: 130px;">
            <label style="font-size: 13px; font-weight: bold; display: block; margin-bottom: 4px;">Status</label>
            <select name="status">
                <option value="">-- Semua Status --</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escalated</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>

        <div style="margin-bottom: 12px; display: flex; gap: 5px;">
            <button type="submit" class="btn">Filter</button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Reports Table -->
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Sekolah</th>
                <th>Tanggal Kejadian</th>
                <th>Menu Distribusi</th>
                <th>Siswa</th>
                <th>Bukti</th>
                <th>Status</th>
                <th>Aksi</th>
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
                    <td>{{ $report->incident_date->format('d/m/Y H:i') }}</td>
                    <td>{{ $report->mealDistribution ? Str::limit($report->mealDistribution->menu_description, 25) : '-' }}</td>
                    <td>{{ $report->affected_count }}</td>
                    <td>
                        @if($report->evidenceFile)
                            <span class="badge" style="background: #e2e8f0;">Ada</span>
                        @else
                            <span style="color: #aaa;">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge">{{ ucfirst($report->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('reports.show', $report) }}" class="btn btn-secondary btn-sm">Lihat</a>
                        @if(auth()->user()->id === $report->user_id && $report->status === 'draft')
                            <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #777; padding: 25px;">
                        Tidak ada laporan kejadian yang cocok dengan kriteria pencarian.
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
