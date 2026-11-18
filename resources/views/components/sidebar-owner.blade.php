<div class="dashboard-sidebar">
    <!-- User Profile Identity Card -->
    <div class="sidebar-profile-card">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon sidebar-profile-icon">
                <i class="bi bi-person-workspace" aria-hidden="true"></i>
            </div>
            <div class="sidebar-profile-meta">
                <h2 class="sidebar-profile-name">{{ auth()->user()->name ?? 'Owner Uyuy Jastip' }}</h2>
                <span class="sidebar-role-badge">Pemilik Usaha · Owner</span>
            </div>
        </div>
        <div class="sidebar-profile-note">
            <i class="bi bi-award me-1" aria-hidden="true"></i> Hak akses penuh: Kontrol PO, master produk, & rekapitulasi bisnis.
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-nav-section">
        <div class="sidebar-heading">Menu Manajemen Utama</div>
        <nav class="nav flex-column" aria-label="Navigasi Owner">
            <a class="sidebar-menu-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}" href="{{ route('owner.dashboard') }}" @if(request()->routeIs('owner.dashboard')) aria-current="page" @endif>
                <i class="bi bi-speedometer2" aria-hidden="true"></i>
                <span>Dashboard Eksekutif</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('owner.po-periods*') ? 'active' : '' }}" href="{{ route('owner.po-periods.index') }}" @if(request()->routeIs('owner.po-periods*')) aria-current="page" @endif>
                <i class="bi bi-calendar2-range" aria-hidden="true"></i>
                <span>Periode Pre-Order (PO)</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('owner.products*') ? 'active' : '' }}" href="{{ route('owner.products.index') }}" @if(request()->routeIs('owner.products*')) aria-current="page" @endif>
                <i class="bi bi-boxes" aria-hidden="true"></i>
                <span>Manajemen Produk</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('owner.recap*') ? 'active' : '' }}" href="{{ route('owner.recap.index') }}" @if(request()->routeIs('owner.recap*')) aria-current="page" @endif>
                <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
                <span>Rekapitulasi PO</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('owner.customers*') ? 'active' : '' }}" href="{{ route('owner.customers.index') }}" @if(request()->routeIs('owner.customers*')) aria-current="page" @endif>
                <i class="bi bi-people" aria-hidden="true"></i>
                <span>Data Pelanggan</span>
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn sidebar-logout-btn">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar
            </button>
        </form>
    </div>
</div>

