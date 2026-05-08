@extends('layouts.app')
@section('title', 'Riwayat Pertandingan')

@section('content')
    <style>
        .modern-filter-group {
            border-radius: 20px !important;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            background: rgba(15, 23, 42, 0.3);
        }

        .modern-filter-group .input-group-text {
            border-radius: 20px 0 0 20px !important;
            padding-left: 1.25rem;
            padding-right: 0.5rem;
            border: none;
            position: relative;
        }

        .modern-filter-group .form-control {
            border-radius: 0 20px 20px 0 !important;
            border: none;
            padding-left: 0.5rem;
            color: white !important;
        }

        .modern-filter-group .form-control option {
            background-color: var(--bg-dark);
            color: white;
        }

        .filter-card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid var(--glass-border);
        }

        .tournament-card:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.06) !important;
            border-color: var(--accent-primary) !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        
        .tournament-card {
            transition: all 0.3s ease;
        }
    </style>

    <div class="mb-5">
        <h2 class="font-weight-bold mb-1">Arsip Rector Cup</h2>
        <p class="text-muted">Jelajahi riwayat pertandingan dan bracket turnamen dari tahun ke tahun.</p>
    </div>

    {{-- Filter Tahun & Sport --}}
    <div class="mb-5">
        <div class="card filter-card p-4">
            <form action="{{ route('history') }}" method="GET" class="row align-items-center">
                <div class="col-md-5 mb-3 mb-md-0">
                    <div class="input-group modern-filter-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent">
                                <i class="bi bi-calendar-event text-primary"></i>
                            </span>
                        </div>
                        <select name="year" class="form-control" style="background: transparent;"
                            onchange="this.form.submit()">
                            <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }}>
                                Semua Tahun
                            </option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    Edisi Tahun {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5 mb-3 mb-md-0">
                    <div class="input-group modern-filter-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent">
                                <i class="bi bi-funnel text-primary"></i>
                            </span>
                        </div>
                        <select name="sport_id" class="form-control" style="background: transparent;"
                            onchange="this.form.submit()">
                            <option value="all" {{ $selectedSportId == 'all' ? 'selected' : '' }}>
                                Semua Cabang Olahraga
                            </option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ $selectedSportId == $sport->id ? 'selected' : '' }}>
                                    {{ $sport->nama_sport }}{{ $sport->sub_kategori ? ' - ' . $sport->sub_kategori : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('history') }}" class="btn btn-outline-secondary btn-block rounded-pill">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($selectedTournament)
        {{-- Detail View untuk Tournament yang Dipilih (Bracket) --}}
        <div class="mb-5">
            <div class="mb-4">
                <a href="{{ route('history', ['year' => $selectedYear]) }}" class="btn text-muted p-0 d-flex align-items-center hover-white">
                    <i class="bi bi-arrow-left-circle h4 mb-0 mr-2"></i>
                    <span class="font-weight-bold">Kembali ke Daftar Turnamen</span>
                </a>
            </div>

            <div class="card p-4 mb-5" style="background: rgba(30, 41, 59, 0.4); border-radius: 24px; border: 1px solid var(--glass-border);">
                <div class="d-flex align-items-center mb-5">
                    <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #6366f1, #a855f7) !important;">
                        <i class="bi {{ $selectedTournament->sport->icon ?? 'bi-diagram-3' }} text-white h4 mb-0"></i>
                    </div>
                    <div>
                        <h2 class="font-weight-bold mb-0 text-white">{{ $selectedTournament->name }}</h2>
                        <p class="text-muted mb-0 small text-uppercase tracking-wider">{{ $selectedTournament->sport->nama_sport }} • {{ $selectedTournament->year }}</p>
                    </div>
                </div>

                {{-- Podium Winners --}}
                @php
                    $final = $selectedTournament->pertandingans->where('babak', 'Final')->first();
                    $thirdPlace = $selectedTournament->pertandingans->where('babak', 'Perebutan Juara 3')->first();
                    
                    $juara1 = $final && $final->winner_id ? $final->winner : null;
                    $juara2 = $final && $final->winner_id ? ($final->winner_id == $final->team_a_id ? $final->teamB : $final->teamA) : null;
                    $juara3 = $thirdPlace && $thirdPlace->winner_id ? $thirdPlace->winner : null;
                @endphp

                {{-- Sheet eksternal: cabang dengan poin manual (Catur, PUBG, dll) --}}
                @if(!empty($selectedTournament->external_score_url))
                    <div class="row justify-content-center mb-5">
                        <div class="col-12 col-md-8 col-lg-6">
                            <h5 class="text-center text-muted small font-weight-bold text-uppercase tracking-widest mb-3">
                                <i class="bi bi-trophy-fill mr-2 text-warning"></i>Pemenang
                            </h5>
                            <a href="{{ $selectedTournament->external_score_url }}" target="_blank" rel="noopener noreferrer"
                               class="external-score-card d-flex align-items-center text-decoration-none">
                                <div class="external-score-icon">
                                    <i class="bi bi-table"></i>
                                </div>
                                <div class="flex-grow-1 text-left">
                                    <div class="external-score-title">Lihat Hasil &amp; Skor di Spreadsheet</div>
                                    <div class="external-score-sub">
                                        Cabang ini menggunakan sistem poin manual.
                                        Klik untuk membuka sheet resmi panitia.
                                    </div>
                                </div>
                                <i class="bi bi-box-arrow-up-right external-score-arrow"></i>
                            </a>
                        </div>
                    </div>
                @elseif($juara1 || $juara2 || $juara3)
                    <div class="row justify-content-center mb-5">
                        <div class="col-12 mb-4">
                            <h5 class="text-center text-muted small font-weight-bold text-uppercase tracking-widest mb-4">
                                <i class="bi bi-trophy-fill mr-2 text-warning"></i>Pemenang
                            </h5>
                            <div class="podium-row">
                                {{-- Juara 1 --}}
                                @if($juara1)
                                    <div class="podium-item podium-1">
                                        <div class="podium-medal podium-medal-gold">
                                            <i class="bi bi-1-circle-fill"></i>
                                        </div>
                                        <div class="podium-team-name">{{ $juara1->name }}</div>
                                        <div class="podium-block podium-block-gold">
                                            <span class="podium-rank">1<sup>st</sup></span>
                                            <span class="podium-label">Juara</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Juara 2 --}}
                                @if($juara2)
                                    <div class="podium-item podium-2">
                                        <div class="podium-medal podium-medal-silver">
                                            <i class="bi bi-2-circle-fill"></i>
                                        </div>
                                        <div class="podium-team-name">{{ $juara2->name }}</div>
                                        <div class="podium-block podium-block-silver">
                                            <span class="podium-rank">2<sup>nd</sup></span>
                                            <span class="podium-label">Runner-up</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Juara 3 --}}
                                @if($juara3)
                                    <div class="podium-item podium-3">
                                        <div class="podium-medal podium-medal-bronze">
                                            <i class="bi bi-3-circle-fill"></i>
                                        </div>
                                        <div class="podium-team-name">{{ $juara3->name }}</div>
                                        <div class="podium-block podium-block-bronze">
                                            <span class="podium-rank">3<sup>rd</sup></span>
                                            <span class="podium-label">Juara 3</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Bracket View --}}
                <div class="bracket-wrapper bracket-history-wrap overflow-auto pb-4">
                    <div class="d-flex bracket-rounds-flex" style="min-width: max-content;">
                        @php
                            $maxRound = $selectedTournament->pertandingans->max('round');
                        @endphp

                        @for($r = 1; $r <= $maxRound; $r++)
                            @php
                                $roundMatches = $selectedTournament->pertandingans->where('round', $r)->where('match_number', '!=', 99)->sortBy('match_number');
                                $roundClasses = 'bracket-round';
                                if ($r === 1)         $roundClasses .= ' bracket-round-first';
                                if ($r === $maxRound) $roundClasses .= ' bracket-round-final';
                            @endphp
                            <div class="{{ $roundClasses }} mr-5" style="width: 280px;">
                                <h5 class="text-center text-muted small font-weight-bold text-uppercase mb-4 bracket-round-title-h">
                                    {{ $roundMatches->first()->babak ?? 'Babak ' . $r }}
                                </h5>
                                <div class="match-list d-flex flex-column justify-content-around h-100">
                                    @foreach($roundMatches as $match)
                                        @php
                                            $labelParts = $match->custom_label ? preg_split('/\s+VS\s+/i', $match->custom_label) : null;
                                            $leftName = $match->teamA?->name ?? ($labelParts[0] ?? 'TBD');
                                            $rightName = $match->teamB?->name ?? ($labelParts[1] ?? 'TBD');
                                        @endphp
                                        <a href="{{ route('pertandingan.show', $match->id) }}" class="bracket-match-link text-decoration-none">
                                            <div class="bracket-match mb-4 p-3 rounded"
                                                style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); position: relative; transition: all 0.3s ease;">
                                                {{-- Tim A --}}
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="small font-weight-bold {{ $match->winner_id == $match->team_a_id && $match->team_a_id ? 'text-primary' : 'text-white' }}">
                                                        {{ $leftName }}
                                                    </span>
                                                    <span class="badge {{ $match->winner_id == $match->team_a_id && $match->team_a_id ? 'badge-primary' : 'badge-dark' }} px-2">
                                                        {{ $match->score_a }}
                                                    </span>
                                                </div>
                                                {{-- Tim B --}}
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="small font-weight-bold {{ $match->winner_id == $match->team_b_id && $match->team_b_id ? 'text-primary' : 'text-white' }}">
                                                        {{ $rightName }}
                                                    </span>
                                                    <span class="badge {{ $match->winner_id == $match->team_b_id && $match->team_b_id ? 'badge-primary' : 'badge-dark' }} px-2">
                                                        {{ $match->score_b }}
                                                    </span>
                                                </div>
                                                @if($match->keterangan)
                                                <div class="mt-2 text-center">
                                                    <span class="badge badge-info px-2 py-1" style="font-size: 0.6rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2);">
                                                        {{ $match->keterangan }}
                                                    </span>
                                                </div>
                                                @endif
                                                <div class="mt-2 text-center">
                                                    <span class="text-muted" style="font-size: 0.6rem;"><i class="bi bi-search mr-1"></i> Klik Detail</span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endfor

                        {{-- Kolom Khusus Perebutan Juara 3 --}}
                        @php
                            $thirdPlaceMatch = $selectedTournament->pertandingans->where('match_number', 99)->first();
                        @endphp
                        @if($thirdPlaceMatch)
                            @php
                                $thirdLabelParts = $thirdPlaceMatch->custom_label ? preg_split('/\s+VS\s+/i', $thirdPlaceMatch->custom_label) : null;
                                $thirdLeft = $thirdPlaceMatch->teamA?->name ?? ($thirdLabelParts[0] ?? 'TBD');
                                $thirdRight = $thirdPlaceMatch->teamB?->name ?? ($thirdLabelParts[1] ?? 'TBD');
                            @endphp
                            <div class="bracket-round bracket-round-third mr-5" style="width: 280px;">
                                <h5 class="text-center text-muted small font-weight-bold text-uppercase mb-4 bracket-round-title-h bracket-round-title-third">
                                    <i class="bi bi-award-fill mr-1"></i>{{ $thirdPlaceMatch->babak }}
                                </h5>
                                <div class="match-list d-flex flex-column justify-content-center h-100">
                                    <a href="{{ route('pertandingan.show', $thirdPlaceMatch->id) }}" class="bracket-match-link text-decoration-none">
                                        <div class="bracket-match mb-4 p-3 rounded"
                                            style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); position: relative; transition: all 0.3s ease;">
                                            {{-- Tim A --}}
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small font-weight-bold {{ $thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_a_id && $thirdPlaceMatch->team_a_id ? 'text-primary' : 'text-white' }}">
                                                    {{ $thirdLeft }}
                                                </span>
                                                <span class="badge {{ $thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_a_id && $thirdPlaceMatch->team_a_id ? 'badge-primary' : 'badge-dark' }} px-2">
                                                    {{ $thirdPlaceMatch->score_a }}
                                                </span>
                                            </div>
                                            {{-- Tim B --}}
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="small font-weight-bold {{ $thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_b_id && $thirdPlaceMatch->team_b_id ? 'text-primary' : 'text-white' }}">
                                                    {{ $thirdRight }}
                                                </span>
                                                <span class="badge {{ $thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_b_id && $thirdPlaceMatch->team_b_id ? 'badge-primary' : 'badge-dark' }} px-2">
                                                    {{ $thirdPlaceMatch->score_b }}
                                                </span>
                                            </div>
                                            @if($thirdPlaceMatch->keterangan)
                                            <div class="mt-2 text-center">
                                                <span class="badge badge-info px-2 py-1" style="font-size: 0.6rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2);">
                                                    {{ $thirdPlaceMatch->keterangan }}
                                                </span>
                                            </div>
                                            @endif
                                            <div class="mt-2 text-center">
                                                <span class="text-muted" style="font-size: 0.6rem;"><i class="bi bi-search mr-1"></i> Klik Detail</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Daftar Tournament --}}
        @if($tournaments->isNotEmpty())
            <div class="mb-5">
                <h4 class="font-weight-bold text-white mb-4"><i class="bi bi-trophy text-warning mr-2"></i> Hasil Tournament</h4>
                <div class="row">
                    @foreach($tournaments as $tournament)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <a href="{{ route('history', ['tournament_id' => $tournament->id, 'year' => $selectedYear]) }}" class="text-decoration-none">
                                <div class="card h-100 tournament-card border-0" 
                                    style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border) !important; border-radius: 24px;">
                                    <div class="card-body p-4 text-center">
                                        <div class="sport-icon-container mb-3 mx-auto d-flex align-items-center justify-content-center"
                                            style="width: 70px; height: 70px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 20px;">
                                            <i class="bi {{ $tournament->sport->icon ?? 'bi-controller' }} text-white h2 mb-0"></i>
                                        </div>
                                        
                                        <h5 class="font-weight-bold text-white mb-1">{{ strtoupper($tournament->sport->nama_sport) }}{{ $tournament->sport->sub_kategori ? ' - ' . strtoupper($tournament->sport->sub_kategori) : '' }}</h5>
                                        <p class="text-muted small text-uppercase tracking-widest mb-3">{{ $tournament->name }}</p>
                                        
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <div class="px-3 py-1 rounded-pill mr-2" style="background: rgba(99, 102, 241, 0.1);">
                                                <span class="text-primary font-weight-bold small">
                                                    {{ $tournament->teams_count }} TIM
                                                </span>
                                            </div>
                                            <div class="px-3 py-1 rounded-pill" style="background: rgba(255, 255, 255, 0.05);">
                                                <span class="text-white font-weight-bold small">
                                                    {{ $tournament->year }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                            Lihat Bracket
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Daftar Pertandingan Mandiri --}}
        @if($history->isNotEmpty())
            <h4 class="font-weight-bold text-white mb-4"><i class="bi bi-calendar-check text-primary mr-2"></i> Daftar Pertandingan</h4>
            @foreach($history as $tahun => $daftarPertandingan)
                <div class="year-section mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-3"
                            style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important;">
                            <span class="text-white font-weight-bold">{{ substr($tahun, 2) }}</span>
                        </div>
                        <h3 class="font-weight-bold mb-0 text-white">
                            Edisi Rector Cup {{ $tahun }}
                        </h3>
                        <div class="flex-grow-1 ml-4 border-bottom border-secondary" style="opacity: 0.1;"></div>
                    </div>

                    <div class="row">
                        @foreach($daftarPertandingan as $p)
                            @php
                                $cardLabelParts = $p->custom_label ? preg_split('/\s+VS\s+/i', $p->custom_label) : null;
                                $cardLeft = $p->teamA?->name ?? ($cardLabelParts[0] ?? 'TBD');
                                $cardRight = $p->teamB?->name ?? ($cardLabelParts[1] ?? 'TBD');
                            @endphp
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 20px;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <span class="badge badge-primary px-3 py-1" style="border-radius: 100px;">
                                                <i class="bi {{ $p->sport->icon ?? 'bi-trophy' }} mr-2"></i>
                                                {{ $p->sport->nama_sport ?? 'Tournament' }}{{ $p->sport?->sub_kategori ? ' - ' . $p->sport->sub_kategori : '' }}
                                            </span>
                                            <span class="text-muted small">
                                                <i class="bi bi-check-circle-fill text-success mr-1"></i> Selesai
                                            </span>
                                        </div>

                                        <div class="row text-center align-items-center py-3">
                                            <div class="col-5">
                                                <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">{{ $cardLeft }}</h4>
                                                <div class="h3 font-weight-bold text-white">{{ $p->score_a }}</div>
                                            </div>
                                            <div class="col-2 p-0">
                                                <div class="text-muted font-weight-bold small">VS</div>
                                            </div>
                                            <div class="col-5">
                                                <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">{{ $cardRight }}</h4>
                                                <div class="h3 font-weight-bold text-white">{{ $p->score_b }}</div>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-4 border-top border-secondary d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
                                            <div class="small text-muted">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="bi bi-calendar3 mr-2"></i> {{ \Carbon\Carbon::parse($p->waktu_tanding)->format('d M Y') }}
                                                </div>
                                            </div>
                                            <a href="{{ route('pertandingan.show', $p->id) }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        @if($history->isEmpty() && $tournaments->isEmpty())
            <div class="card border-0 py-5 text-center" style="background: rgba(255,255,255,0.02); border-radius: 24px;">
                <div class="card-body">
                    <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                        style="width: 80px; height: 80px; background: rgba(255,255,255,0.05) !important;">
                        <i class="bi bi-archive text-muted h2 mb-0"></i>
                    </div>
                    <h3 class="font-weight-bold text-white">Belum Ada Riwayat</h3>
                    <p class="text-muted mx-auto" style="max-width: 400px;">Data pertandingan untuk periode ini belum tersedia.</p>
                </div>
            </div>
        @endif
    @endif
