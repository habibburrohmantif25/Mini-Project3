@extends('layouts.app')

@section('title', 'Masuk - Sistem Pemantauan MBG')

@section('content')
<div class="card auth-box">
    <h2>Selamat Datang</h2>
    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Silakan masuk ke akun Anda.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
            @error('email')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label style="font-weight: normal; font-size: 13px;">
                <input type="checkbox" name="remember"> Ingat saya
            </label>
        </div>

        <button type="submit" class="btn btn-block">Masuk</button>
    </form>

    <div style="margin-top: 15px; text-align: center; font-size: 13px;">
        Belum punya akun? <a href="{{ route('register') }}">Daftar Pelapor</a>
    </div>

    <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #ccc; font-size: 13px;">
        <div style="margin-bottom: 8px; font-weight: bold;">Akun Demo:</div>
        <div style="display: flex; gap: 5px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('pelapor@mbg.test', 'password')">Pelapor</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('pemantau@mbg.test', 'password')">Pemantau</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('koordinator@mbg.test', 'password')">Koordinator</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endpush
@endsection
