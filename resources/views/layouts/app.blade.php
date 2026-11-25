<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#faf9f6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Uyuy Jastip') - Jasa Titip Parfum In Parfume</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
@php
    $isPublicRoot = request()->routeIs('home');
    $appBackFallback = match (true) {
        request()->routeIs('product.detail') => route('catalog'),
        request()->routeIs('customer.cart') => route('catalog'),
        request()->routeIs('customer.orders.payment', 'customer.orders.cancellation') && request()->route('id') => route('customer.orders.show', request()->route('id')),
        request()->routeIs('customer.orders.show') => route('customer.orders.index'),
        request()->routeIs('customer.orders.index', 'customer.profile.edit') => route('customer.dashboard'),
        default => route('home'),
    };
@endphp
<body data-nav-root="{{ $isPublicRoot ? 'true' : 'false' }}" data-nav-auth="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Main Navigation Bar -->
    @include('components.navbar')

    @unless($isPublicRoot)
        <div class="editorial-back-bar editorial-back-wrapper">
            <div class="container">
                <x-back-button :fallback="$appBackFallback" />
            </div>
        </div>
    @endunless

    <x-flash-messages wrapper-class="container mt-3" />

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    @stack('scripts')
</body>
</html>

