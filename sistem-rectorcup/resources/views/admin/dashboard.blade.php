@extends('layouts.app')

@section('title', 'Kelola Jadwal')

@section('content')
    <div class="mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h2 class="font-weight-bold mb-1">Manajemen Pertandingan</h2>
            <p class="text-muted">Kelola bracket, jadwal, dan aktivasi pertandingan Rector Cup.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.tournament.bracket.builder') }}" 
               class="btn btn-success shadow-sm font-weight-bold px-4 py-2 mt-3 mt-md-0 mr-2">
                <i class="bi bi-magic mr-2"></i> Custom Bracket Builder
            </a>
            <button type="button" class="btn btn-outline-primary shadow-sm font-weight-bold px-4 py-2 mt-3 mt-md-0 mr-2"
                data-toggle="modal" data-target="#generateBracketModal">
                <i class="bi bi-diagram-3 mr-2"></i> Bracket Otomatis
            </button>
            <button type="button" class="btn btn-primary shadow-sm font-weight-bold px-4 py-2 mt-3 mt-md-0"
                data-toggle="modal" data-target="#addMatchModal">
                <i class="bi bi-plus-lg mr-2"></i> Tambah Pertandingan
            </button>
        </div>
    </div>

    {{-- Alert Section --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 16px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <i class="bi bi-check-circle-fill mr-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Section Tournament/Bracket --}}
    <div class="mb-5">
        <h5 class="text-white font-weight-bold mb-4"><i class="bi bi-trophy text-warning mr-2"></i> Tournament & Bracket Aktif</h5>
        <div class="row">
            @forelse($tournaments as $tournament)
                <div class="col-md-6 mb-4">
                    <div class="card border-0 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border) !important; border-radius: 24px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                                        style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1, #a855f7) !important;">
                                        <i class="bi {{ $tournament->sport->icon ?? 'bi-diagram-3' }} text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-weight-bold text-white mb-0">{{ $tournament->name }}</h6>
                                        <span class="text-muted small text-uppercase">{{ $tournament->sport->nama_sport }} • {{ $tournament->year }}</span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" data-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical h5 mb-0"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right bg-dark border-secondary shadow-lg">
                                        <a href="{{ route('admin.tournament.bracket.view', $tournament) }}" class="dropdown-item text-white small">
                                            <i class="bi bi-eye mr-2"></i> View Bracket
                                        </a>
                                        <div class="dropdown-divider border-secondary"></div>
                                        <form action="{{ route('admin.bracket.reroll', $tournament->id) }}" method="POST" onsubmit="return confirm('Reroll akan mengacak ulang semua tim di bracket. Lanjutkan?')">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning small font-weight-bold">
                                                <i class="bi bi-shuffle mr-2"></i> Reroll Bracket
                                            </button>
                                        </form>
                                        <div class="dropdown-divider border-secondary"></div>
                                        <form action="{{ route('admin.tournament.delete', $tournament->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus turnamen ini? Semua pertandingan di dalamnya juga akan dihapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger small font-weight-bold">
                                                <i class="bi bi-trash mr-2"></i> Hapus Turnamen
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mb-3" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-borderless text-white mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th>Babak</th>
                                            <th>Pertandingan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                            $tMatches = $groupedMatches->get('tournament_' . $tournament->id, collect());
                                        @endphp
                                        @foreach($tMatches as $p)
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                <td class="small align-middle text-muted">{{ $p->babak }}</td>
                                                <td class="small align-middle">
                                                    <span class="{{ $p->team_a_id ? 'text-white' : 'text-muted italic' }}">{{ $p->teamA?->name ?? 'TBD' }}</span>
                                                    <span class="text-muted mx-1">vs</span>
                                                    <span class="{{ $p->team_b_id ? 'text-white' : 'text-muted italic' }}">{{ $p->teamB?->name ?? 'TBD' }}</span>
                                                </td>
                                                <td class="align-middle">
                                                    @if($p->status == 'live')
                                                        <span class="badge badge-success small">LIVE</span>
                                                    @elseif($p->status == 'finished')
                                                        <span class="badge badge-secondary small">DONE</span>
                                                    @else
                                                        <span class="badge badge-dark small">SCHED</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted text-center py-4 bg-dark-subtle rounded-xl" style="border: 1px dashed var(--glass-border);">Belum ada tournament aktif.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Section Independent Matches --}}
    <div class="mb-4">
        <h5 class="text-white font-weight-bold mb-3"><i class="bi bi-calendar-event text-primary mr-2"></i> Pertandingan Mandiri</h5>
        <div class="card border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Sport</th>
                                <th>Waktu</th>
                                <th>Pertandingan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $iMatches = $groupedMatches->get('independent', collect()); @endphp
                            @forelse($iMatches->where('status', '!=', 'finished') as $p)
                                <tr data-match-id="{{ $p->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                                                style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.1) !important;">
                                                <i class="bi {{ $p->sport->icon ?? 'bi-trophy' }} text-primary small"></i>
                                            </div>
                                            <span class="font-weight-600">{{ $p->sport->nama_sport ?? 'Tournament' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-white small font-weight-bold">{{ $p->waktu_tanding->format('d M, H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="font-weight-600 text-uppercase small text-white">
                                            {{ $p->teamA?->name ?? 'TBD' }} <span class="text-muted mx-2">VS</span> {{ $p->teamB?->name ?? 'TBD' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($p->status == 'live')
                                            <span class="badge-live"><span class="live-dot"></span> LIVE</span>
                                        @else
                                            <span class="badge badge-dark px-3 py-1">SCHEDULED</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($p->status == 'scheduled')
                                            <button class="btn btn-sm btn-warning rounded-pill px-3 font-weight-bold" data-toggle="modal" data-target="#editMatch{{ $p->id }}">
                                                Edit
                                            </button>
                                        @else
                                            <a href="{{ route('admin.skor') }}?match={{ $p->id }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 font-weight-bold">Update Skor</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted small">Tidak ada pertandingan mandiri aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Buat Bracket --}}
<div class="modal fade" id="generateBracketModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content"
            style="background: var(--bg-dark); border: 1px solid var(--glass-border); border-radius: 24px;">
            <div class="modal-header border-0 p-4"
                style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important; border-radius: 24px 24px 0 0;">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="bi bi-diagram-3 mr-2"></i> Buat Bracket Tournament
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <form action="{{ route('admin.bracket.generate') }}" method="POST" class="mb-0">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8 mb-4">
                            <label class="small font-weight-bold text-uppercase text-muted mb-2">Nama Tournament</label>
                            <input type="text" name="tournament_name" class="form-control"
                                placeholder="Contoh: Rector Cup Futsal 2026" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="small font-weight-bold text-uppercase text-muted mb-2">Jumlah Tim</label>
                            <select name="manual_team_count" class="form-control">
                                <option value="">Otomatis (Ikuti List Tim)</option>
                                <option value="4">4 Tim</option>
                                <option value="8">8 Tim</option>
                                <option value="16">16 Tim</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="small font-weight-bold text-uppercase text-muted mb-2">Cabang Olahraga</label>
                            <select name="sport_id" class="form-control" required>
                                <option value="" disabled selected>Pilih Cabang Sport...</option>
                                @foreach($sports as $sport)
                                    <option value="{{ $sport->id }}">{{ $sport->nama_sport }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="small font-weight-bold text-uppercase text-muted mb-2">
                                <i class="bi bi-info-circle mr-1"></i> Keterangan Bracket
                            </label>
                            <input type="text" name="keterangan" class="form-control" 
                                placeholder="Contoh: Basket Putra, Basket Putri, Badminton Ganda Putra, dll.">
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-lightbulb mr-1"></i> 
                                Keterangan akan ditampilkan di bracket untuk membantu peserta memahami jenis pertandingan.
                            </small>
                        </div>
                    </div>
                    
                    <div class="p-3 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
                        <label class="small font-weight-bold text-uppercase text-muted mb-3 d-block">
                            <i class="bi bi-people mr-1"></i> Pilih Tim Peserta (Pilih Minimal 2)
                        </label>
                        
                        <div class="alert alert-info py-2 px-3 mb-3 border-0 small" style="background: rgba(59, 130, 246, 0.1); color: #60a5fa; border-radius: 12px;">
                            <i class="bi bi-info-circle mr-1"></i> <b>Tips:</b> Jika ingin membuat bracket kosong (Manual Input), Anda bisa melewati pemilihan tim ini dan langsung klik Generate.
                        </div>
                        
                        <div style="max-height: 300px; overflow-y: auto;">
                            @php
                                $groupedTeams = $teams->groupBy('prodi');
                            @endphp
                            
                            {{-- Opsi Seluruh Prodi --}}
                            @if($groupedTeams->has('Semua Prodi'))
                                <div class="mb-4">
                                    <h6 class="text-warning small font-weight-bold text-uppercase mb-3">
                                        <i class="bi bi-globe mr-1"></i> Opsi Khusus
                                    </h6>
                                    <div class="row">
                                        @foreach($groupedTeams['Semua Prodi'] as $team)
                                            <div class="col-md-12 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="team_ids[]" value="{{ $team->id }}"
                                                        class="custom-control-input" id="team_bracket_{{ $team->id }}">
                                                    <label class="custom-control-label text-white font-weight-medium"
                                                        for="team_bracket_{{ $team->id }}">
                                                        <i class="bi bi-people mr-2"></i>{{ $team->name }} 
                                                        <span class="text-muted small ml-2">(Untuk pertandingan yang semua prodi berpartisipasi, misal: PUBG Mobile)</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if(count($groupedTeams) > 1)
                                    <div class="border-top border-secondary my-3 opacity-25"></div>
                                @endif
                            @endif
                            
                            {{-- Tim per Prodi --}}
                            @foreach($groupedTeams as $prodi => $prodiTeams)
                                @if($prodi != 'Semua Prodi')
                                    <div class="mb-4">
                                        <h6 class="text-primary small font-weight-bold text-uppercase mb-3">
                                            <i class="bi bi-building mr-1"></i> {{ $prodi }}
                                        </h6>
                                        <div class="row">
                                            @foreach($prodiTeams as $team)
                                                <div class="col-md-6 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="team_ids[]" value="{{ $team->id }}"
                                                            class="custom-control-input" id="team_bracket_{{ $team->id }}">
                                                        <label class="custom-control-label text-white font-weight-medium"
                                                            for="team_bracket_{{ $team->id }}">
                                                            {{ $team->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="border-top border-secondary my-3 opacity-25"></div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-link text-muted font-weight-bold text-decoration-none" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5">Generate Bracket</button>
                </div>
            </form>
        </div>
    </div>
</div>

    {{-- Modal Tambah Pertandingan --}}
    <div class="modal fade" id="addMatchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content"
                style="background: var(--bg-dark); border: 1px solid var(--glass-border); border-radius: 24px;">
                <div class="modal-header border-0 p-4"
                    style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important; border-radius: 24px 24px 0 0;">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="bi bi-calendar-plus mr-2"></i> Input Jadwal Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="formTambahJadwal" action="{{ route('pertandingan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Cabang Olahraga (Sport)</label>
                                <select name="sport_id" id="sportSelect" class="form-control" required>
                                    <option value="" disabled selected>Pilih Cabang Sport...</option>
                                    @foreach($sports as $sport)
                                        <option value="{{ $sport->id }}" data-nama="{{ strtoupper($sport->nama_sport) }}">
                                            {{ $sport->nama_sport }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-4 team-a-container">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Tim A (Prodi)</label>
                                <select name="team_a" id="teamASelect" class="form-control" required>
                                    <option value="" disabled selected>Pilih Tim A...</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-4 team-b-container">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Tim B (Prodi)</label>
                                <select name="team_b" id="teamBSelect" class="form-control" required>
                                    <option value="" disabled selected>Pilih Tim B...</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Waktu Tanding</label>
                                <input type="datetime-local" name="waktu" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">Lokasi / GOR</label>
                                <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Lapangan Basket UKDW" required>
                            </div>
                        </div>
                        
                        {{-- Field Keterangan --}}
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="small font-weight-bold text-uppercase text-muted mb-2">
                                    <i class="bi bi-info-circle mr-1"></i> Keterangan Pertandingan
                                </label>
                                <input type="text" name="keterangan" class="form-control" 
                                    placeholder="Contoh: Basket Putra, Basket Putri, Badminton Ganda Putra, dll.">
                                <small class="text-muted mt-1 d-block">
                                    <i class="bi bi-lightbulb mr-1"></i> 
                                    Keterangan akan ditampilkan di bracket untuk membantu peserta memahami jenis pertandingan.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-link text-muted font-weight-bold text-decoration-none" data-dismiss="modal">Batal</button>
                        <button type="button" onclick="confirmSave()" class="btn btn-primary px-5">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Preview Bracket --}}
    <div class="modal fade" id="previewBracketModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content bg-dark border-secondary" style="border-radius: 24px;">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title text-white font-weight-bold">
                        <i class="bi bi-diagram-3 mr-2"></i> Preview Struktur Bracket
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4 overflow-auto">
                    <div id="bracketPreviewContent" class="d-flex justify-content-center py-4">
                        {{-- Content loaded via JS --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    /* Fix untuk memastikan bracket tetap di dalam modal */
    #previewBracketModal {
        z-index: 1050;
    }
    
    #previewBracketModal .modal-content {
        position: relative;
        z-index: 1051;
    }
    
    #bracketPreviewContent {
        position: relative;
        z-index: 1;
        overflow: auto;
    }
    
    .bracket-wrapper {
        position: relative;
        z-index: 1;
    }
    
    .bracket-column {
        position: relative;
        z-index: 1;
    }
    
    /* Prevent any absolute positioning from escaping modal */
    #previewBracketModal * {
        position: relative !important;
    }
    
    #previewBracketModal .modal-backdrop {
        z-index: 1049;
    }
    
    /* Fix untuk modal Buat Bracket - pastikan tombol tetap di dalam */
    #generateBracketModal {
        z-index: 1050;
    }
    
    #generateBracketModal .modal-content {
        position: relative;
        z-index: 1051;
        overflow: hidden;
    }
    
    #generateBracketModal .modal-footer {
        position: relative;
        z-index: 1;
        border-top: 1px solid var(--glass-border);
        margin-top: 0;
    }
    
    #generateBracketModal .modal-backdrop {
        z-index: 1049;
    }
    
    /* Prevent any elements from escaping modal */
    #generateBracketModal * {
        position: relative !important;
    }
    
    /* Fix untuk modal Edit Pertandingan - mencegah screen gelap dan cursor type */
    /* Hapus aturan z-index global untuk modal agar Bootstrap bisa mengelola stacking secara normal. */
    
    .modal {
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }
    
    body.modal-open {
        padding-right: 0 !important;
        overflow: auto !important;
    }
    
    body.modal-open .modal {
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }
    
    /* Ensure buttons are clickable */
    .modal-open .btn {
        pointer-events: auto !important;
    }
</style>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ === 'undefined') {
                console.error('jQuery not loaded');
                return;
            }
            
            $(document).ready(function () {
                const selprodiId = "{{ \App\Models\Team::where('name', 'Seluruh Prodi')->first()->id ?? '' }}";
                $('#sportSelect').on('change', function () {
                    const selectedSport = $(this).find(':selected').data('nama');
                    if (selectedSport === 'PUBG MOBILE') {
                        $('#teamASelect').val(selprodiId).trigger('change');
                        $('#teamBSelect').val(selprodiId).trigger('change');
                        $('.team-b-container').hide();
                        $('.team-a-container label').text('Format Pertandingan');
                    } else {
                        $('.team-b-container').show();
                        $('.team-a-container label').text('Tim A (Prodi)');
                    }
                });
            });
            
            // Handle WebSocket/Pusher errors gracefully
            window.addEventListener('error', function(e) {
                if (e.message && e.message.includes('WebSocket')) {
                    console.warn('WebSocket connection failed - real-time features may not work');
                    e.preventDefault();
                }
            });
            
            // Handle Pusher connection errors
            if (typeof Echo !== 'undefined') {
                Echo.connector.pusher.connection.bind('error', function(err) {
                    console.warn('Pusher connection error:', err);
                });
                
                Echo.connector.pusher.connection.bind('disconnected', function() {
                    console.warn('Pusher disconnected - real-time features paused');
                });
            }
            
            // Laravel Reverb Real-time Implementation for Admin Dashboard
            if (typeof Echo !== 'undefined') {
                console.log('Initializing Laravel Reverb for Admin Dashboard...');
                
                // Listen for score updates
                Echo.channel('scores')
                    .listen('.score.updated', function(data) {
                        console.log('Admin: Score updated via Reverb:', data);
                        
                        // Update score in table if match exists
                        updateAdminMatchScore(data);
                        
                        // Show notification
                        showAdminNotification('Skor diperbarui!', 'success');
                    })
                    .listen('.match.created', function(data) {
                        console.log('Admin: New match created via Reverb:', data);
                        
                        // Refresh page to show new match
                        showAdminNotification('Pertandingan baru dibuat! Refresh halaman untuk melihat.', 'info');
                    })
                    .listen('.match.status.updated', function(data) {
                        console.log('Admin: Match status updated via Reverb:', data);
                        
                        // Handle status finished - remove from table
                        if (data.status === 'finished') {
                            const matchRow = document.querySelector(`tr[data-match-id="${data.id}"]`);
                            if (matchRow) {
                                // Animasi fade out
                                matchRow.style.transition = 'all 0.5s ease';
                                matchRow.style.opacity = '0';
                                matchRow.style.transform = 'translateX(-100%)';
                                
                                setTimeout(() => {
                                    matchRow.remove();
                                }, 500);
                                
                                showAdminNotification(`Pertandingan ${data.data.team_a} vs ${data.data.team_b} selesai!`, 'success');
                            }
                        }
                    });
                
                console.log('Laravel Reverb initialized for Admin Dashboard');
            }
            
            // Function to update match score in admin table
            function updateAdminMatchScore(data) {
                // Find match row in table
                const matchRow = document.querySelector(`tr[data-match-id="${data.id}"]`);
                if (matchRow) {
                    // Update score badges
                    const scoreBadgeA = matchRow.querySelector('.score-a');
                    const scoreBadgeB = matchRow.querySelector('.score-b');
                    
                    if (scoreBadgeA) scoreBadgeA.textContent = data.score_a;
                    if (scoreBadgeB) scoreBadgeB.textContent = data.score_b;
                    
                    // Add animation effect
                    if (scoreBadgeA) {
                        scoreBadgeA.style.transition = 'all 0.3s ease';
                        scoreBadgeA.style.transform = 'scale(1.2)';
                        setTimeout(() => {
                            scoreBadgeA.style.transform = 'scale(1)';
                        }, 300);
                    }
                    
                    if (scoreBadgeB) {
                        scoreBadgeB.style.transition = 'all 0.3s ease';
                        scoreBadgeB.style.transform = 'scale(1.2)';
                        setTimeout(() => {
                            scoreBadgeB.style.transform = 'scale(1)';
                        }, 300);
                    }
                }
            }
            
            // Function to show admin notification
            function showAdminNotification(message, type = 'info') {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} border-0 shadow-sm mb-4 py-3`;
                alertDiv.style.cssText = 'border-radius: 16px; background: rgba(16, 185, 129, 0.1); color: #10b981; position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alertDiv.innerHTML = `<i class="bi bi-check-circle-fill mr-2"></i> ${message}`;
                
                document.body.appendChild(alertDiv);
                
                // Auto remove after 3 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            }
        });

        function previewBracket(tournamentId) {
            $('#bracketPreviewContent').html('<div class="spinner-border text-primary"></div>');
            
            // Show modal and wait for it to be fully shown
            $('#previewBracketModal').modal('show');
            
            // Wait for modal to be fully shown before rendering bracket
            $('#previewBracketModal').on('shown.bs.modal', function () {
                // Simulating a quick view since we have the data in groupedMatches
                // In real app, you might fetch via AJAX or just filter from existing DOM
                const matches = @json($groupedMatches);
                const tMatches = matches['tournament_' + tournamentId] || [];
                
                if (tMatches.length === 0) {
                    $('#bracketPreviewContent').html('<p class="text-muted text-center">Data bracket tidak ditemukan.</p>');
                    return;
                }

                let html = '<div class="bracket-wrapper d-flex align-items-start gap-4" style="position: relative; width: 100%;">';
                const rounds = [...new Set(tMatches.map(m => m.round))].sort((a,b) => a-b);
                
                rounds.forEach(round => {
                    const roundMatches = tMatches.filter(m => m.round === round);
                    html += `<div class="bracket-column d-flex flex-column justify-content-around" style="position: relative; min-width: 200px; gap: 20px;">
                        <div class="text-center mb-3 small font-weight-bold text-uppercase text-primary">${roundMatches[0].babak}</div>`;
                    
                    roundMatches.forEach(m => {
                        const teamA = m.team_a ? m.team_a.name : 'TBD';
                        const teamB = m.team_b ? m.team_b.name : 'TBD';
                        html += `
                        <div class="card bg-dark border-secondary mb-3" style="border-radius: 12px; position: relative;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-white">${teamA}</span>
                                    <span class="badge bg-primary">${m.score_a || 0}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="small text-white">${teamB}</span>
                                    <span class="badge bg-primary">${m.score_b || 0}</span>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                    html += '</div>';
                });
                html += '</div>';
                
                // Render bracket inside modal body
                $('#bracketPreviewContent').html(html);
                
                // Remove the event listener to prevent multiple calls
                $('#previewBracketModal').off('shown.bs.modal');
            });
        }

        function confirmSave() {
            Swal.fire({
                title: 'Konfirmasi Jadwal',
                text: "Apakah data pertandingan sudah sesuai dan siap dipublikasikan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Cek Kembali',
                background: '#1a1a1a',
                color: '#ffffff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formTambahJadwal').submit();
                }
            })
        }
    </script>

