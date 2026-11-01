<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Operasional') - Uyuy Jastip</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Uyuy Jastip Theme -->
    <link rel="stylesheet" href="{{ asset('css/uyuy-theme.css') }}">

    @stack('styles')
</head>
<body class="bg-light">

    <!-- Top Navigation Bar for Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top border-bottom border-secondary">
        <div class="container-fluid px-3">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/logo-emblem-light.png') }}" alt="Uyuy Jastip Logo" style="height: 32px; width: auto; object-fit: contain;">
                <span class="font-heading fw-bold">Uyuy Jastip</span>
                <span class="badge bg-secondary ms-2 small">Admin Operasional</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminTopNav" aria-controls="adminTopNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminTopNav">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link text-white-50" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Toko
                        </a>
                    </li>
                    <li class="nav-item text-white small">
                        <i class="bi bi-person-circle me-1 text-warning"></i> {{ auth()->user()->name ?? 'Admin Staf' }}
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin App Container -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-xl-2 p-0 bg-white shadow-sm">
                @include('components.sidebar-admin')
            </div>

            <!-- Main Operational Area -->
            <div class="col-lg-9 col-xl-10 p-4 main-content">
                <!-- Notifications -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show card-uyuy border-success mb-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show card-uyuy border-danger mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show card-uyuy border-warning mb-3" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2 text-warning"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show card-uyuy border-info mb-3" role="alert">
                        <i class="bi bi-info-circle-fill me-2 text-info"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show card-uyuy border-danger mb-3" role="alert">
                        <i class="bi bi-x-circle-fill me-2 text-danger"></i> <strong>Pemeriksaan data gagal:</strong>
                        <ul class="mb-0 mt-1 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

