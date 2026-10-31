<nav class="navbar navbar-expand-lg navbar-uyuy sticky-top">
    <div class="container">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <i class="bi bi-droplet-half text-mocha fs-4" style="color: var(--color-mocha);"></i>
            <span>Uyuy Jastip</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUyuyNav" aria-controls="navbarUyuyNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarUyuyNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="bi bi-house me-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalog*') ? 'active' : '' }}" href="{{ route('catalog') }}">
                        <i class="bi bi-grid me-1"></i> Katalog Parfum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('po.info') ? 'active' : '' }}" href="{{ route('po.info') }}">
                        <i class="bi bi-calendar2-week me-1"></i> Informasi PO
                    </a>
                </li>
            </ul>

            <!-- Right-Hand Side: Auth & Cart Navigation -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                @auth
                    @if(auth()->user()->role === 'customer')
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('customer.cart') }}" title="Keranjang Belanja">
                                <i class="bi bi-bag fs-5"></i>
                                <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                    {{ session('cart') ? count(session('cart')) : 0 }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-secondary rounded-pill text-capitalize">{{ auth()->user()->role }}</span>
                                <span class="fw-semibold">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item" href="{{ route('customer.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard Saya</a></li>
                                <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}"><i class="bi bi-receipt me-2"></i> Riwayat Pesanan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @elseif(auth()->user()->role === 'admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-info text-dark rounded-pill">Admin</span>
                                <span class="fw-semibold">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard Admin</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i class="bi bi-box-seam me-2"></i> Kelola Pesanan</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.payments.index') }}"><i class="bi bi-credit-card me-2"></i> Verifikasi Pembayaran</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.cancellations.index') }}"><i class="bi bi-x-octagon me-2"></i> Permohonan Batal</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @elseif(auth()->user()->role === 'owner')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="badge bg-warning text-dark rounded-pill">Owner</span>
                                <span class="fw-semibold">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item" href="{{ route('owner.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard Owner</a></li>
                                <li><a class="dropdown-item" href="{{ route('owner.po-periods.index') }}"><i class="bi bi-calendar2-range me-2"></i> Kelola Periode PO</a></li>
                                <li><a class="dropdown-item" href="{{ route('owner.products.index') }}"><i class="bi bi-boxes me-2"></i> Manajemen Produk</a></li>
                                <li><a class="dropdown-item" href="{{ route('owner.recap.index') }}"><i class="bi bi-file-earmark-bar-graph me-2"></i> Rekapitulasi PO</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="btn btn-outline-mocha btn-sm px-3" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-mocha btn-sm px-3" href="{{ route('register') }}">
                            <i class="bi bi-person-plus me-1"></i> Daftar
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

