@extends('layouts.owner')

@section('title', 'Data Pelanggan')

@section('content')
<section class="owner-customer-page" aria-labelledby="ownerCustomerTitle">
    <header class="owner-customer-header">
        <div>
            <p class="owner-recap-eyebrow">OWNER / PELANGGAN</p>
            <h1 class="owner-customer-title" id="ownerCustomerTitle">Data pelanggan</h1>
            <p class="owner-customer-description">Informasi akun, kontak, dan riwayat jumlah pesanan pelanggan.</p>
        </div>
        <p class="owner-customer-total"><strong>{{ number_format($customers->total(), 0, ',', '.') }}</strong> {{ request('search') ? 'hasil pencarian' : 'pelanggan terdaftar' }}</p>
    </header>

    <section class="owner-customer-search" aria-label="Cari pelanggan">
        <form action="{{ route('owner.customers.index') }}" method="GET" class="owner-customer-search-form">
            <label class="visually-hidden" for="customerSearch">Cari nama, nomor WhatsApp, email, atau kota</label>
            <div class="owner-customer-search-control">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="customerSearch" name="search" class="form-control" placeholder="Nama, WhatsApp, email, atau kota" value="{{ request('search') }}" maxlength="100">
            </div>
            <button type="submit" class="btn btn-dark owner-customer-search-submit">Cari pelanggan</button>
            @if(request('search'))
                <a href="{{ route('owner.customers.index') }}" class="btn btn-outline-secondary owner-customer-reset">Hapus pencarian</a>
            @endif
        </form>
        @if(request('search'))
            <p class="owner-customer-search-caption">Hasil pencarian untuk “{{ request('search') }}”.</p>
        @endif
    </section>

    <section class="owner-customer-table-section" aria-labelledby="ownerCustomerTableTitle">
        <header class="owner-customer-table-header">
            <h2 id="ownerCustomerTableTitle">Daftar pelanggan</h2>
            <span>Menampilkan {{ number_format($customers->count(), 0, ',', '.') }} dari {{ number_format($customers->total(), 0, ',', '.') }} hasil</span>
        </header>
        <div class="table-responsive owner-customer-table-wrap">
            <table class="table owner-customer-table align-middle mb-0">
                <caption class="visually-hidden">Daftar nama, kontak, kota, jumlah pesanan, dan tanggal registrasi pelanggan</caption>
                <thead>
                    <tr>
                        <th scope="col">Pelanggan</th>
                        <th scope="col">Nomor WhatsApp</th>
                        <th scope="col">Email</th>
                        <th scope="col">Kota</th>
                        <th scope="col" class="text-end">Pesanan</th>
                        <th scope="col">Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @php
                            $whatsappPhone = preg_replace('/\D+/', '', $customer->phone_number);
                            if (str_starts_with($whatsappPhone, '0')) {
                                $whatsappPhone = '62' . substr($whatsappPhone, 1);
                            }
                        @endphp
                        <tr>
                            <td class="owner-customer-name">{{ $customer->full_name }}</td>
                            <td>
                                @if($whatsappPhone)
                                    <a class="owner-customer-contact" href="https://wa.me/{{ $whatsappPhone }}" target="_blank" rel="noopener noreferrer">
                                        {{ $customer->phone_number }} <span aria-hidden="true">↗</span>
                                    </a>
                                @else
                                    <span class="owner-customer-muted">Belum diisi</span>
                                @endif
                            </td>
                            <td>{{ $customer->user?->email ?? '—' }}</td>
                            <td>{{ $customer->city ?: '—' }}</td>
                            <td class="text-end">{{ number_format($customer->orders_count, 0, ',', '.') }}</td>
                            <td>{{ $customer->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="owner-customer-empty">
                                    <i class="bi bi-people" aria-hidden="true"></i>
                                    <strong>{{ request('search') ? 'Tidak ada pelanggan yang cocok.' : 'Belum ada pelanggan terdaftar.' }}</strong>
                                    <span>{{ request('search') ? 'Coba kata kunci nama, nomor WhatsApp, email, atau kota yang lain.' : 'Data pelanggan yang mendaftar akan muncul di halaman ini.' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="owner-customer-pagination">
                {{ $customers->links() }}
            </div>
        @endif
    </section>
</section>
@endsection
