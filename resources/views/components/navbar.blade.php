<nav class="navbar navbar-expand-lg navbar-uyuy sticky-top">
    <div class="container navbar-inner">
        <!-- Brand / Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('images/logo-emblem.png') }}" alt="" width="32" height="32">
            <span class="fw-bold font-heading">Uyuy Jastip</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUyuyNav" aria-controls="navbarUyuyNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarUyuyNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalog*') ? 'active' : '' }}" href="{{ route('catalog') }}" @if(request()->routeIs('catalog*')) aria-current="page" @endif>
                        Katalog Parfum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('po.info') ? 'active' : '' }}" href="{{ route('po.info') }}" @if(request()->routeIs('po.info')) aria-current="page" @endif>
                        Informasi PO
                    </a>
                </li>
            </ul>

            <!-- Right-Hand Side: Auth & Cart Navigation -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                @auth
                    @if(auth()->user()->role === 'customer')
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('customer.cart') }}" title="Keranjang Belanja" aria-label="Keranjang Belanja, {{ session('cart') ? count(session('cart')) : 0 }} produk">
                                <i class="bi bi-bag fs-5"></i>
                                <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill cart-count-badge">
                                    {{ session('cart') ? count(session('cart')) : 0 }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <button type="button" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="nav-role-badge">{{ auth()->user()->role }}</span>
                                <span>{{ auth()->user()->name }}</span>
                            </button>
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
                            <button type="button" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="nav-role-badge">Admin</span>
                                <span>{{ auth()->user()->name }}</span>
                            </button>
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
                            <button type="button" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="nav-role-badge">Owner</span>
                                <span>{{ auth()->user()->name }}</span>
                            </button>
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
                        <a class="btn btn-outline-mocha nav-action-btn" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-mocha nav-action-btn" href="{{ route('register') }}">
                            <i class="bi bi-person-plus" aria-hidden="true"></i> Daftar
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
