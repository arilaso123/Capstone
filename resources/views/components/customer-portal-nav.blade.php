@php($cartCount = session('cart') ? count(session('cart')) : 0)

<nav class="customer-portal-nav" aria-label="Navigasi Portal Pelanggan">
    <a href="{{ route('customer.dashboard') }}" @class(['active' => request()->routeIs('customer.dashboard')]) @if(request()->routeIs('customer.dashboard')) aria-current="page" @endif>
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span>Ringkasan</span>
    </a>
    <a href="{{ route('customer.orders.index') }}" @class(['active' => request()->routeIs('customer.orders*')]) @if(request()->routeIs('customer.orders*')) aria-current="page" @endif>
        <i class="bi bi-receipt" aria-hidden="true"></i>
        <span>Pesanan saya</span>
    </a>
    <a href="{{ route('customer.cart') }}" @class(['active' => request()->routeIs('customer.cart')]) @if(request()->routeIs('customer.cart')) aria-current="page" @endif>
        <i class="bi bi-bag" aria-hidden="true"></i>
        <span>Keranjang ({{ $cartCount }})</span>
    </a>
    <a href="{{ route('catalog') }}">
        <i class="bi bi-grid" aria-hidden="true"></i>
        <span>Katalog parfum</span>
    </a>
</nav>

