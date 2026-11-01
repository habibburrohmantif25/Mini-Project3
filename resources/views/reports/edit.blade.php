@extends('layouts.app')

@section('title', 'Edit Laporan ' . $report->report_code . ' - Sistem Pemantauan MBG')

@section('content')
<div class="card" style="max-width: 700px; margin: 0 auto;">
    <h2>Edit Laporan: {{ $report->report_code }}</h2>
    <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
        Perubahan data laporan hanya dapat dilakukan selama laporan masih berstatus <strong>Draft</strong>.
    </p>

    <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="school_id">Pilih Sekolah <span style="color:red;">*</span></label>
            <select id="school_id" name="school_id" required>
                <option value="">-- Pilih Sekolah --</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id', $report->school_id) == $school->id ? 'selected' : '' }}>
                        {{ $school->name }} ({{ $school->address }})
                    </option>
                @endforeach
            </select>
            @error('school_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="meal_distribution_id">Pilih Distribusi Makanan Terkait <span style="color:red;">*</span></label>
            <select id="meal_distribution_id" name="meal_distribution_id" required>
                <option value="">-- Pilih Distribusi Makanan --</option>
                @foreach($schools as $school)
                    <optgroup label="{{ $school->name }}">
                        @foreach($school->mealDistributions as $dist)
                            <option value="{{ $dist->id }}" data-school-id="{{ $school->id }}" {{ old('meal_distribution_id', $report->meal_distribution_id) == $dist->id ? 'selected' : '' }}>
                                {{ $dist->distribution_date->format('d/m/Y') }} - {{ $dist->menu_description }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('meal_distribution_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 2; min-width: 200px;">
                <label for="incident_date">Waktu Kejadian Gejala <span style="color:red;">*</span></label>
                <input type="datetime-local" id="incident_date" name="incident_date" value="{{ old('incident_date', $report->incident_date->format('Y-m-d\TH:i')) }}" required>
                @error('incident_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="flex: 1; min-width: 140px;">
                <label for="affected_count">Siswa Terdampak <span style="color:red;">*</span></label>
                <input type="number" id="affected_count" name="affected_count" min="1" value="{{ old('affected_count', $report->affected_count) }}" required>
                @error('affected_count')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="symptom_description">Deskripsi Gejala & Keluhan <span style="color:red;">*</span></label>
            <textarea id="symptom_description" name="symptom_description" rows="4" required>{{ old('symptom_description', $report->symptom_description) }}</textarea>
            @error('symptom_description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="evidence">Ganti Bukti Pendukung (Opsional)</label>
            @if($report->evidenceFile)
                <div style="font-size: 13px; color: #444; margin-bottom: 6px;">
                    File saat ini: <strong>{{ $report->evidenceFile->original_filename }}</strong> ({{ round($report->evidenceFile->file_size / 1024, 1) }} KB)
                </div>
            @endif
            <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf">
            <div style="font-size: 12px; color: #777;">Format file: JPG, PNG, atau PDF. Maksimal 2 MB (2048 KB). Biarkan kosong jika tidak ingin mengganti.</div>
            @error('evidence')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" class="btn">Simpan Perubahan</button>
            <a href="{{ route('reports.show', $report) }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
