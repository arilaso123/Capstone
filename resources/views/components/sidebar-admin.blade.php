<div class="dashboard-sidebar">
    <!-- User Profile Identity Card -->
    <div class="sidebar-profile-card">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon sidebar-profile-icon">
                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
            </div>
            <div class="sidebar-profile-meta">
                <h2 class="sidebar-profile-name">{{ auth()->user()->name ?? 'Admin Uyuy Jastip' }}</h2>
                <span class="sidebar-role-badge">
                    {{ auth()->user()?->isOwner() ? 'Owner' : 'Staf Operasional' }}
                </span>
            </div>
        </div>
        <div class="sidebar-profile-note">
            <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Wewenang: Verifikasi pembayaran, pembaruan status, & penanganan pembatalan.
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-nav-section">
        <div class="sidebar-heading">Menu Operasional</div>
        <nav class="nav flex-column" aria-label="Navigasi Admin">
            <a class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                <i class="bi bi-speedometer2" aria-hidden="true"></i>
                <span>Dashboard Operasional</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}" @if(request()->routeIs('admin.orders*')) aria-current="page" @endif>
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span>Daftar Pesanan</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}" @if(request()->routeIs('admin.payments*')) aria-current="page" @endif>
                <i class="bi bi-credit-card-2-front" aria-hidden="true"></i>
                <span>Pemeriksaan Pembayaran</span>
            </a>
            <a class="sidebar-menu-link {{ request()->routeIs('admin.cancellations*') ? 'active' : '' }}" href="{{ route('admin.cancellations.index') }}" @if(request()->routeIs('admin.cancellations*')) aria-current="page" @endif>
                <i class="bi bi-x-octagon" aria-hidden="true"></i>
                <span>Permohonan Pembatalan</span>
            </a>
        </nav>
    </div>

    <!-- Footer Restrictions & Logout Docked at Bottom -->
    <div class="sidebar-footer">
        <div class="sidebar-access-note">
            <strong><i class="bi bi-shield-shaded" aria-hidden="true"></i> Batasan Wewenang</strong>
            @if(auth()->user()?->isOwner())
                Pengelolaan periode PO, produk, dan rekapitulasi tersedia di Panel Owner.
            @else
                Admin tidak berwenang membuka/menutup PO, mengubah master produk, atau mengakses rekapitulasi Owner.
            @endif
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn sidebar-logout-btn">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar
            </button>
        </form>
    </div>
</div>

