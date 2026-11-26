@extends('layouts.auth')

@section('title', 'Buat Akun')

@section('content')
    <h1>Buat akun investasi</h1>
    <p>Mulai menyimpan watchlist dan portofolio simulasi yang hanya dapat diakses akun Anda.</p>
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label>Nama<input type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus></label>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required></label>
        <label>Kata sandi<input type="password" name="password" autocomplete="new-password" minlength="12" required></label>
        <label>Ulangi kata sandi<input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required></label>
        <p class="muted">Gunakan minimal 12 karakter, termasuk huruf besar, huruf kecil, dan angka. Hindari memakai kata sandi yang sama dengan layanan lain.</p>
        <button type="submit">Buat akun</button>
    </form>
    <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>.</p>
@endsection
