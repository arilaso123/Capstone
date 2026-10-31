<div class="dashboard-sidebar">
    <!-- User Profile Header -->
    <div class="px-3 pb-3 mb-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon" style="background: var(--color-champagne-light); color: var(--color-mocha); width: 42px; height: 42px; font-size: 1.1rem;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold">{{ auth()->user()->name ?? 'Admin Uyuy Jastip' }}</h6>
                <span class="badge bg-info text-dark" style="font-size: 0.7rem;">Staf Operasional</span>
            </div>
        </div>
        <div class="mt-2 text-muted small" style="font-size: 0.72rem;">
            <i class="bi bi-info-circle me-1"></i> Wewenang: Verifikasi pembayaran, pembaruan status, & penanganan pembatalan.
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-heading">Menu Operasional</div>
    <nav class="nav flex-column mb-3">
        <a class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard Operasional</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
            <i class="bi bi-box-seam"></i>
            <span>Daftar Pesanan</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">
            <i class="bi bi-credit-card-2-front"></i>
            <span>Pemeriksaan Pembayaran</span>
        </a>
        <a class="sidebar-menu-link {{ request()->routeIs('admin.cancellations*') ? 'active' : '' }}" href="{{ route('admin.cancellations.index') }}">
            <i class="bi bi-x-octagon"></i>
            <span>Permohonan Pembatalan</span>
        </a>
    </nav>

    <!-- Operational Restrictions Notice (According to Proposal) -->
    <div class="p-3 mx-2 rounded-3 bg-light border text-muted small" style="font-size: 0.72rem;">
        <strong class="d-block text-secondary mb-1"><i class="bi bi-shield-shaded me-1"></i> Batasan Wewenang:</strong>
        Admin tidak berwenang membuka/menutup PO, mengubah master produk, atau mengakses rekapitulasi Owner.
    </div>

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

