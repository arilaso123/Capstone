<footer class="footer-uyuy">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & About -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ asset('images/logo-emblem-light.png') }}" alt="Uyuy Jastip Logo" style="height: 38px; width: auto; object-fit: contain;">
                    <h5 class="mb-0 text-white font-heading">Uyuy Jastip</h5>
                </div>
                <p class="small text-white-50">
                    Layanan jasa titip (jastip) parfum pilihan resmi dari <em>In Parfume</em>. Menghadirkan wewangian premium berkualitas, praktis, dan terpercaya dengan sistem pengelolaan pre-order yang terstruktur.
                </p>
                <div class="d-flex gap-2 mt-3">
                    <span class="badge bg-dark border border-secondary text-white-50 small">
                        <i class="bi bi-clock me-1 text-warning"></i> Sel - Min: 09.00 - 19.00 WIB
                    </span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
                <h5>Menu Cepat</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('home') }}"><i class="bi bi-chevron-right me-1 small"></i> Beranda</a></li>
                    <li class="mb-2"><a href="{{ route('catalog') }}"><i class="bi bi-chevron-right me-1 small"></i> Katalog Parfum</a></li>
                    <li class="mb-2"><a href="{{ route('po.info') }}"><i class="bi bi-chevron-right me-1 small"></i> Informasi PO</a></li>
                    <li class="mb-2"><a href="{{ route('login') }}"><i class="bi bi-chevron-right me-1 small"></i> Masuk Akun</a></li>
                    <li class="mb-2"><a href="{{ route('register') }}"><i class="bi bi-chevron-right me-1 small"></i> Registrasi</a></li>
                </ul>
            </div>

            <!-- Pre-Order Flow -->
            <div class="col-lg-3 col-md-6 col-6">
                <h5>Alur Layanan PO</h5>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><i class="bi bi-1-circle text-warning me-2"></i> Pilih varian parfum saat PO Dibuka</li>
                    <li class="mb-2"><i class="bi bi-2-circle text-warning me-2"></i> Checkout pesanan via website</li>
                    <li class="mb-2"><i class="bi bi-3-circle text-warning me-2"></i> Bayar melalui BCA Merchant</li>
                    <li class="mb-2"><i class="bi bi-4-circle text-warning me-2"></i> Unggah bukti transfer</li>
                    <li class="mb-2"><i class="bi bi-5-circle text-warning me-2"></i> Verifikasi admin & pengiriman</li>
                </ul>
            </div>

            <!-- Social Media Channels (According to Proposal) -->
            <div class="col-lg-3 col-md-6">
                <h5>Kanal Komunikasi</h5>
                <p class="small text-white-50 mb-3">Ikuti pembaruan aroma terbaru dan interaksi melalui media sosial resmi kami:</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="https://instagram.com" target="_blank" class="btn btn-sm btn-outline-light d-flex align-items-center gap-2">
                        <i class="bi bi-instagram"></i> Instagram
                    </a>
                    <a href="https://wa.me" target="_blank" class="btn btn-sm btn-outline-success d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                    <a href="https://tiktok.com" target="_blank" class="btn btn-sm btn-outline-light d-flex align-items-center gap-2">
                        <i class="bi bi-tiktok"></i> TikTok
                    </a>
                    <a href="https://threads.net" target="_blank" class="btn btn-sm btn-outline-light d-flex align-items-center gap-2">
                        <i class="bi bi-threads"></i> Threads
                    </a>
                </div>
            </div>
        </div>

        <hr class="my-4 border-secondary opacity-25">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <small class="text-white-50">
                    &copy; 2026 <strong>Uyuy Jastip</strong>. Seluruh hak cipta dilindungi.
                </small>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <small class="text-white-50">
                    Capstone Design Project &bull; Universitas Telkom Kampus Jakarta
                </small>
            </div>
        </div>
    </div>
</footer>

