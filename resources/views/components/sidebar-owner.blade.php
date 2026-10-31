<div class="dashboard-sidebar">
    <!-- User Profile Header -->
    <div class="px-3 pb-3 mb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: var(--color-champagne-light); color: var(--color-mocha); width: 42px; height: 42px; font-size: 1.1rem;">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">{{ auth()->user()->name ?? 'Owner Uyuy Jastip' }}</h6>
                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Pemilik Usaha (Owner)</span>
            </div>
        </div>
        <div class="mt-2 text-muted small" style="font-size: 0.72rem;">
            <i class="bi bi-star-fill text-warning me-1"></i> Hak akses penuh: Kontrol PO, master produk, & rekapitulasi bisnis.
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-heading">Menu Manajemen Utama</div>
    <nav class="nav flex-column mb-3">
        <a class="sidebar-menu-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}" href="{{ route('owner.dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard Eksekutif</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('owner.po-periods*') ? 'active' : '' }}" href="{{ route('owner.po-periods.index') }}">
            <i class="bi bi-calendar2-range"></i>
            <span>Periode Pre-Order (PO)</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('owner.products*') ? 'active' : '' }}" href="{{ route('owner.products.index') }}">
            <i class="bi bi-boxes"></i>
            <span>Manajemen Produk</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('owner.recap*') ? 'active' : '' }}" href="{{ route('owner.recap.index') }}">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>Rekapitulasi PO</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('owner.customers*') ? 'active' : '' }}" href="{{ route('owner.customers.index') }}">
            <i class="bi bi-people"></i>
            <span>Data Pelanggan</span>
        </a>
    </nav>

    <hr class="my-3">

    <div class="px-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </button>
        </form>
    </div>
</div>

