<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code', 'Error') — @yield('heading', 'Terjadi Kendala') | Uyuy Jastip</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="navbar navbar-uyuy">
        <div class="container navbar-inner">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}">
                <img src="{{ asset('images/logo-emblem.png') }}" alt="" width="34" height="34">
                <span class="font-heading">Uyuy Jastip</span>
            </a>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('catalog') ? route('catalog') : url('/catalog') }}">Katalog Parfum</a>
                <a class="nav-link" href="{{ \Illuminate\Support\Facades\Route::has('po.info') ? route('po.info') : url('/po-info') }}">Informasi PO</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container error-page">
            <section class="error-panel" aria-labelledby="errorPageTitle">
                <p class="home-eyebrow">SYSTEM / @yield('eyebrow', 'INFORMASI HALAMAN')</p>
                <p class="error-code">@yield('code', 'Error')</p>
                <h1 id="errorPageTitle">@yield('heading', 'Halaman tidak dapat dimuat')</h1>
                <p>@yield('message', 'Terjadi kendala saat memproses permintaan Anda. Silakan kembali ke halaman utama atau ulangi beberapa saat lagi.')</p>

                <div class="error-actions">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}" class="btn btn-dark">
                        Kembali ke beranda <span aria-hidden="true">→</span>
                    </a>
                    <a href="{{ \Illuminate\Support\Facades\Route::has('catalog') ? route('catalog') : url('/catalog') }}" class="btn btn-outline-secondary">
                        Lihat katalog parfum
                    </a>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer-uyuy">
        <div class="container footer-inner">
            <div class="footer-legal border-top-0">
                <span>© {{ date('Y') }} Uyuy Jastip</span>
                <span>Layanan Jasa Titip Parfum Pre-Order</span>
            </div>
        </div>
    </footer>
</body>
</html>
