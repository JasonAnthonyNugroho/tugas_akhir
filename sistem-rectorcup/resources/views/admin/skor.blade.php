@extends('layouts.app')

@section('title', 'Kelola Skor')

@section('content')
    <div class="mb-5 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
        <div>
            <h2 class="font-weight-bold mb-1">Kelola Skor & Status</h2>
            <p class="text-muted mb-0">Update hasil pertandingan secara real-time untuk penonton.</p>
        </div>
        <a href="{{ route('admin.test-upload') }}" class="btn btn-outline-info rounded-pill px-4 shadow-sm font-weight-bold" style="border: 2px solid #06b6d4 !important; color: #fff !important; background: rgba(6, 182, 212, 0.1);">
            <i class="bi bi-shield-check mr-2"></i> Diagnostik Upload & Drive
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4 py-3" style="border-radius: 16px; background: rgba(239, 68, 68, 0.1); color: #f87171;">
            <h6 class="font-weight-bold mb-2"><i class="bi bi-exclamation-triangle-fill mr-2"></i> Gagal Memperbarui Skor:</h6>
            <ul class="mb-0 pl-4 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4 py-3" style="border-radius: 16px; background: rgba(239, 68, 68, 0.1); color: #f87171;">
            <h6 class="font-weight-bold mb-0"><i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ session('error') }}</h6>
        </div>
    @endif

    <div class="row">
        @php $liveMatches = $pertandingans->where('status', 'live'); @endphp

        <div class="col-12 mb-4">
            <div class="d-flex align-items-center">
                <div class="badge-live mr-3">
                    <span class="live-dot"></span> LIVE NOW
                </div>
                <div class="flex-grow-1 border-bottom border-secondary" style="opacity: 0.1;"></div>
            </div>
        </div>

        @if($liveMatches->isEmpty())
            <div class="col-12 mb-5">
                <div class="card border-0 py-5 text-center" style="background: rgba(255,255,255,0.02);">
                    <div class="card-body">
                        <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                            style="width: 80px; height: 80px; background: rgba(255,255,255,0.05) !important;">
                            <i class="bi bi-broadcast text-muted h2 mb-0"></i>
                        </div>
                        <h5 class="text-white font-weight-bold">Tidak Ada Pertandingan Live</h5>
                        <p class="text-muted small mx-auto" style="max-width: 400px;">Aktifkan pertandingan dari daftar jadwal
                            di bawah untuk mulai memperbarui skor secara real-time.</p>
                    </div>
                </div>
            </div>
        @else
            @foreach($liveMatches as $p)
                <div class="col-lg-6 mb-4" data-match-id="{{ $p->id }}">
                    <div class="card h-100 border-0 shadow-lg" style="border-top: 4px solid #ef4444 !important;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="badge-primary">
                                    <i class="bi {{ $p->sport->icon ?? 'bi-trophy' }} mr-2"></i>
                                    {{ $p->sport->nama_sport ?? 'Tournament' }}
                                </span>
                                <span class="text-muted small"><i class="bi bi-geo-alt mr-1"></i> {{ $p->lokasi }}</span>
                            </div>

                            <form action="{{ url('/pertandingan/' . $p->id . '/update-score') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')

                                @if(!$p->team_a_id || !$p->team_b_id)
                                    {{-- TBD Warning — disable all score controls --}}
                                    <div class="text-center py-5 px-3">
                                        <div class="d-inline-flex align-items-center justify-content-center mb-3" 
                                             style="width: 60px; height: 60px; border-radius: 50%; background: rgba(245, 158, 11, 0.15);">
                                            <i class="bi bi-exclamation-triangle text-warning h4 mb-0"></i>
                                        </div>
                                        <h6 class="text-warning font-weight-bold mb-2">Tim Belum Lengkap</h6>
                                        <p class="text-muted small mb-0" style="max-width: 300px; margin: 0 auto;">
                                            {{ !$p->team_a_id ? 'Tim A' : '' }}{{ !$p->team_a_id && !$p->team_b_id ? ' & ' : '' }}{{ !$p->team_b_id ? 'Tim B' : '' }} 
                                            masih <strong>TBD</strong>. Selesaikan pertandingan babak sebelumnya terlebih dahulu.
                                        </p>
                                    </div>
                                @else

                                <div class="bg-dark-subtle rounded-xl p-4 mb-4"
                                    style="background: rgba(15, 23, 42, 0.3); border-radius: 20px; border: 1px solid var(--glass-border);">
                                    @if(strtoupper($p->sport->nama_sport ?? '') == 'PUBG MOBILE')
                                        <div class="text-center">
                                            <label class="small font-weight-bold text-muted text-uppercase mb-3 d-block">Total Points
                                                (Battle Royale)</label>
                                            <div class="d-flex justify-content-center align-items-center">
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-lg rounded-circle mr-3 score-btn score-btn-down"
                                                    data-for="score_a_{{ $p->id }}"
                                                    onclick="decrementScore('score_a_{{ $p->id }}')"
                                                    @if($p->score_a <= 0) disabled @endif>
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <input type="number" name="score_a" id="score_a_{{ $p->id }}" data-match-id="{{ $p->id }}"
                                                    class="form-control form-control-lg text-center font-weight-bold text-white bg-transparent border-0 p-0"
                                                    style="font-size: 3.5rem; width: 100px; height: auto;" value="{{ $p->score_a }}">
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-lg rounded-circle ml-3 score-btn score-btn-up"
                                                    onclick="incrementScore('score_a_{{ $p->id }}')">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <span class="h4 text-primary mb-0 ml-3">PTS</span>
                                            </div>
                                            <input type="hidden" name="score_b" value="0">
                                            <p class="text-muted small mt-2">{{ $p->teamA?->name ?? 'TBD' }}</p>
                                        </div>
                                    @else
                                        <div class="row align-items-center text-center">
                                            <div class="col-5">
                                                <label
                                                    class="small font-weight-bold text-muted text-uppercase mb-3 d-block text-truncate">{{ $p->teamA?->name ?? 'TBD' }}</label>
                                                <div class="d-flex flex-column align-items-center">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm rounded-pill mb-2 w-100 score-btn score-btn-up"
                                                        onclick="incrementScore('score_a_{{ $p->id }}')">
                                                        <i class="bi bi-chevron-up"></i>
                                                    </button>
                                                    <input type="number" name="score_a" id="score_a_{{ $p->id }}" data-match-id="{{ $p->id }}"
                                                        class="form-control form-control-lg text-center font-weight-bold text-white bg-transparent border-0 p-0"
                                                        style="font-size: 3rem; height: auto;" value="{{ $p->score_a }}">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm rounded-pill mt-2 w-100 score-btn score-btn-down"
                                                        data-for="score_a_{{ $p->id }}"
                                                        onclick="decrementScore('score_a_{{ $p->id }}')"
                                                        @if($p->score_a <= 0) disabled @endif>
                                                        <i class="bi bi-chevron-down"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-2 p-0">
                                                <div class="h3 text-muted mb-0">:</div>
                                            </div>
                                            <div class="col-5">
                                                <label
                                                    class="small font-weight-bold text-muted text-uppercase mb-3 d-block text-truncate">{{ $p->teamB?->name ?? 'TBD' }}</label>
                                                <div class="d-flex flex-column align-items-center">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm rounded-pill mb-2 w-100 score-btn score-btn-up"
                                                        onclick="incrementScore('score_b_{{ $p->id }}')">
                                                        <i class="bi bi-chevron-up"></i>
                                                    </button>
                                                    <input type="number" name="score_b" id="score_b_{{ $p->id }}" data-match-id="{{ $p->id }}"
                                                        class="form-control form-control-lg text-center font-weight-bold text-white bg-transparent border-0 p-0"
                                                        style="font-size: 3rem; height: auto;" value="{{ $p->score_b }}">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm rounded-pill mt-2 w-100 score-btn score-btn-down"
                                                        data-for="score_b_{{ $p->id }}"
                                                        onclick="decrementScore('score_b_{{ $p->id }}')"
                                                        @if($p->score_b <= 0) disabled @endif>
                                                        <i class="bi bi-chevron-down"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- BO3 Games Section (tampil hanya jika format_tanding = BO3) --}}
                                @if(($p->format_tanding ?? '') === 'BO3')
                                    <div class="mb-4 p-3 rounded-lg"
                                        style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                        <h6 class="small font-weight-bold text-primary text-uppercase mb-3"><i
                                                class="bi bi-controller mr-2"></i> Detail Game (BO3)</h6>
                                        @for($i = 1; $i <= 3; $i++)
                                            @php $game = $p->games->where('game_number', $i)->first(); @endphp
                                            <div class="row align-items-center mb-3">
                                                <div class="col-md-3">
                                                    <span class="small font-weight-bold text-muted">Game {{ $i }}</span>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="custom-file custom-file-sm">
                                                        <input type="file" name="game_screenshots[{{ $i }}]" class="custom-file-input"
                                                            id="game_ss{{ $p->id }}_{{ $i }}">
                                                        <label class="custom-file-label small" for="game_ss{{ $p->id }}_{{ $i }}"
                                                            style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--glass-border); color: var(--text-muted); border-radius: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            {{ $game && $game->screenshot ? 'Update SS' : 'Upload SS Game ' . $i }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                        <p class="small text-muted mb-0 mt-2"><i class="bi bi-info-circle mr-1"></i> Screenshot per game
                                            akan muncul di detail pertandingan.</p>
                                    </div>
                                @else
                                    {{-- BO1 / Single Match Screenshot --}}
                                    <div class="mb-4 p-3 rounded-lg"
                                        style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                        <h6 class="small font-weight-bold text-primary text-uppercase mb-3"><i
                                                class="bi bi-camera-fill mr-2"></i> Bukti Hasil Pertandingan</h6>
                                        <div class="custom-file custom-file-sm">
                                            <input type="file" name="screenshot" class="custom-file-input"
                                                id="screenshot{{ $p->id }}">
                                            <label class="custom-file-label small" for="screenshot{{ $p->id }}"
                                                style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--glass-border); color: var(--text-muted); border-radius: 8px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ $p->screenshot ? 'Update Screenshot Bukti' : 'Upload Screenshot Bukti' }}
                                            </label>
                                        </div>
                                        <p class="small text-muted mb-0 mt-2"><i class="bi bi-info-circle mr-1"></i> Screenshot bukti akan muncul di halaman detail pertandingan.</p>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="small font-weight-bold text-muted text-uppercase mb-2">
                                            <i class="bi bi-info-circle mr-1"></i> Keterangan Bracket
                                        </label>
                                        <input type="text" name="keterangan" class="form-control" 
                                            placeholder="Contoh: Badminton Ganda Putra, dll."
                                            value="{{ $p->keterangan ?? '' }}">
                                    </div>
                                </div>

                                {{-- Hidden status field, default = live --}}
                                <input type="hidden" name="status" id="statusField_{{ $p->id }}" value="live">

                                <div class="d-flex mt-2" style="gap: 10px;">
                                    <button type="submit" class="btn btn-primary flex-fill font-weight-bold py-3 shadow-lg skor-btn-update">
                                        <i class="bi bi-cloud-arrow-up-fill mr-2"></i> Update Skor
                                    </button>
                                    <button type="button" class="btn flex-fill font-weight-bold py-3 shadow-lg skor-btn-finish"
                                            onclick="confirmFinish({{ $p->id }}, '{{ addslashes($p->teamA?->name ?? 'TBD') }}', '{{ addslashes($p->teamB?->name ?? 'TBD') }}')">
                                        <i class="bi bi-flag-fill mr-2"></i> Selesaikan
                                    </button>
                                </div>
                                
                                @if(session('success') && session('updated_id') == $p->id)
                                    <div class="alert alert-success mt-3 mb-0 py-2 small border-0 text-center" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px;">
                                        <i class="bi bi-check-circle-fill mr-1"></i> Data sudah berhasil terupdate.
                                    </div>
                                @endif
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        <div class="col-12 mt-5 mb-4">
            <div class="d-flex align-items-center">
                <h5 class="text-white font-weight-bold mb-0 mr-3">AKTIVASI JADWAL (GROUPED)</h5>
                <div class="flex-grow-1 border-bottom border-secondary" style="opacity: 0.1;"></div>
            </div>
        </div>

        @foreach($tournaments as $tournament)
            @php 
                $tMatches = $groupedMatches->get('tournament_' . $tournament->id, collect())->where('status', 'scheduled');
            @endphp
            @if($tMatches->isNotEmpty())
                <div class="col-12 mb-4">
                    <div class="card border-0" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                                    style="width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1, #a855f7) !important;">
                                    <i class="bi {{ $tournament->sport->icon ?? 'bi-diagram-3' }} text-white small"></i>
                                </div>
                                <h6 class="text-white font-weight-bold mb-0">{{ $tournament->name }} <span class="text-muted small ml-2">{{ $tournament->sport->nama_sport }}</span></h6>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Babak</th>
                                            <th>Waktu</th>
                                            <th>Pertandingan</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tMatches as $p)
                                            <tr data-match-id="{{ $p->id }}">
                                                <td class="small text-muted align-middle">{{ $p->babak }}</td>
                                                <td class="align-middle">
                                                    <div class="text-white small font-weight-bold">{{ $p->waktu_tanding->format('H:i') }}</div>
                                                    <div class="text-muted small">{{ $p->waktu_tanding->format('d M') }}</div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="font-weight-600 text-uppercase small text-white">
                                                        {{ $p->teamA?->name ?? 'TBD' }} <span class="text-muted mx-2">VS</span> {{ $p->teamB?->name ?? 'TBD' }}
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button class="btn btn-sm btn-success rounded-pill px-4 shadow-sm font-weight-bold"
                                                        data-toggle="modal" data-target="#quickLive{{ $p->id }}">
                                                        Mulai Live
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        @php 
            $iMatches = $groupedMatches->get('independent', collect())->where('status', 'scheduled');
        @endphp
        @if($iMatches->isNotEmpty())
            <div class="col-12 mb-4">
                <div class="card border-0" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h6 class="text-white font-weight-bold mb-0"><i class="bi bi-calendar-event mr-2"></i> Pertandingan Mandiri</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Sport</th>
                                        <th>Waktu</th>
                                        <th>Pertandingan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($iMatches as $p)
                                        <tr data-match-id="{{ $p->id }}">
                                            <td class="align-middle">
                                                <span class="badge px-3 py-1" style="background: rgba(99, 102, 241, 0.1); color: var(--accent-primary); border-radius: 8px; font-weight: 600;">
                                                    {{ $p->sport->nama_sport ?? 'Tournament' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="text-white small font-weight-bold">{{ $p->waktu_tanding->format('H:i') }}</div>
                                                <div class="text-muted small">{{ $p->waktu_tanding->format('d M') }}</div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-600 text-uppercase small text-white">
                                                    {{ $p->teamA?->name ?? 'TBD' }} <span class="text-muted mx-2">VS</span> {{ $p->teamB?->name ?? 'TBD' }}
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button class="btn btn-sm btn-success rounded-pill px-4 shadow-sm font-weight-bold"
                                                    data-toggle="modal" data-target="#quickLive{{ $p->id }}">
                                                    Mulai Live
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- All Modals for Quick Live --}}
        @foreach($pertandingans->where('status', 'scheduled') as $p)
            <div class="modal fade" id="quickLive{{ $p->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" style="background: var(--bg-dark); border: 1px solid var(--glass-border); border-radius: 24px;">
                        <div class="modal-header border-0 p-4" style="background: linear-gradient(135deg, #10b981, #059669) !important; border-radius: 24px 24px 0 0;">
                            <h5 class="modal-title text-white font-weight-bold">Aktivasi Pertandingan</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <form action="{{ url('/pertandingan/' . $p->id . '/update-score') }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="score_a" value="0">
                            <input type="hidden" name="score_b" value="0">
                            <input type="hidden" name="status" value="live">
                            <div class="modal-body text-center p-5">
                                <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                                    style="width: 60px; height: 60px; background: rgba(16, 185, 129, 0.1);">
                                    <i class="bi bi-play-fill text-success h3 mb-0"></i>
                                </div>
                                <p class="text-muted mb-2">Mulai pertandingan live untuk:</p>
                                <h4 class="text-white font-weight-bold mb-4">
                                    {{ $p->teamA?->name ?? 'TBD' }} VS {{ $p->teamB?->name ?? 'TBD' }}
                                </h4>
                                <p class="text-muted small mb-0">Pertandingan akan segera muncul di dashboard publik.</p>
                            </div>
                            <div class="modal-footer border-0 p-4">
                                <button type="button" class="btn btn-link text-muted font-weight-bold text-decoration-none" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success px-5 font-weight-bold">Mulai Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .modern-file-input .custom-file-label {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            border-radius: 12px;
        }

        .modern-file-input .custom-file-label::after {
            background: var(--accent-primary);
            color: white;
            border-radius: 0 12px 12px 0;
            padding: 0.375rem 1rem;
        }

        .score-btn {
            transition: all 0.2s;
            border-width: 2px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .score-btn:active {
            transform: scale(0.9);
        }

        .score-btn:disabled {
            color: #6c757d !important;
            border-color: #6c757d !important;
            opacity: 0.5;
            cursor: not-allowed;
            background: transparent !important;
        }

        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Action buttons for score cards */
        .skor-btn-update {
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border: none;
            color: #fff;
            border-radius: 14px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .skor-btn-update:hover {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            color: #fff;
        }
        .skor-btn-finish {
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            border-radius: 14px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .skor-btn-finish:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: #ef4444;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
        }
    </style>

    <script>
        function syncDownButton(id) {
            const input = document.getElementById(id);
            if (!input) return;
            const val = parseInt(input.value || 0);
            document.querySelectorAll(`.score-btn-down[data-for="${id}"]`).forEach(btn => {
                btn.disabled = val <= 0;
            });
        }

        function incrementScore(id) {
            const input = document.getElementById(id);
            input.value = parseInt(input.value || 0) + 1;
            syncDownButton(id);
        }

        function decrementScore(id) {
            const input = document.getElementById(id);
            const val = parseInt(input.value || 0);
            if (val > 0) {
                input.value = val - 1;
            }
            syncDownButton(id);
        }

        function confirmFinish(matchId, teamA, teamB) {
            Swal.fire({
                title: 'Selesaikan Pertandingan?',
                html: `<div style="margin-top:8px">
                    <p style="font-size:1.05rem; margin-bottom:6px;"><strong>${teamA}</strong> <span style="color:#64748b">vs</span> <strong>${teamB}</strong></p>
                    <p style="color:#94a3b8; font-size:0.85rem;">Skor akan dikunci dan pertandingan akan diarsipkan. Pemenang akan otomatis maju ke babak selanjutnya (jika ada).</p>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#475569',
                confirmButtonText: '<i class="bi bi-flag-fill mr-1"></i> Ya, Selesaikan!',
                cancelButtonText: 'Batal',
                background: '#0f172a',
                color: '#f1f5f9',
                customClass: {
                    popup: 'swal-dark-popup',
                    confirmButton: 'swal-confirm-danger',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const statusField = document.getElementById('statusField_' + matchId);
                    statusField.value = 'finished';
                    statusField.closest('form').submit();
                }
            });
        }

        // Sinkron saat user mengetik langsung di input
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="score_a"], input[name="score_b"]').forEach(inp => {
                inp.addEventListener('input', () => syncDownButton(inp.id));
            });

            // Update label custom-file-label saat memilih berkas
            document.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('custom-file-input')) {
                    const fileName = e.target.value.split('\\').pop();
                    const label = e.target.nextElementSibling;
                    if (label && label.classList.contains('custom-file-label')) {
                        label.textContent = fileName || 'Pilih file';
                    }
                }
            });
        });

        // Laravel Reverb Real-time Implementation for Admin Skor
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Echo !== 'undefined') {
                console.log('Initializing Laravel Reverb for Admin Skor...');
                
                // Listen for score updates
                Echo.channel('scores')
                    .listen('.score.updated', function(data) {
                        console.log('Admin Skor: Score updated via Reverb:', data);
                        
                        // Update score in form if match exists
                        const scoreInputA = document.querySelector(`input[name="score_a"][data-match-id="${data.id}"]`);
                        const scoreInputB = document.querySelector(`input[name="score_b"][data-match-id="${data.id}"]`);
                        
                        if (scoreInputA) scoreInputA.value = data.score_a;
                        if (scoreInputB) scoreInputB.value = data.score_b;
                        
                        // Show notification
                        showSkorNotification(`Skor diperbarui! ${data.team_a || 'Tim A'} ${data.score_a} - ${data.score_b} ${data.team_b || 'Tim B'}`, 'success');
                    })
                    .listen('.match.created', function(data) {
                        console.log('Admin Skor: New match created via Reverb:', data);
                        showSkorNotification('Pertandingan baru dibuat! Refresh halaman untuk melihat.', 'info');
                    })
                    .listen('.match.status.updated', function(data) {
                        console.log('Admin Skor: Match status updated via Reverb:', data);
                        
                        // Handle status live → reload agar grouping/section terupdate
                        if (data.status === 'live') {
                            showSkorNotification('Pertandingan dimulai! Memperbarui halaman...', 'info');
                            setTimeout(() => location.reload(), 1000);
                            return;
                        }

                        // Handle status finished - remove from page
                        if (data.status === 'finished') {
                            const matchCard = document.querySelector(`[data-match-id="${data.id}"]`);
                            if (matchCard) {
                                // Animasi fade out
                                matchCard.style.transition = 'all 0.5s ease';
                                matchCard.style.opacity = '0';
                                matchCard.style.transform = 'scale(0.9)';
                                
                                setTimeout(() => {
                                    matchCard.remove();
                                }, 500);
                                
                                showSkorNotification(`Pertandingan selesai! ${data.data.team_a || 'Tim A'} ${data.data.score_a} - ${data.data.score_b} ${data.data.team_b || 'Tim B'}`, 'success');
                            }
                        }
                    });
                
                console.log('Laravel Reverb initialized for Admin Skor');
            }
            
            // Handle Pusher connection errors
            if (typeof Echo !== 'undefined' && Echo.connector && Echo.connector.pusher) {
                Echo.connector.pusher.connection.bind('error', function(err) {
                    console.warn('Pusher connection error:', err);
                });
                
                Echo.connector.pusher.connection.bind('disconnected', function() {
                    console.warn('Pusher disconnected - real-time features paused');
                });
            }
        });
        
        // Function to show notification
        function showSkorNotification(message, type = 'info') {
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

        // Scroll to match when navigating from dashboard with #match-{id}
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#match-')) {
                const matchId = hash.replace('#match-', '');
                const target = document.querySelector(`[data-match-id="${matchId}"]`);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // Highlight animation
                        target.style.transition = 'all 0.4s ease';
                        target.style.boxShadow = '0 0 0 3px rgba(99, 102, 241, 0.5), 0 4px 20px rgba(99, 102, 241, 0.3)';
                        target.style.transform = 'scale(1.01)';
                        setTimeout(() => {
                            target.style.boxShadow = '';
                            target.style.transform = '';
                        }, 2500);
                    }, 500);
                }
            }
        });
    </script>
@endsection