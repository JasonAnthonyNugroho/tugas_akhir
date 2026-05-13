@extends('layouts.app')

@section('title', 'Kelola Jadwal')

@section('content')
    <div class="admin-header mb-5">
        <div class="admin-header-title">
            <h2 class="font-weight-bold mb-1">Manajemen Pertandingan</h2>
            <p class="text-muted mb-0">Kelola bracket, jadwal, dan aktivasi pertandingan Rector Cup.</p>
        </div>

        <div class="admin-header-actions">
            {{-- Primary CTA: Generate Bracket --}}
            <a href="{{ route('admin.tournament.bracket.builder') }}"
               class="btn btn-primary-action">
                <i class="bi bi-diagram-3 mr-2"></i>
                <span>Generate Bracket</span>
            </a>

            {{-- Secondary actions --}}
            <div class="admin-header-secondary">
                <button type="button" class="btn btn-secondary-action"
                        data-toggle="modal" data-target="#addMatchModal"
                        title="Tambah satu pertandingan independen (di luar bracket)">
                    <i class="bi bi-plus-lg mr-2"></i>
                    <span>Tambah Pertandingan</span>
                </button>
            </div>

        </div>
    </div>

    {{-- Alert Section --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 py-3" style="border-radius: 16px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <i class="bi bi-check-circle-fill mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert border-0 shadow-sm mb-4 py-3" style="border-radius: 16px; background: rgba(239, 68, 68, 0.1); color: #f87171;">
            <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ session('error') }}
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
                                        @php
                                            $tLokasi = $tournament->pertandingans->whereNotIn('lokasi', ['TBA', ''])->pluck('lokasi')->unique()->first();
                                        @endphp
                                        @if($tLokasi)
                                            <br><span class="text-muted small"><i class="bi bi-geo-alt mr-1"></i>{{ $tLokasi }}</span>
                                        @endif
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
                                        <button type="button" class="dropdown-item text-white small"
                                                data-toggle="modal" data-target="#editTournament{{ $tournament->id }}">
                                            <i class="bi bi-pencil mr-2"></i> Edit Tournament
                                        </button>
                                        <div class="dropdown-divider border-secondary"></div>
                                        <form action="{{ route('admin.bracket.reroll', $tournament->id) }}" method="POST" onsubmit="return confirm('Reroll akan mengacak ulang semua tim di bracket. Lanjutkan?')">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning small font-weight-bold">
                                                <i class="bi bi-shuffle mr-2"></i> Reroll Bracket
                                            </button>
                                        </form>
                                        <div class="dropdown-divider border-secondary"></div>
                                        <form action="{{ route('admin.tournament.delete', $tournament) }}" method="POST" onsubmit="return confirm('Hapus tournament ini beserta semua pertandingannya?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger small font-weight-bold">
                                                <i class="bi bi-trash mr-2"></i> Hapus Tournament
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

    {{-- Modal Edit Tournament --}}
    @foreach($tournaments as $tournament)
        @php
            $tLokasiEdit = $tournament->pertandingans->whereNotIn('lokasi', ['TBA', ''])->pluck('lokasi')->unique()->first() ?? '';
        @endphp
        <div class="modal fade dash-modal" id="editTournament{{ $tournament->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content dash-modal-content">
                    <div class="dash-modal-header">
                        <div class="dash-modal-icon"><i class="bi bi-pencil-square"></i></div>
                        <div class="flex-grow-1">
                            <h5 class="dash-modal-title">Edit Tournament</h5>
                            <p class="dash-modal-subtitle">{{ $tournament->name }}</p>
                        </div>
                        <button type="button" class="dash-modal-close" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.tournament.update', $tournament) }}" method="POST" class="mb-0">
                        @csrf @method('PATCH')
                        <div class="modal-body dash-modal-body">
                            <div class="dash-form-section">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="dash-label"><i class="bi bi-trophy mr-1"></i> Nama Tournament</label>
                                        <input type="text" name="name" class="dash-input" value="{{ $tournament->name }}" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="dash-label"><i class="bi bi-geo-alt mr-1"></i> Lokasi / GOR</label>
                                        <input type="text" name="lokasi" class="dash-input" value="{{ $tLokasiEdit }}" placeholder="Contoh: GOR UKDW">
                                        <small class="dash-hint">
                                            <i class="bi bi-info-circle mr-1"></i>
                                            Mengubah lokasi akan update semua pertandingan di turnamen ini.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="dash-label"><i class="bi bi-calendar-event mr-1"></i> Tanggal Mulai</label>
                                        <input type="date" name="start_date" class="dash-input"
                                               value="{{ $tournament->start_date ? \Carbon\Carbon::parse($tournament->start_date)->format('Y-m-d') : '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="dash-label"><i class="bi bi-calendar-check mr-1"></i> Tanggal Selesai</label>
                                        <input type="date" name="end_date" class="dash-input"
                                               value="{{ $tournament->end_date ? \Carbon\Carbon::parse($tournament->end_date)->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dash-modal-footer">
                            <button type="button" class="btn-modal-cancel" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn-modal-primary">
                                <i class="bi bi-check2-circle mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

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




    {{-- Modal Tambah Pertandingan --}}
    <div class="modal fade dash-modal" id="addMatchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content dash-modal-content">
                {{-- Header --}}
                <div class="dash-modal-header dash-modal-header-green">
                    <div class="dash-modal-icon dash-modal-icon-green"><i class="bi bi-calendar-plus"></i></div>
                    <div class="flex-grow-1">
                        <h5 class="dash-modal-title">Tambah Pertandingan</h5>
                        <p class="dash-modal-subtitle">Buat 1 jadwal pertandingan independen (di luar bracket)</p>
                    </div>
                    <button type="button" class="dash-modal-close" data-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form id="formTambahJadwal" action="{{ route('pertandingan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body dash-modal-body">

                        {{-- Section: Cabang & Tim --}}
                        <div class="dash-form-section">
                            <div class="dash-section-title">
                                <i class="bi bi-trophy"></i>
                                <span>Cabang & Tim</span>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="dash-label">Cabang Olahraga <span class="text-danger">*</span></label>
                                    <select name="sport_id" id="sportSelect" class="dash-input" required>
                                        <option value="" disabled selected>Pilih cabang olahraga...</option>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" data-nama="{{ strtoupper($sport->nama_sport) }}">
                                                {{ $sport->nama_sport }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 team-a-container">
                                    <label class="dash-label">
                                        <span class="dash-team-badge dash-team-badge-a">A</span>
                                        Tim A <span class="text-danger">*</span>
                                    </label>
                                    <select name="team_a" id="teamASelect" class="dash-input" required>
                                        <option value="" disabled selected>Pilih Tim A...</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3 team-b-container">
                                    <label class="dash-label">
                                        <span class="dash-team-badge dash-team-badge-b">B</span>
                                        Tim B <span class="text-danger">*</span>
                                    </label>
                                    <select name="team_b" id="teamBSelect" class="dash-input" required>
                                        <option value="" disabled selected>Pilih Tim B...</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Jadwal --}}
                        <div class="dash-form-section">
                            <div class="dash-section-title">
                                <i class="bi bi-clock-history"></i>
                                <span>Jadwal & Lokasi</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="dash-label"><i class="bi bi-calendar-event mr-1"></i> Waktu Tanding <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="waktu" class="dash-input" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="dash-label"><i class="bi bi-geo-alt mr-1"></i> Lokasi / GOR <span class="text-danger">*</span></label>
                                    <input type="text" name="lokasi" class="dash-input" placeholder="Contoh: GOR UKDW" required>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Format & Catatan --}}
                        <div class="dash-form-section">
                            <div class="dash-section-title">
                                <i class="bi bi-card-text"></i>
                                <span>Format & Catatan</span>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="dash-label"><i class="bi bi-controller mr-1"></i> Format Pertandingan <span class="text-danger">*</span></label>
                                    <div class="d-flex" style="gap: 12px;">
                                        <label class="format-pill flex-fill">
                                            <input type="radio" name="format_tanding" value="BO1" checked>
                                            <span><strong>BO1</strong><small>Single match / Best of 1</small></span>
                                        </label>
                                        <label class="format-pill flex-fill">
                                            <input type="radio" name="format_tanding" value="BO3">
                                            <span><strong>BO3</strong><small>Best of 3 (mis. MLBB)</small></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="dash-label">Keterangan Pertandingan</label>
                                    <input type="text" name="keterangan" class="dash-input"
                                           placeholder="Contoh: Basket Putra, Badminton Ganda Putra">
                                    <small class="dash-hint">
                                        <i class="bi bi-lightbulb mr-1"></i>
                                        Akan tampil di kartu match untuk konteks tambahan.
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="dash-modal-footer">
                        <button type="button" class="btn-modal-cancel" data-dismiss="modal">Batal</button>
                        <button type="button" onclick="confirmSave()" class="btn-modal-primary btn-modal-primary-green">
                            <i class="bi bi-check2-circle mr-2"></i>Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
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

    /* ─────────── Admin Header (Manajemen Pertandingan) ─────────── */
    .admin-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 22px;
        background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(168,85,247,0.04));
        border: 1px solid rgba(99,102,241,0.15);
        border-radius: 18px;
    }
    .admin-header-title h2 {
        font-size: 1.5rem;
        background: linear-gradient(90deg, #fff, #c7d2fe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .admin-header-title p { font-size: 0.85rem; }

    .admin-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .admin-header-secondary {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-left: 12px;
        border-left: 1px solid rgba(255,255,255,0.1);
    }
    .admin-header-danger {
        padding-left: 8px;
        margin-left: 4px;
        border-left: 1px solid rgba(255,255,255,0.1);
    }

    /* Primary action button — paling menonjol (gradient ungu) */
    .btn-primary-action {
        display: inline-flex;
        align-items: center;
        padding: 10px 22px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(99,102,241,0.35);
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-primary-action:hover {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(99,102,241,0.5);
        text-decoration: none;
    }
    .btn-primary-action:focus, .btn-primary-action:active {
        color: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.4);
    }

    /* Secondary action — outline subtle */
    .btn-secondary-action {
        display: inline-flex;
        align-items: center;
        padding: 9px 16px;
        background: rgba(255,255,255,0.04);
        color: #cbd5e1;
        font-weight: 500;
        font-size: 0.85rem;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .btn-secondary-action:hover {
        background: rgba(99,102,241,0.12);
        border-color: rgba(99,102,241,0.4);
        color: #fff;
    }
    .btn-secondary-action:focus, .btn-secondary-action:active {
        color: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.25);
    }

    /* Kebab button (3-dots) untuk danger zone */
    .btn-kebab {
        width: 38px; height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: rgba(255,255,255,0.04);
        color: #94a3b8;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        font-size: 1.1rem;
        transition: all 0.2s ease;
    }
    .btn-kebab:hover {
        background: rgba(239,68,68,0.12);
        border-color: rgba(239,68,68,0.4);
        color: #fca5a5;
    }
    .btn-kebab:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }

    /* Dropdown menu di kebab — dark theme */
    .admin-header-menu {
        background: #1a1f2e;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 8px;
        min-width: 280px;
        margin-top: 6px;
    }
    .admin-header-menu .dropdown-header {
        padding: 6px 10px;
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        color: #ef4444 !important;
    }
    .admin-header-menu .dropdown-item {
        padding: 10px 12px;
        border-radius: 8px;
        white-space: normal;
        line-height: 1.3;
    }
    .admin-header-menu .dropdown-item:hover {
        background: rgba(239,68,68,0.1);
        color: #fca5a5 !important;
    }
    .admin-header-menu .dropdown-item small { line-height: 1.3; }
    .admin-header-menu form { margin: 0; }

    /* Mobile: stack vertikal, reset borders */
    @media (max-width: 767px) {
        .admin-header { padding: 16px; }
        .admin-header-actions {
            width: 100%;
            justify-content: flex-start;
            gap: 8px;
        }
        .admin-header-secondary,
        .admin-header-danger {
            border-left: none;
            padding-left: 0;
            margin-left: 0;
        }
        .btn-primary-action { width: 100%; justify-content: center; }
        .btn-secondary-action { flex: 1; justify-content: center; }
    }

    /* ─────────── Dash Modals (Bracket Otomatis & Tambah Pertandingan) ─────────── */
    .dash-modal .modal-content.dash-modal-content {
        background: #0f172a;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }

    /* Header dengan icon avatar */
    .dash-modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 22px;
        background: linear-gradient(135deg, rgba(99,102,241,0.18), rgba(168,85,247,0.12));
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .dash-modal-header-green {
        background: linear-gradient(135deg, rgba(16,185,129,0.16), rgba(59,130,246,0.1));
    }
    .dash-modal-icon {
        width: 44px; height: 44px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border-radius: 12px;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(99,102,241,0.4);
    }
    .dash-modal-icon-green {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 4px 12px rgba(16,185,129,0.4);
    }
    .dash-modal-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        line-height: 1.2;
    }
    .dash-modal-subtitle {
        font-size: 0.78rem;
        color: #94a3b8;
        margin: 2px 0 0 0;
        line-height: 1.3;
    }
    .dash-modal-close {
        width: 34px; height: 34px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .dash-modal-close:hover {
        background: rgba(239,68,68,0.15);
        border-color: rgba(239,68,68,0.3);
        color: #fca5a5;
    }

    /* Body */
    .dash-modal-body {
        padding: 22px;
        background: #0f172a;
        max-height: calc(100vh - 240px);
        overflow-y: auto;
    }

    /* Section card */
    .dash-form-section {
        background: rgba(255,255,255,0.025);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .dash-form-section:last-child { margin-bottom: 0; }
    .dash-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #c7d2fe;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .dash-section-title i {
        color: #818cf8;
        font-size: 0.95rem;
    }
    .dash-section-hint {
        margin-left: auto;
        font-size: 0.7rem;
        font-weight: 500;
        text-transform: none;
        letter-spacing: 0;
        color: #64748b;
    }

    /* Input fields */
    .dash-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 6px;
    }
    .dash-input {
        width: 100%;
        padding: 10px 14px;
        background: rgba(15,23,42,0.6);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        color: #f1f5f9;
        font-size: 0.9rem;
        transition: all 0.15s ease;
    }
    .dash-input::placeholder { color: #64748b; }
    .dash-input:focus {
        outline: none;
        border-color: rgba(99,102,241,0.5);
        background: rgba(15,23,42,0.9);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    .dash-input:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Format pill radio (BO1/BO3) */
    .format-pill {
        position: relative;
        display: block;
        padding: 12px 14px;
        background: rgba(15,23,42,0.6);
        border: 1px solid rgba(148,163,184,0.2);
        border-radius: 10px;
        cursor: pointer;
        transition: all .2s;
        margin: 0;
    }
    .format-pill input { position: absolute; opacity: 0; pointer-events: none; }
    .format-pill span { display: flex; flex-direction: column; gap: 2px; color: #cbd5e1; }
    .format-pill span strong { font-size: 0.95rem; color: #fff; letter-spacing: 0.05em; }
    .format-pill span small { font-size: 0.7rem; color: #64748b; }
    .format-pill:hover { border-color: rgba(99,102,241,0.4); }
    .format-pill:has(input:checked) {
        border-color: #6366f1;
        background: rgba(99,102,241,0.12);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
    }
    select.dash-input {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='%2394a3b8' d='M8 11.5L3 6h10z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 36px;
    }
    .dash-input[type="date"], .dash-input[type="datetime-local"] {
        color-scheme: dark;
    }
    .dash-hint {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
        margin-top: 6px;
    }

    /* Team A/B badge di label */
    .dash-team-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px; height: 18px;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 4px;
        margin-right: 6px;
        color: #fff;
    }
    .dash-team-badge-a { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .dash-team-badge-b { background: linear-gradient(135deg, #f59e0b, #ef4444); }

    /* Info banner */
    .dash-info-banner {
        display: flex;
        align-items: flex-start;
        gap: 4px;
        padding: 10px 14px;
        background: rgba(59,130,246,0.08);
        border: 1px solid rgba(59,130,246,0.2);
        border-left: 3px solid #3b82f6;
        border-radius: 10px;
        font-size: 0.8rem;
        color: #bfdbfe;
        margin-bottom: 14px;
    }
    .dash-info-banner i { color: #60a5fa; margin-top: 2px; }

    /* Team picker */
    .dash-team-picker {
        max-height: 320px;
        overflow-y: auto;
        padding: 4px 2px;
    }
    .dash-team-picker::-webkit-scrollbar { width: 6px; }
    .dash-team-picker::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

    .dash-prodi-group {
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px dashed rgba(255,255,255,0.06);
    }
    .dash-prodi-group:last-child { border-bottom: none; padding-bottom: 0; margin-bottom: 0; }
    .dash-prodi-header {
        display: flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #818cf8;
        margin-bottom: 8px;
    }
    .dash-prodi-special { color: #fbbf24; }
    .dash-prodi-count {
        margin-left: auto;
        background: rgba(255,255,255,0.05);
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.65rem;
        color: #94a3b8;
        text-transform: none;
        letter-spacing: 0;
    }

    .dash-team-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }
    @media (max-width: 575px) {
        .dash-team-grid { grid-template-columns: 1fr; }
    }

    .dash-team-pick {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.15s ease;
        margin: 0;
    }
    .dash-team-pick-wide { padding: 10px 14px; }
    .dash-team-pick input[type="checkbox"] {
        width: 0;
        height: 0;
        opacity: 0;
        margin: 0;
        padding: 0;
        pointer-events: none;
        flex: 0 0 0;
    }
    .dash-team-pick:hover {
        background: rgba(99,102,241,0.06);
        border-color: rgba(99,102,241,0.25);
    }
    .dash-team-pick:has(input:checked) {
        background: rgba(99,102,241,0.14);
        border-color: rgba(99,102,241,0.5);
        box-shadow: 0 0 0 1px rgba(99,102,241,0.3);
    }
    .dash-team-check {
        width: 20px; height: 20px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.04);
        border: 1.5px solid rgba(255,255,255,0.15);
        border-radius: 5px;
        color: transparent;
        font-size: 0.75rem;
        transition: all 0.15s ease;
    }
    .dash-team-pick:has(input:checked) .dash-team-check {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-color: #6366f1;
        color: #fff;
    }
    .dash-team-name {
        flex-grow: 1;
        font-size: 0.85rem;
        font-weight: 500;
        color: #e2e8f0;
        line-height: 1.3;
    }
    .dash-team-pick:has(input:checked) .dash-team-name { color: #fff; font-weight: 600; }

    /* Footer */
    .dash-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 22px;
        background: rgba(0,0,0,0.2);
        border-top: 1px solid rgba(255,255,255,0.06);
    }
    .btn-modal-cancel {
        padding: 9px 18px;
        background: transparent;
        color: #94a3b8;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 9px;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.15s ease;
    }
    .btn-modal-cancel:hover { color: #fff; border-color: rgba(255,255,255,0.25); }
    .btn-modal-primary {
        display: inline-flex;
        align-items: center;
        padding: 9px 22px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border: none;
        border-radius: 9px;
        font-weight: 600;
        font-size: 0.85rem;
        box-shadow: 0 4px 14px rgba(99,102,241,0.35);
        transition: all 0.15s ease;
    }
    .btn-modal-primary:hover {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(99,102,241,0.5);
    }
    .btn-modal-primary-green {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 4px 14px rgba(16,185,129,0.35);
    }
    .btn-modal-primary-green:hover {
        background: linear-gradient(135deg, #059669, #047857);
        box-shadow: 0 6px 18px rgba(16,185,129,0.5);
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
                                        value="{{ $p->lokasi }}" placeholder="Contoh: GOR UKDW" required>
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
