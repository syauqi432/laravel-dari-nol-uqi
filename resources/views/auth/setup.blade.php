<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
</div>
@extends('layouts.auth')

@section('title', 'Siapkan akun pemilik')

@section('content')
    <h1>Siapkan akun pemilik</h1>
    <p>Aplikasi ini memakai satu akun pemilik. Pembuatan akun dari browser hanya aktif pada instalasi lokal.</p>
    @if($legacyRecords > 0)
        <div class="notice">{{ $legacyRecords }} catatan saham lama akan dipindahkan ke akun ini setelah akun berhasil dibuat.</div>
    @endif
    <form method="POST" action="{{ route('owner.setup.store') }}">
        @csrf
        <label>Nama<input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required></label>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></label>
        <label>Kata sandi<input type="password" name="password" autocomplete="new-password" minlength="12" required></label>
        <label>Ulangi kata sandi<input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required></label>
        <p>Kata sandi minimal 12 karakter, dengan huruf besar, huruf kecil, dan angka.</p>
        <button type="submit">Buat akun pemilik</button>
    </form>
@endsection
