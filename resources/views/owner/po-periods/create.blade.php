@extends('layouts.owner')

@section('title', 'Buat Periode Pre-Order Baru')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('owner.po-periods.index') }}">Periode PO</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold mb-0 font-heading">Tambah Periode Pre-Order (PO) Baru</h2>
    </div>
    <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-uyuy p-4">
            <form action="{{ route('owner.po-periods.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Nama Periode Pre-Order <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: PO Batch 11 - Pertengahan November 2026">
                    @error('name')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label small fw-semibold">Tanggal Mulai Dibuka <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="end_date" class="form-label small fw-semibold">Tanggal Berakhir (Tutup) <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                        <span class="text-muted small" style="font-size: 0.72rem;">Satu siklus PO berlangsung sekitar 1 minggu (7 hari).</span>
                        @error('end_date')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Status Awal Periode <span class="text-danger">*</span></label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="statusOpen" value="Dibuka" checked>
                            <label class="form-check-label fw-bold text-success" for="statusOpen">
                                <i class="bi bi-unlock-fill me-1"></i> Dibuka Langsung
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="statusClosed" value="Ditutup">
                            <label class="form-check-label text-muted" for="statusClosed">
                                <i class="bi bi-lock-fill me-1"></i> Ditutup (Draft Persiapan)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label small fw-semibold">Catatan Khusus Periode (Opsional)</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Contoh: Estimasi belanja ke Bandung tanggal 5 November, pengiriman mulai 7 November...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-mocha px-4">
                        <i class="bi bi-check-circle me-1"></i> Simpan Periode PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