{{-- Modal Edit Pertandingan - Dipindahkan ke luar tabel --}}
@foreach($pertandingans as $p)
    @if($p->status == 'scheduled')
        <div class="modal fade" id="editMatch{{ $p->id }}" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content"
                    style="background: var(--bg-dark); border: 1px solid var(--glass-border); border-radius: 24px;">
                    <div class="modal-header border-0 p-4"
                        style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important; border-radius: 24px 24px 0 0;">
                        <h5 class="modal-title text-white font-weight-bold">
                            <i class="bi bi-pencil mr-2"></i> Edit Pertandingan
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('pertandingan.update', $p->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <div class="modal-body p-4">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">Cabang Olahraga</label>
                                    <select name="sport_id" class="form-control" required>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ $p->sport_id == $sport->id ? 'selected' : '' }}>
                                                {{ $sport->nama_sport }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">Tim A</label>
                                    <select name="team_a_id" class="form-control">
                                        <option value="" {{ is_null($p->team_a_id) ? 'selected' : '' }} disabled>-- TBD (To Be Determined) --</option>
                                        @foreach($teams->where('prodi', '!=', 'TBD') as $team)
                                            <option value="{{ $team->id }}" {{ $p->team_a_id == $team->id ? 'selected' : '' }}>
                                                {{ $team->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">Tim B</label>
                                    <select name="team_b_id" class="form-control">
                                        <option value="" {{ is_null($p->team_b_id) ? 'selected' : '' }} disabled>-- TBD (To Be Determined) --</option>
                                        @foreach($teams->where('prodi', '!=', 'TBD') as $team)
                                            <option value="{{ $team->id }}" {{ $p->team_b_id == $team->id ? 'selected' : '' }}>
                                                {{ $team->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">Waktu Tanding</label>
                                    <input type="datetime-local" name="waktu_tanding" class="form-control" 
                                        value="{{ $p->waktu_tanding ? \Carbon\Carbon::parse($p->waktu_tanding)->format('Y-m-d\TH:i') : '' }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">Lokasi / GOR</label>
                                    <input type="text" name="lokasi" class="form-control" 
                                        value="{{ $p->lokasi }}" placeholder="Contoh: Lapangan Basket UKDW" required>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="small font-weight-bold text-uppercase text-muted mb-2">
                                        <i class="bi bi-info-circle mr-1"></i> Keterangan Pertandingan
                                    </label>
                                    <input type="text" name="keterangan" class="form-control" 
                                        value="{{ $p->keterangan ?? '' }}" 
                                        placeholder="Contoh: Basket Putra, Basket Putri, Badminton Ganda Putra, dll.">
                                    <small class="text-muted mt-1 d-block">
                                        <i class="bi bi-lightbulb mr-1"></i> 
                                        Keterangan akan ditampilkan di bracket untuk membantu peserta memahami jenis pertandingan.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4">
                            <button type="button" class="btn btn-link text-muted font-weight-bold text-decoration-none" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-5">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
