@extends('layouts.admin')

@section('title', 'Daftar Pesanan Operasional')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 fw-bold mb-1 font-heading">Daftar Pesanan Pre-Order</h2>
        <span class="text-muted small">Pencatatan dan pemantauan seluruh pesanan dalam lingkup operasional admin.</span>
    </div>
</div>

<!-- Search & Status Filter -->
<div class="card card-uyuy p-3 mb-4">
    <form action="{{ route('admin.orders.index') }}" method="GET">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. Pesanan, nama customer, atau WhatsApp..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua Status Pesanan</option>
                    @foreach(\App\Models\Order::$statuses as $statusKey)
                        <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>{{ $statusKey }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-mocha w-100">
                    <i class="bi bi-funnel me-1"></i> Saring Data
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card card-uyuy p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>No. Pesanan</th>
                    <th>Customer</th>
                    <th>WhatsApp</th>
                    <th>Jumlah Item</th>
                    <th>Total Tagihan</th>
                    <th>Status Pesanan</th>
                    <th>Status Bukti Transfer</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong class="text-dark">{{ $order->order_number }}</strong>
                            <div class="text-muted small">{{ $order->created_at->format('d M Y, H:i') }}</div>
                        </td>
                        <td>{{ $order->customer?->full_name ?? $order->recipient_name }}</td>
                        <td>{{ $order->recipient_phone }}</td>
                        <td>{{ $order->orderDetails->sum('quantity') }} botol</td>
                        <td class="fw-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td>
                            @if($order->payment && $order->payment->proof_file_path)
                                <span class="badge {{ $order->payment->isValid() ? 'bg-success' : ($order->payment->isRejected() ? 'bg-danger' : 'bg-primary') }}">
                                    {{ $order->payment->verification_status }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Menunggu Bukti</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary">Kelola</a>
                                @if($order->payment && $order->payment->proof_file_path && $order->payment->isPending())
                                    <a href="{{ route('admin.payments.verify', $order->payment->id) }}" class="btn btn-mocha">Verifikasi</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada data pesanan yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
