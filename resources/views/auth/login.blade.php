<div>
    <!-- An unexamined life is not worth living. - Socrates -->
</div>
@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h1>Masuk ke akun investasi</h1>
    <p>Kelola watchlist, catatan emiten, dan portofolio simulasi privat Anda.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
        <label>Kata sandi<input type="password" name="password" autocomplete="current-password" required></label>
        <label><span><input type="checkbox" name="remember" value="1"> Ingat saya</span></label>
        <button type="submit">Masuk</button>
    </form>
    <p>Belum punya akun? <a href="{{ route('register') }}">Buat akun gratis</a>.</p>
@endsection
