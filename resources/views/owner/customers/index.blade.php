@extends('layouts.owner')

@section('title', 'Database Pelanggan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Data Pelanggan Pre-Order</h2>
        <span class="text-muted small">Daftar pelanggan terdaftar pada layanan pre-order parfum Uyuy Jastip (~200 pelanggan).</span>
    </div>
</div>

<!-- Search Bar -->
<div class="card card-uyuy p-3 mb-4">
    <form action="{{ route('owner.customers.index') }}" method="GET">
        <div class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama pelanggan, nomor WhatsApp, email, atau kota..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-mocha w-100">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('owner.customers.index') }}" class="btn btn-outline-secondary" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="card card-uyuy p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Nama Pelanggan</th>
                    <th>Nomor WhatsApp</th>
                    <th>Email</th>
                    <th>Kota Domisili</th>
                    <th class="text-center">Total Pesanan</th>
                    <th>Terdaftar Sejak</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <strong class="text-dark">{{ $customer->full_name }}</strong>
                        </td>
                        <td>
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $customer->phone_number) }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                                <i class="bi bi-whatsapp me-1"></i> {{ $customer->phone_number }}
                            </a>
                        </td>
                        <td>{{ $customer->user?->email ?? '-' }}</td>
                        <td>{{ $customer->city ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $customer->orders_count }} PO</span>
                        </td>
                        <td>{{ $customer->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            Tidak ada data pelanggan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
