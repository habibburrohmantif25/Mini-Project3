@extends('layouts.app')

@section('title', 'Daftar Pelapor - Sistem Pemantauan MBG')

@section('content')
<div class="card auth-box">
    <h2>Daftar Akun Pelapor</h2>
    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Daftar akun untuk orang tua siswa / masyarakat.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Nama lengkap">
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter">
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
        </div>

        <button type="submit" class="btn btn-block">Daftar</button>
    </form>

    <div style="margin-top: 15px; text-align: center; font-size: 13px;">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </div>
</div>
@endsection
