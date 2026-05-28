@extends('layouts.app')

@section('title', 'Diagnostik Upload & Google Drive')

@section('content')
    <div class="mb-5">
        <div class="d-flex align-items-center mb-1">
            <a href="{{ route('admin.skor') }}" class="btn btn-dark btn-sm rounded-circle mr-3" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-chevron-left"></i>
            </a>
            <h2 class="font-weight-bold mb-0">Diagnostik Upload & Google Drive</h2>
        </div>
        <p class="text-muted ml-5">Gunakan halaman ini untuk mendiagnosis secara rinci mengapa foto/screenshot tidak dapat diunggah.</p>
    </div>

    <div class="row">
        <!-- Form Uji Coba -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; background: rgba(30, 41, 59, 0.3); border: 1px solid var(--glass-border);">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h5 class="text-white font-weight-bold mb-0"><i class="bi bi-play-circle text-primary mr-2"></i> Jalankan Tes Upload</h5>
                    <p class="text-muted small mt-1">Pilih berkas gambar atau dokumen apa saja untuk memulai pengujian.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.test-upload.post') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted text-uppercase mb-2">Pilih File Tes</label>
                            <div class="custom-file">
                                <input type="file" name="test_file" class="custom-file-input" id="test_file" required>
                                <label class="custom-file-label" for="test_file" style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--glass-border); color: var(--text-muted); border-radius: 8px;">Pilih berkas...</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block font-weight-bold py-3" style="border-radius: 12px; background: linear-gradient(135deg, #6366f1, #818cf8); border: none;">
                            <i class="bi bi-lightning-charge-fill mr-2"></i> Mulai Tes Diagnostik
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Panduan Cepat status config -->
            <div class="card border-0 mt-4 shadow-sm" style="border-radius: 20px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                <div class="card-body p-4">
                    <h6 class="text-white font-weight-bold mb-3"><i class="bi bi-info-circle text-info mr-2"></i> Status Konfigurasi Saat Ini:</h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Client Email:</span>
                            <span class="font-weight-bold {{ config('services.google_drive.client_email') ? 'text-success' : 'text-danger' }}">
                                {{ config('services.google_drive.client_email') ? 'Terisi (' . substr(config('services.google_drive.client_email'), 0, 15) . '...)' : 'KOSONG' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Private Key:</span>
                            <span class="font-weight-bold {{ config('services.google_drive.private_key') ? 'text-success' : 'text-danger' }}">
                                {{ config('services.google_drive.private_key') ? 'Terisi' : 'KOSONG' }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Parent Folder ID:</span>
                            <span class="font-weight-bold {{ config('services.google_drive.parent_folder_id') ? 'text-success' : 'text-warning' }}">
                                {{ config('services.google_drive.parent_folder_id') ? 'Terisi (' . substr(config('services.google_drive.parent_folder_id'), 0, 8) . '...)' : 'KOSONG (Hanya Simpan Lokal)' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Output Log Konsol -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 20px; background: #0b0f19; border: 1px solid #1e293b;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white font-weight-bold mb-0"><i class="bi bi-terminal text-success mr-2"></i> Konsol Diagnostik</h5>
                    <span class="badge badge-success px-3 py-1 font-weight-bold">Live Status</span>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div class="flex-grow-1 p-3 rounded-lg overflow-auto text-monospace small" 
                         style="background: #040711; border: 1px solid rgba(255,255,255,0.05); color: #a6adbb; min-height: 350px; max-height: 500px; line-height: 1.6;">
                        
                        @if(session('diagnostic_log'))
                            {!! session('diagnostic_log') !!}
                        @else
                            <div class="text-muted text-center py-5">
                                <i class="bi bi-activity h1 d-block mb-3" style="opacity: 0.3;"></i>
                                Menunggu tes dijalankan...<br>Silakan pilih file di sebelah kiri dan klik tombol untuk memulai.
                            </div>
                        @endif
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update custom-file-label ketika user memilih file
            const fileInput = document.getElementById('test_file');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const fileName = e.target.value.split('\\').pop();
                    const label = e.target.nextElementSibling;
                    if (label) {
                        label.textContent = fileName || 'Pilih berkas...';
                    }
                });
            }
        });
    </script>
@endsection
