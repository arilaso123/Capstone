@extends('layouts.app')

@section('title', 'Informasi Pre-Order — Uyuy Jastip')

@section('content')
<div class="container po-information-page py-4">
    <nav class="product-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Informasi PO</span>
    </nav>

    <header class="po-information-header">
        <p class="home-eyebrow">JADWAL DAN PANDUAN</p>
        <h1>Informasi pre-order</h1>
        <p>Ketahui jadwal pemesanan, pembayaran, dan cara memantau pesanan di Uyuy Jastip.</p>
    </header>

    <div class="po-information-layout">
        <div class="po-information-main">
            <section class="po-period-panel" aria-labelledby="poPeriodTitle">
                <header class="po-period-panel-header">
                    <div>
                        <p class="home-eyebrow">PERIODE PO</p>
                        <h2 id="poPeriodTitle">{{ $activePo ? $activePo->name : 'Jadwal berikutnya belum tersedia' }}</h2>
                    </div>
                    <x-status-badge :status="$activePo && $activePo->isOrderableNow() ? 'Dibuka' : 'Ditutup'" />
                </header>

                @if($activePo)
                    <dl class="po-period-details">
                        <div><dt>Tanggal mulai</dt><dd>{{ $activePo->start_date->format('d F Y') }}</dd></div>
                        <div><dt>Tanggal akhir</dt><dd>{{ $activePo->end_date->format('d F Y') }} · 23.59 WIB</dd></div>
                    </dl>
                    <p class="po-period-explanation">
                        {{ $activePo->isOrderableNow() ? 'Checkout tersedia selama status dan rentang tanggal PO mengizinkan pemesanan.' : 'Checkout belum tersedia. Pemesanan hanya dapat dilakukan saat PO berstatus Dibuka dan tanggal saat ini berada dalam rentang jadwal WIB.' }}
                    </p>
                    @if($activePo->notes)
                        <div class="po-period-note"><span>Catatan periode</span>{{ $activePo->notes }}</div>
                    @endif
                @else
                    <p class="po-period-explanation">Periode baru akan ditampilkan setelah jadwal diumumkan. Katalog tetap dapat dijelajahi untuk melihat produk dan ukuran.</p>
                @endif

                <a href="{{ route('catalog') }}" class="btn {{ $activePo && $activePo->isOrderableNow() ? 'btn-dark' : 'btn-outline-secondary' }} po-period-action">
                    {{ $activePo && $activePo->isOrderableNow() ? 'Jelajahi produk' : 'Lihat katalog' }} <span aria-hidden="true">→</span>
                </a>
            </section>

            <section class="po-faq-section" aria-labelledby="poFaqTitle">
                <header>
                    <p class="home-eyebrow">PERTANYAAN UMUM</p>
                    <h2 id="poFaqTitle">Panduan singkat pemesanan</h2>
                </header>

                <div class="po-faq-list">
                    <details>
                        <summary>Bagaimana cara melakukan pembayaran?</summary>
                        <p>Ikuti nominal, batas waktu, dan rekening yang tercantum pada rincian pesanan. Setelah transfer, unggah bukti pembayaran melalui halaman pesanan agar Admin dapat memeriksanya.</p>
                    </details>
                    <details>
                        <summary>Kapan saya bisa checkout?</summary>
                        <p>Checkout tersedia saat periode berstatus Dibuka dan tanggal saat ini berada di antara tanggal mulai dan akhir yang ditetapkan, menggunakan waktu WIB.</p>
                    </details>
                    <details>
                        <summary>Bagaimana mengajukan pembatalan?</summary>
                        <p>Ajukan permohonan melalui detail pesanan sebelum barang dikirim. Admin akan meninjau alasan pembatalan. Untuk pesanan yang telah dibayar, Admin perlu mencatat refund manual sebelum menyetujui pembatalan.</p>
                    </details>
                    <details>
                        <summary>Berapa lama bukti pembayaran disimpan?</summary>
                        <p>Bukti pembayaran disimpan secara privat dengan akses terbatas. Kebijakan retensi, arsip, atau penghapusan masih menunggu keputusan Owner.</p>
                    </details>
                    <details>
                        <summary>Di mana saya dapat melihat perkembangan pesanan?</summary>
                        <p>Masuk ke akun pelanggan, lalu buka menu pesanan untuk melihat status dan rincian transaksi.</p>
                    </details>
                </div>
            </section>
        </div>

        <aside class="po-information-aside" aria-label="Jam layanan dan bantuan">
            <section class="po-aside-panel">
                <p class="home-eyebrow">LAYANAN ADMIN</p>
                <h2>Jam verifikasi</h2>
                <dl class="po-hours-list">
                    <div><dt>Selasa – Minggu</dt><dd>09.00 – 19.00 WIB</dd></div>
                    <div><dt>Senin</dt><dd>Rekapitulasi vendor</dd></div>
                </dl>
                <p class="po-aside-note">Jadwal tersebut merupakan jam layanan verifikasi pembayaran.</p>
            </section>

            <section class="po-aside-panel">
                <p class="home-eyebrow">PERLU BANTUAN?</p>
                <h2>Tanyakan kepada Admin</h2>
                <p>Hubungi kanal dukungan yang telah dikonfigurasi untuk informasi pesanan atau produk.</p>
                <div class="po-contact-links">
                    @if(config('jastip.social.whatsapp'))
                        <a href="{{ config('jastip.social.whatsapp') }}" target="_blank" rel="noopener noreferrer">WhatsApp <span aria-hidden="true">↗</span></a>
                    @endif
                    @if(config('jastip.social.instagram'))
                        <a href="{{ config('jastip.social.instagram') }}" target="_blank" rel="noopener noreferrer">Instagram <span aria-hidden="true">↗</span></a>
                    @endif
                    @unless(config('jastip.social.whatsapp') || config('jastip.social.instagram'))
                        <span class="po-aside-note">Kanal kontak Admin belum dikonfigurasi.</span>
                    @endunless
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
