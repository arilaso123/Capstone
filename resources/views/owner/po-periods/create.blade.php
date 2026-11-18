@extends('layouts.owner')

@section('title', 'Buat Periode PO')

@section('content')
<section class="owner-form-page" aria-labelledby="poCreateTitle">
    <nav class="owner-form-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('owner.dashboard') }}">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('owner.po-periods.index') }}">Periode PO</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Buat periode</span>
    </nav>

    <header class="owner-form-header">
        <div>
            <p class="owner-recap-eyebrow">OWNER / PERIODE PRE-ORDER</p>
            <h1 id="poCreateTitle">Buat periode PO</h1>
            <p>Tetapkan nama periode dan rentang tanggal pemesanan.</p>
        </div>
        <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary">Kembali ke daftar</a>
    </header>

    <div class="owner-form-card">
        <form action="{{ route('owner.po-periods.store') }}" method="POST">
            @csrf
            <div class="owner-form-field owner-form-field-wide">
                <label for="name">Nama periode <span aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="255" required placeholder="Contoh: PO Parfum November 2026" class="form-control @error('name') is-invalid @enderror" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('name') ? 'nameHint nameError' : 'nameHint' }}">
                <span class="owner-form-hint" id="nameHint">Gunakan nama yang mudah dibedakan dari periode PO lainnya.</span>
                @error('name')<div class="invalid-feedback" id="nameError">{{ $message }}</div>@enderror
            </div>

            <div class="owner-form-grid">
                <div class="owner-form-field">
                    <label for="start_date">Tanggal mulai <span aria-hidden="true">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="form-control @error('start_date') is-invalid @enderror" aria-invalid="{{ $errors->has('start_date') ? 'true' : 'false' }}" @if($errors->has('start_date')) aria-describedby="start-date-error" @endif>
                    @error('start_date')<div class="invalid-feedback" id="start-date-error">{{ $message }}</div>@enderror
                </div>
                <div class="owner-form-field">
                    <label for="end_date">Tanggal akhir <span aria-hidden="true">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}" required class="form-control @error('end_date') is-invalid @enderror" aria-invalid="{{ $errors->has('end_date') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('end_date') ? 'poDateHint end-date-error' : 'poDateHint' }}">
                    <span class="owner-form-hint" id="poDateHint">Pesanan hanya dapat dibuat dalam rentang tanggal ini menurut waktu WIB.</span>
                    @error('end_date')<div class="invalid-feedback" id="end-date-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <fieldset class="owner-form-field owner-form-field-wide owner-po-status-fieldset" @if($errors->has('status')) aria-describedby="status-error" @endif>
                <legend>Status awal <span aria-hidden="true">*</span></legend>
                <p class="owner-form-hint">Status Dibuka menandai periode aktif. Checkout tetap mengikuti rentang tanggal PO WIB.</p>
                <div class="owner-po-status-options">
                    <label class="owner-po-status-option">
                        <input type="radio" name="status" value="Dibuka" required {{ old('status', 'Dibuka') === 'Dibuka' ? 'checked' : '' }} aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="status-error" @endif>
                        <span class="owner-po-status-indicator" aria-hidden="true"></span>
                        <span><strong>Dibuka</strong><small>Aktif untuk operasional periode</small></span>
                    </label>
                    <label class="owner-po-status-option">
                        <input type="radio" name="status" value="Ditutup" {{ old('status') === 'Ditutup' ? 'checked' : '' }} aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="status-error" @endif>
                        <span class="owner-po-status-indicator" aria-hidden="true"></span>
                        <span><strong>Ditutup</strong><small>Disiapkan tanpa menerima pesanan</small></span>
                    </label>
                </div>
                @error('status')<div class="owner-form-error" id="status-error">{{ $message }}</div>@enderror
            </fieldset>

            <div class="owner-form-field owner-form-field-wide">
                <label for="notes">Catatan <span class="owner-form-optional">Opsional</span></label>
                <textarea id="notes" name="notes" rows="4" maxlength="1000" class="form-control @error('notes') is-invalid @enderror" placeholder="Tambahkan catatan persiapan periode bila diperlukan." aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}" @if($errors->has('notes')) aria-describedby="notes-error" @endif>{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback" id="notes-error">{{ $message }}</div>@enderror
            </div>

            <div class="owner-form-actions">
                <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-dark"><i class="bi bi-check2" aria-hidden="true"></i> Simpan periode</button>
            </div>
        </form>
    </div>
</section>
@endsection
