@extends('layouts.app')

@section('title', 'Detail Pertandingan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ url()->previous() }}" class="btn-back">
                    <i class="bi bi-arrow-left-circle mr-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="detail-card">
                <!-- Header Section -->
                <div class="detail-header">
                    <div class="d-flex align-items-center">
                        <div class="sport-avatar mr-3">
                            <i class="bi {{ $pertandingan->sport->icon ?? 'bi-trophy' }}"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-white">
                                {{ $pertandingan->sport->nama_sport ?? 'Tournament' }}
                            </h5>
                            <p class="small text-muted mb-0">Rector Cup Official Match</p>
                        </div>
                    </div>
                    <div>
                        @if($pertandingan->status == 'live')
                            <span class="status-pill status-live">
                                <span class="live-dot"></span> LIVE
                            </span>
                        @elseif($pertandingan->status == 'finished')
                            <span class="status-pill status-finished">
                                <i class="bi bi-check-circle-fill"></i> FINISHED
                            </span>
                        @else
                            <span class="status-pill status-upcoming">
                                <i class="bi bi-clock"></i> UPCOMING
                            </span>
                        @endif
                    </div>
                </div>

                <div class="detail-body">
                    <!-- Score Board -->
                    <div class="score-board">
                        <div class="team-block">
                            <div class="team-emblem team-emblem-a">
                                {{ strtoupper(substr($pertandingan->teamA?->name ?? 'T', 0, 1)) }}
                            </div>
                            <h3 class="team-name">{{ $pertandingan->teamA?->name ?? 'TBD' }}</h3>
                            <div class="team-prodi-badge">{{ $pertandingan->teamA ? strtoupper($pertandingan->teamA->prodi) : '-' }}</div>
                        </div>

                        <div class="score-display">
                            <div class="score-numbers">
                                <span>{{ $pertandingan->score_a }}</span>
                                <span class="score-sep">:</span>
                                <span>{{ $pertandingan->score_b }}</span>
                            </div>
                            <div class="score-label">Score</div>
                        </div>

                        <div class="team-block">
                            <div class="team-emblem team-emblem-b">
                                {{ strtoupper(substr($pertandingan->teamB?->name ?? 'T', 0, 1)) }}
                            </div>
                            <h3 class="team-name">{{ $pertandingan->teamB?->name ?? 'TBD' }}</h3>
                            <div class="team-prodi-badge">{{ $pertandingan->teamB ? strtoupper($pertandingan->teamB->prodi) : '-' }}</div>
                        </div>
                    </div>

                    <!-- Match Info Grid -->
                    <div class="match-info-grid">
                        <div class="info-cell">
                            <p class="info-label">Lokasi</p>
                            <p class="info-value"><i class="bi bi-geo-alt"></i>{{ $pertandingan->lokasi ?? '-' }}</p>
                        </div>
                        <div class="info-cell">
                            <p class="info-label">Tanggal</p>
                            <p class="info-value"><i class="bi bi-calendar3"></i>{{ $pertandingan->waktu_tanding->format('d M Y') }}</p>
                        </div>
                        <div class="info-cell">
                            <p class="info-label">Waktu</p>
                            <p class="info-value"><i class="bi bi-clock"></i>{{ $pertandingan->waktu_tanding->format('H:i') }} WIB</p>
                        </div>
                        <div class="info-cell">
                            <p class="info-label">Wasit/PJ</p>
                            <p class="info-value"><i class="bi bi-person-check"></i>Panitia</p>
                        </div>
                    </div>

                    <hr class="section-divider">

                    <!-- Game Breakdown (For BO3) -->
                    @if($pertandingan->games->isNotEmpty())
                        <div class="games-breakdown mb-5">
                            <h5 class="font-weight-bold text-white mb-4 d-flex align-items-center">
                                <i class="bi bi-controller mr-3 text-primary"></i> Detail Match (Best of {{ $pertandingan->games->count() }})
                            </h5>
                            <div class="row">
                                @foreach($pertandingan->games as $game)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 border-0" style="background: rgba(255,255,255,0.03); border-radius: 20px; border: 1px solid var(--glass-border) !important;">
                                            <div class="card-header border-0 bg-transparent pt-4 px-4 pb-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge badge-primary px-3 py-1" style="border-radius: 100px; font-size: 0.7rem;">MATCH {{ $game->game_number }}</span>
                                                    @if($game->winner_id)
                                                        <span class="text-success small font-weight-bold">
                                                            <i class="bi bi-trophy-fill mr-1"></i> {{ $game->winner?->name ?? 'Unknown' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-body p-4">
                                                <div class="text-center mb-3">
                                                    <div class="h4 font-weight-bold text-white mb-0">
                                                        {{ $game->score_a }} <span class="text-muted mx-1">-</span> {{ $game->score_b }}
                                                    </div>
                                                </div>
                                                
                                                @if($game->screenshot)
                                                    <div class="game-screenshot-container" style="cursor: pointer;" onclick="window.open('{{ asset('storage/' . $game->screenshot) }}', '_blank')">
                                                        <img src="{{ asset('storage/' . $game->screenshot) }}" alt="Game {{ $game->game_number }} SS" 
                                                            class="img-fluid rounded-lg shadow-sm mb-2" style="width: 100%; height: 120px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                                                        <div class="text-center">
                                                            <span class="text-muted small"><i class="bi bi-zoom-in mr-1"></i> Klik untuk perbesar</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center bg-dark rounded-lg" style="height: 120px; opacity: 0.5;">
                                                        <i class="bi bi-image text-muted h3 mb-0"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Evidence / Screenshot Section -->
                    <div class="evidence-section">
                        <h5 class="font-weight-bold text-white mb-4 d-flex align-items-center">
                            <i class="bi bi-image mr-3 text-primary"></i> Bukti Hasil Keseluruhan
                        </h5>

                        @if($pertandingan->screenshot)
                            <div class="card bg-black-20 border-0 p-2 overflow-hidden"
                                style="background: rgba(0,0,0,0.2); border-radius: 20px;">
                                <img src="{{ asset('storage/' . $pertandingan->screenshot) }}" alt="Screenshot Hasil"
                                    class="img-fluid rounded-lg shadow-lg"
                                    style="width: 100%; object-fit: contain; max-height: 600px;">
                            </div>
                            <div class="mt-3 p-3 bg-white-5 rounded-lg d-flex align-items-start"
                                style="background: rgba(255,255,255,0.03);">
                                <i class="bi bi-info-circle text-primary mt-1 mr-3"></i>
                                <p class="small text-muted mb-0">
                                    Screenshot ini diunggah secara resmi oleh panitia Rector Cup sebagai bukti transparansi dan
                                    validitas hasil pertandingan yang telah berlangsung secara keseluruhan.
                                </p>
                            </div>
                        @else
                            <div class="text-center py-5 rounded-lg"
                                style="background: rgba(255,255,255,0.02); border: 2px dashed var(--glass-border);">
                                <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                                    style="width: 70px; height: 70px; background: rgba(255,255,255,0.05) !important;">
                                    <i class="bi bi-image-alt text-muted h3 mb-0"></i>
                                </div>
                                <h6 class="text-white font-weight-bold">Belum Ada Bukti Visual</h6>
                                <p class="text-muted small mx-auto" style="max-width: 300px;">Screenshot hasil pertandingan akan
                                    diunggah oleh panitia setelah pertandingan selesai.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ── Back button ── */
        .btn-back {
            display: inline-flex;
            align-items: center;
            color: #94a3b8;
            font-weight: 600;
            text-decoration: none;
            transition: color .2s, transform .2s;
        }
        .btn-back i { font-size: 1.25rem; }
        .btn-back:hover { color: #fff; transform: translateX(-2px); text-decoration: none; }

        /* ── Detail card (root) ── */
        .detail-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
        }

        /* ── Header ── */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 20px 28px;
            background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.06));
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sport-avatar {
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 12px;
            color: #fff;
            font-size: 1.15rem;
        }

        /* ── Status pills (unified across pages) ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .status-live {
            background: rgba(239, 68, 68, 0.12);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.3);
        }
        .status-finished {
            background: rgba(16, 185, 129, 0.12);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.3);
        }
        .status-upcoming {
            background: rgba(148, 163, 184, 0.1);
            color: #cbd5e1;
            border-color: rgba(148, 163, 184, 0.25);
        }

        /* ── Body ── */
        .detail-body { padding: 32px; }
        @media (max-width: 767.98px) { .detail-body { padding: 20px; } }

        /* ── Score board ── */
        .score-board {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 24px;
            align-items: center;
            text-align: center;
            margin-bottom: 40px;
        }
        @media (max-width: 767.98px) {
            .score-board { grid-template-columns: 1fr; gap: 28px; }
        }
        .team-block { display: flex; flex-direction: column; align-items: center; }
        .team-emblem {
            width: 84px; height: 84px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 2rem;
            border-radius: 22px;
            margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }
        .team-emblem-a { background: linear-gradient(135deg, #6366f1, #a855f7); }
        .team-emblem-b { background: linear-gradient(135deg, #a855f7, #ec4899); }
        .team-name {
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
            font-size: 1.15rem;
        }
        .team-prodi-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #94a3b8;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 6px;
        }

        .score-display { display: flex; flex-direction: column; align-items: center; }
        .score-numbers {
            display: flex;
            align-items: baseline;
            gap: 14px;
            font-size: 4.5rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            letter-spacing: -2px;
            font-variant-numeric: tabular-nums;
        }
        .score-sep { color: #475569; font-size: 2.5rem; }
        .score-label {
            margin-top: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #64748b;
        }

        /* ── Match info grid ── */
        .match-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 36px;
        }
        @media (max-width: 575.98px) { .match-info-grid { grid-template-columns: repeat(2, 1fr); } }
        .info-cell {
            padding: 18px 16px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        .info-cell:last-child { border-right: none; }
        @media (max-width: 575.98px) {
            .info-cell:nth-child(2) { border-right: none; }
            .info-cell:nth-child(-n+2) { border-bottom: 1px solid rgba(255,255,255,0.05); }
        }
        .info-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }
        .info-value {
            color: #fff;
            font-weight: 600;
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        .info-value i { color: #6366f1; margin-right: 6px; }

        /* ── Section divider ── */
        .section-divider {
            border: 0;
            border-top: 1px solid rgba(255,255,255,0.06);
            margin: 36px 0;
        }

        /* ── Helpers retained ── */
        .bg-black-20 { background: rgba(0, 0, 0, 0.2); }
        .bg-white-5 { background: rgba(255, 255, 255, 0.05); }
    </style>
@endsection