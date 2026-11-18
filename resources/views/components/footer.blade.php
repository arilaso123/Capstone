<footer class="footer-uyuy">
    <div class="container footer-inner">
        <div class="footer-grid">
            <section class="footer-brand" aria-labelledby="footer-brand-heading">
                <a class="footer-brand-mark" href="{{ route('home') }}" aria-label="Uyuy Jastip, beranda">
                    <img src="{{ asset('images/logo-emblem-light.png') }}" alt="" width="38" height="38">
                    <span id="footer-brand-heading">Uyuy Jastip</span>
                </a>
                <p>Katalog parfum dan pengelolaan pesanan pre-order dalam satu layanan.</p>
                <span class="footer-hours"><i class="bi bi-clock" aria-hidden="true"></i> Jam verifikasi Admin: Selasa–Minggu, 09.00–19.00 WIB</span>
            </section>

            <nav class="footer-links" aria-label="Menu footer">
                <h2>Jelajahi</h2>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('catalog') }}">Katalog parfum</a>
                <a href="{{ route('po.info') }}">Informasi PO</a>
                @guest
                    <a href="{{ route('login') }}">Masuk</a>
                    <a href="{{ route('register') }}">Daftar akun</a>
                @else
                    @if(auth()->user()->role === 'customer')
                        <a href="{{ route('customer.dashboard') }}">Dashboard pelanggan</a>
                    @elseif(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                    @elseif(auth()->user()->role === 'owner')
                        <a href="{{ route('owner.dashboard') }}">Dashboard Owner</a>
                    @endif
                @endguest
            </nav>

            <section class="footer-order-flow" aria-labelledby="footer-order-heading">
                <h2 id="footer-order-heading">Alur pemesanan</h2>
                <ol>
                    <li>Pilih produk dan ukuran dari katalog.</li>
                    <li>Checkout selama status dan tanggal PO mengizinkan.</li>
                    <li>Ikuti instruksi pembayaran pada detail pesanan.</li>
                    <li>Unggah bukti untuk diperiksa Admin.</li>
                </ol>
            </section>

            <section class="footer-social" aria-labelledby="footer-social-heading">
                <h2 id="footer-social-heading">Kanal komunikasi</h2>
                @php($socialLinks = config('jastip.social', []))
                @if(collect($socialLinks)->filter()->isNotEmpty())
                    <p>Kanal yang telah dikonfigurasi:</p>
                    <div>
                        @foreach(['whatsapp' => ['WhatsApp', 'bi-whatsapp'], 'instagram' => ['Instagram', 'bi-instagram'], 'tiktok' => ['TikTok', 'bi-tiktok'], 'threads' => ['Threads', 'bi-threads']] as $key => [$label, $icon])
                            @php($url = $socialLinks[$key] ?? null)
                            @if($url && filter_var($url, FILTER_VALIDATE_URL) && parse_url($url, PHP_URL_SCHEME) === 'https')
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $label }}</a>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p>Kanal komunikasi akan ditampilkan setelah Owner mengaturnya.</p>
                @endif
            </section>
        </div>

        <div class="footer-legal">
            <span>© {{ now()->year }} Uyuy Jastip</span>
            <span>Capstone Design Project · Universitas Telkom Kampus Jakarta</span>
        </div>
    </div>
</footer>
