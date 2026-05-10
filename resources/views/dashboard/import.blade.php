@extends('layouts.app')
@section('title', 'Import Data Penjualan')

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(!isset($previewData))
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="display-6 text-primary mb-3">
                        <i class="bi bi-upload"></i>
                    </div>
                    <h4 class="fw-bold">Import Data Penjualan</h4>
                    <p class="text-muted">Upload file Excel (.xlsx, .xls, .csv) untuk mengimport data penjualan. Data akan dibersihkan secara otomatis sebelum disimpan.</p>
                </div>

                <form action="{{ route('import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="form-label fw-semibold">Pilih File Excel</label>
                        <input type="file" class="form-control form-control-lg @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: .xlsx, .xls, .csv (Maks: 5MB)</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Proses Data Cleaning:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Baris dengan Produk/Kategori NULL akan dihapus</li>
                            <li>Jumlah NULL akan diisi 1, Harga NULL akan diisi 0</li>
                            <li>Total akan dihitung ulang (Jumlah x Harga)</li>
                            <li>Tanggal invalid akan diperbaiki secara otomatis</li>
                            <li>Nama produk/kategori akan distandarisasi (capitalize)</li>
                        </ul>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-eye me-2"></i> Preview Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-check2-circle text-success me-2"></i> Hasil Data Cleaning</h5>
                        <p class="text-muted mb-0">
                            {{ $originalCount }} data asli →
                            <strong class="text-success">{{ $cleanedCount }} data bersih</strong>
                            ({{ $originalCount - $cleanedCount }} data dihapus karena invalid)
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('import.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Upload Ulang
                        </a>
                        <form action="{{ route('import.confirm') }}" method="POST" onsubmit="return confirm('Yakin ingin mengimport {{ $cleanedCount }} data ini? Data yang ada akan ditimpa.')">
                            @csrf
                            <input type="hidden" name="temp_file" value="{{ $tempFile }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Konfirmasi & Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-table me-2"></i> Preview Data ({{ count($previewData) }} dari {{ $cleanedCount }} baris)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewData as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['tanggal'] }}</td>
                                <td class="fw-semibold">{{ $row['produk'] }}</td>
                                <td>{{ $row['kategori'] }}</td>
                                <td>{{ $row['jumlah'] }}</td>
                                <td>Rp {{ number_format($row['harga'], 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card chart-card shadow-sm">
            <div class="card-header">
                <i class="bi bi-clipboard-data me-2"></i> Cleaning Log ({{ count($cleaningLog) }} perubahan)
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    @forelse($cleaningLog as $log)
                    <div class="list-group-item py-2 px-3">
                        <small class="text-muted">{{ $log }}</small>
                    </div>
                    @empty
                    <div class="list-group-item py-3 text-center text-muted">
                        <i class="bi bi-check-circle text-success me-1"></i> Tidak ada perubahan — data sudah bersih
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if(isset($previewData))
<script>
    document.querySelectorAll('.table-responsive').forEach(el => {
        el.style.maxHeight = '500px';
        el.style.overflowY = 'auto';
    });
</script>
@endif
@endsection
