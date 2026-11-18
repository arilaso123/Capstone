@extends('layouts.owner')

@section('title', 'Edit Periode PO')

@section('content')
<section class="owner-form-page" aria-labelledby="poEditTitle">
    <nav class="owner-form-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('owner.dashboard') }}">Dashboard</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('owner.po-periods.index') }}">Periode PO</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Edit</span>
    </nav>

    <header class="owner-form-header">
        <div>
            <p class="owner-recap-eyebrow">OWNER / PERIODE PRE-ORDER</p>
            <h1 id="poEditTitle">Edit periode PO</h1>
            <p>Perbarui nama dan informasi jadwal periode.</p>
        </div>
        <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary">Kembali ke daftar</a>
    </header>

    <div class="owner-form-card">
        <form action="{{ route('owner.po-periods.update', $poPeriod->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="owner-form-field owner-form-field-wide">
                <label for="name">Nama periode <span aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $poPeriod->name) }}" maxlength="255" required class="form-control @error('name') is-invalid @enderror" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if($errors->has('name')) aria-describedby="name-error" @endif>
                @error('name')<div class="invalid-feedback" id="name-error">{{ $message }}</div>@enderror
            </div>

            <div class="owner-form-grid">
                <div class="owner-form-field">
                    <label for="start_date">Tanggal mulai <span aria-hidden="true">*</span></label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $poPeriod->start_date->format('Y-m-d')) }}" required @disabled($poPeriod->orders_count > 0) class="form-control @error('start_date') is-invalid @enderror" aria-invalid="{{ $errors->has('start_date') ? 'true' : 'false' }}" @if($errors->has('start_date')) aria-describedby="start-date-error" @endif>
                    @error('start_date')<div class="invalid-feedback" id="start-date-error">{{ $message }}</div>@enderror
                </div>
                <div class="owner-form-field">
                    <label for="end_date">Tanggal akhir <span aria-hidden="true">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $poPeriod->end_date->format('Y-m-d')) }}" required @disabled($poPeriod->orders_count > 0) class="form-control @error('end_date') is-invalid @enderror" aria-invalid="{{ $errors->has('end_date') ? 'true' : 'false' }}" @if($errors->has('end_date')) aria-describedby="end-date-error" @endif>
                    @error('end_date')<div class="invalid-feedback" id="end-date-error">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($poPeriod->orders_count > 0)
                <div class="owner-form-notice" role="note">
                    Tanggal tidak dapat diubah karena periode ini sudah memiliki {{ number_format($poPeriod->orders_count, 0, ',', '.') }} pesanan. Nama dan catatan tetap dapat diperbarui.
                </div>
                <input type="hidden" name="start_date" value="{{ $poPeriod->start_date->format('Y-m-d') }}">
                <input type="hidden" name="end_date" value="{{ $poPeriod->end_date->format('Y-m-d') }}">
            @endif

            <div class="owner-form-field owner-form-field-wide">
                <label for="notes">Catatan <span class="owner-form-optional">Opsional</span></label>
                <textarea id="notes" name="notes" rows="4" maxlength="1000" class="form-control @error('notes') is-invalid @enderror" aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}" @if($errors->has('notes')) aria-describedby="notes-error" @endif>{{ old('notes', $poPeriod->notes) }}</textarea>
                @error('notes')<div class="invalid-feedback" id="notes-error">{{ $message }}</div>@enderror
            </div>

            <div class="owner-form-actions">
                <a href="{{ route('owner.po-periods.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-dark"><i class="bi bi-check2" aria-hidden="true"></i> Simpan perubahan</button>
            </div>
        </form>
    </div>
</section>
@endsection
