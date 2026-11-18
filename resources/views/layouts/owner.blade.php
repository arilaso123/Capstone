<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Owner Dashboard') - Uyuy Jastip</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Uyuy Jastip Theme -->
    @stack('styles')
</head>
<body class="role-owner">

    <!-- Top Navigation Bar for Owner -->
    <nav class="navbar navbar-expand-lg navbar-dark role-topbar sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('owner.dashboard') }}">
                <img src="{{ asset('images/logo-emblem-light.png') }}" alt="" width="32" height="32" class="role-brand-mark">
                <span class="font-heading">Uyuy Jastip</span>
                <span class="role-topbar-badge ms-1">Owner Panel</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ownerTopNav" aria-controls="ownerTopNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="ownerTopNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="bi bi-clipboard-check" aria-hidden="true"></i> Operasional
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Kunjungi Toko
                        </a>
                    </li>
                    <li class="nav-item role-topbar-user">
                        <i class="bi bi-person-fill-gear" aria-hidden="true"></i> {{ auth()->user()->name ?? 'Owner Uyuy Jastip' }}
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Owner App Container -->
    <div class="container-fluid role-shell">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-xl-2 p-0 role-sidebar">
                @include('components.sidebar-owner')
            </div>

            <!-- Main Management Area -->
            <main class="col-lg-9 col-xl-10 p-4 main-content role-main">
                <x-flash-messages validation-title="Pemeriksaan data gagal:" />

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

