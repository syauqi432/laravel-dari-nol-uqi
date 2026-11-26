<nav class="bottom-nav" aria-label="Navigasi utama">
    <a href="{{ route('market.overview') }}" class="{{ $active === 'market' ? 'active' : '' }}" @if($active === 'market') aria-current="page" @endif>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg></span>
        Beranda
    </a>
    <a href="{{ route('watchlist.index') }}" class="{{ $active === 'watchlist' ? 'active' : '' }}" @if($active === 'watchlist') aria-current="page" @endif>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9z"/></svg></span>
        Watchlist
    </a>
    <a href="{{ route('transactions.index') }}" class="{{ $active === 'transactions' ? 'active' : '' }}" @if($active === 'transactions') aria-current="page" @endif>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 7h13m0 0-4-4m4 4-4 4M17 17H4m0 0 4 4m-4-4 4-4"/></svg></span>
        Transaksi
    </a>
    <a href="{{ route('saham.index') }}" class="{{ $active === 'portfolio' ? 'active' : '' }}" @if($active === 'portfolio') aria-current="page" @endif>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6"/></svg></span>
        Portofolio
    </a>
    <a href="{{ route('profile.index') }}" class="{{ $active === 'profile' ? 'active' : '' }}" @if($active === 'profile') aria-current="page" @endif>
        <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg></span>
        Profil
    </a>
</nav>