@endsection

@section('styles')
<style>
    /* ── Podium row ── */
    .podium-row {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 18px;
        flex-wrap: wrap;
        padding: 0 12px;
    }

    .podium-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        min-width: 140px;
    }

    /* Medal numbered circle di atas */
    .podium-medal {
        font-size: 3rem;
        line-height: 1;
        margin-bottom: 10px;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        transition: transform 0.2s;
    }
    .podium-item:hover .podium-medal { transform: scale(1.08) rotate(-3deg); }

    .podium-medal-gold {
        color: #fbbf24;
        filter: drop-shadow(0 0 16px rgba(251,191,36,0.55)) drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }
    .podium-medal-silver {
        color: #cbd5e1;
        filter: drop-shadow(0 0 12px rgba(203,213,225,0.4)) drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }
    .podium-medal-bronze {
        color: #d97706;
        filter: drop-shadow(0 0 12px rgba(217,119,6,0.4)) drop-shadow(0 4px 8px rgba(0,0,0,0.3));
    }

    /* Nama tim */
    .podium-team-name {
        color: #f1f5f9;
        font-weight: 700;
        font-size: 0.92rem;
        margin-bottom: 12px;
        max-width: 160px;
        line-height: 1.25;
    }

    /* Block podium (kotak rank) */
    .podium-block {
        position: relative;
        width: 140px;
        border-radius: 12px 12px 0 0;
        padding: 14px 10px 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        box-shadow: 0 -4px 14px rgba(0,0,0,0.25);
    }
    .podium-block::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255,255,255,0.18), transparent 50%);
        pointer-events: none;
    }
    .podium-rank {
        font-size: 1.7rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.02em;
    }
    .podium-rank sup { font-size: 0.55em; font-weight: 800; opacity: 0.85; }
    .podium-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-top: 2px;
        opacity: 0.85;
    }

    /* Tinggi & warna per rank: 1 paling tinggi */
    .podium-block-gold {
        height: 130px;
        background: linear-gradient(180deg, #fbbf24, #d97706);
        color: #1a1300;
    }
    .podium-block-silver {
        height: 100px;
        background: linear-gradient(180deg, #e2e8f0, #94a3b8);
        color: #1a1f2e;
    }
    .podium-block-bronze {
        height: 75px;
        background: linear-gradient(180deg, #d97706, #92400e);
        color: #1a0f00;
    }

    /* Item juara 1 sedikit lebih besar untuk emphasis */
    .podium-1 .podium-medal { font-size: 3.6rem; }
    .podium-1 .podium-team-name { font-size: 1rem; }

    /* Mobile stack */
    @media (max-width: 575px) {
        .podium-row { gap: 12px; }
        .podium-item { min-width: 110px; }
        .podium-block { width: 110px; }
        .podium-medal { font-size: 2.4rem; }
        .podium-1 .podium-medal { font-size: 2.8rem; }
    }

    /* ── External score sheet card (cabang manual: Catur, PUBG, dll) ── */
    .external-score-card {
        gap: 16px;
        padding: 18px 20px;
        background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(59,130,246,0.08));
        border: 1px solid rgba(16,185,129,0.3);
        border-radius: 14px;
        color: #f1f5f9;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(16,185,129,0.1);
    }
    .external-score-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,0.55);
        box-shadow: 0 6px 22px rgba(16,185,129,0.25);
        color: #fff;
        text-decoration: none;
    }
    .external-score-icon {
        width: 56px; height: 56px;
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border-radius: 12px;
        font-size: 1.6rem;
        box-shadow: 0 4px 12px rgba(16,185,129,0.4);
    }
    .external-score-title {
        font-weight: 700;
        font-size: 1rem;
        color: #f1f5f9;
        margin-bottom: 2px;
    }
    .external-score-sub {
        font-size: 0.8rem;
        color: #94a3b8;
        line-height: 1.4;
    }
    .external-score-arrow {
        flex-shrink: 0;
        font-size: 1.2rem;
        color: #34d399;
        opacity: 0.7;
        transition: all 0.2s;
    }
    .external-score-card:hover .external-score-arrow {
        opacity: 1;
        transform: translate(2px, -2px);
    }

    /* ─────────── BRACKET HISTORY (Liquipedia style) ─────────── */
    .bracket-history-wrap {
        padding: 8px 4px 24px;
    }

    /* Round title sebagai pill */
    .bracket-history-wrap .bracket-round-title-h {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: linear-gradient(135deg, rgba(99,102,241,0.18), rgba(168,85,247,0.12));
        border: 1px solid rgba(99,102,241,0.3);
        color: #c7d2fe !important;
        padding: 7px 14px;
        border-radius: 999px;
        letter-spacing: 0.1em;
        margin-bottom: 18px !important;
        box-shadow: 0 2px 8px rgba(99,102,241,0.15);
        align-self: center;
    }
    .bracket-history-wrap .bracket-round-title-third {
        background: linear-gradient(135deg, rgba(217,119,6,0.2), rgba(120,53,15,0.15));
        border-color: rgba(217,119,6,0.4);
        color: #fbbf24 !important;
    }

    /* Round container */
    .bracket-history-wrap .bracket-round {
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    /* Match list = full height kolom, override bootstrap justify-around dengan flex:1 child */
    .bracket-history-wrap .match-list {
        position: relative;
        flex: 1;
        justify-content: stretch !important;
    }

    /* Setiap match-link mengambil tinggi yang sama (flex 1) */
    .bracket-history-wrap .bracket-match-link {
        position: relative;
        display: flex !important;
        flex: 1 1 0;
        align-items: center;
        margin-bottom: 0 !important;
    }
    /* Match content tetap natural size, di-center di link wrapper */
    .bracket-history-wrap .bracket-match {
        position: relative;
        z-index: 2;
        width: 100%;
        margin-bottom: 0 !important;
    }
    .bracket-history-wrap .bracket-match:hover {
        border-color: rgba(99,102,241,0.5) !important;
        box-shadow: 0 4px 14px rgba(99,102,241,0.2);
        transform: translateY(-1px);
    }

    /* ── Connector lines ── */
    /* Variabel warna */
    .bracket-history-wrap { --bk-line: rgba(99,102,241,0.45); --bk-line-win: rgba(16,185,129,0.6); }

    /* Garis masuk dari kiri (semua round kecuali round-first dan kolom juara 3) */
    .bracket-history-wrap .bracket-round:not(.bracket-round-first):not(.bracket-round-third) .bracket-match-link::before {
        content: "";
        position: absolute;
        left: -3rem;
        top: 50%;
        width: 3rem;
        height: 2px;
        background: var(--bk-line);
        z-index: 1;
    }

    /* L-shape keluar ke kanan: pakai border pada area dari midpoint match → edge link wrapper.
       Karena setiap link tinggi sama (flex 1), edge link = midpoint pasangan match. */
    .bracket-history-wrap .bracket-round:not(.bracket-round-final):not(.bracket-round-third) .bracket-match-link::after {
        content: "";
        position: absolute;
        right: -3rem;
        width: 3rem;
        background: transparent;
        z-index: 1;
    }
    /* Match ganjil (top of pair): horizontal kanan dari midpoint, lalu turun ke bottom edge (= merge point) */
    .bracket-history-wrap .bracket-round:not(.bracket-round-final):not(.bracket-round-third) .match-list > .bracket-match-link:nth-child(odd)::after {
        top: 50%;
        bottom: 0;
        border-top: 2px solid var(--bk-line);
        border-right: 2px solid var(--bk-line);
        border-top-right-radius: 8px;
    }
    /* Match genap (bottom of pair): horizontal kanan dari midpoint, lalu naik ke top edge (= merge point) */
    .bracket-history-wrap .bracket-round:not(.bracket-round-final):not(.bracket-round-third) .match-list > .bracket-match-link:nth-child(even)::after {
        top: 0;
        bottom: 50%;
        border-bottom: 2px solid var(--bk-line);
        border-right: 2px solid var(--bk-line);
        border-bottom-right-radius: 8px;
    }

    /* Highlight kalau match punya pemenang (winner) */
    .bracket-history-wrap .bracket-match-link:has(.text-primary)::before {
        background: var(--bk-line-win);
        height: 3px;
    }
    .bracket-history-wrap .match-list > .bracket-match-link:has(.text-primary):nth-child(odd)::after {
        border-top-color: var(--bk-line-win);
        border-right-color: var(--bk-line-win);
        border-top-width: 3px;
        border-right-width: 3px;
    }
    .bracket-history-wrap .match-list > .bracket-match-link:has(.text-primary):nth-child(even)::after {
        border-bottom-color: var(--bk-line-win);
        border-right-color: var(--bk-line-win);
        border-bottom-width: 3px;
        border-right-width: 3px;
    }

    /* Pemisah visual: kolom Perebutan Juara 3 di luar bracket utama */
    .bracket-history-wrap .bracket-round-third {
        margin-left: 2.5rem !important;
        padding-left: 1.5rem;
        border-left: 1px dashed rgba(255,255,255,0.1);
        position: relative;
    }
    .bracket-history-wrap .bracket-round-third::before {
        content: "Bonus Match";
        position: absolute;
        left: 1.5rem;
        top: -8px;
        background: rgba(217,119,6,0.15);
        color: #fbbf24;
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        padding: 2px 8px;
        border-radius: 4px;
        text-transform: uppercase;
    }
</style>
@endsection
