@extends('layouts.app')

@section('title', 'Sistem Pemantauan Keamanan Makanan MBG')

@section('content')
<div class="card" style="text-align: center; padding: 30px 15px;">
    <h2>Sistem Pemantauan Keamanan Makanan MBG</h2>
    <p style="color: #666; max-width: 600px; margin: 10px auto 20px;">
        Aplikasi pemantauan dan pelaporan keluhan kesehatan terkait makanan program Makanan Bergizi Gratis (MBG) bagi siswa sekolah.
    </p>

    <div>
        @auth
            <a href="{{ route('dashboard') }}" class="btn">Buka Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn">Masuk</a>
            <a href="{{ route('register') }}" class="btn btn-secondary">Daftar Pelapor</a>
        @endauth
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
    <div class="card">
        <h3>1. Pelaporan Kejadian</h3>
        <p style="font-size: 14px; color: #555;">
            Pelapor dapat menyampaikan laporan keluhan kesehatan makanan disertai informasi sekolah, tanggal distribusi, dan bukti foto.
        </p>
    </div>
    <div class="card">
        <h3>2. Peninjauan Komunitas</h3>
        <p style="font-size: 14px; color: #555;">
            Pemantau Komunitas memeriksa kelengkapan laporan dan mendeteksi pola keluhan yang sama pada distribusi makanan tertentu.
        </p>
    </div>
    <div class="card">
        <h3>3. Tindak Lanjut Koordinator</h3>
        <p style="font-size: 14px; color: #555;">
            Koordinator meneruskan laporan ke pihak eksternal terkait (Sekolah, SPPG, Puskesmas) dan mencatat hasil penyelesaiannya.
        </p>
    </div>
</div>
@endsection
