@extends('layouts.app')

@section('title', 'Detail Pertandingan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <!-- Breadcrumb / Back Button -->
            <div class="mb-4 d-flex align-items-center justify-content-between">
                <a href="{{ url()->previous() }}" class="btn text-muted p-0 d-flex align-items-center hover-white">
                    <i class="bi bi-arrow-left-circle h4 mb-0 mr-2"></i>
                    <span class="font-weight-bold">Kembali</span>
                </a>
                <div class="text-muted small">
                    ID Pertandingan: <span class="text-white">#{{ $pertandingan->id }}</span>
                </div>
            </div>

            <div class="card overflow-hidden">
                <!-- Header Section with Sport Background Style -->
                <div class="card-header border-0 py-4 d-flex justify-content-between align-items-center"
                    style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1)) !important;">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                            style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important;">
                            <i class="bi {{ $pertandingan->sport->icon ?? 'bi-trophy' }} text-white h5 mb-0"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-white">
                                {{ $pertandingan->sport->nama_sport ?? 'Tournament' }}</h5>
                            <p class="small text-muted mb-0">Rector Cup Official Match</p>
                        </div>
                    </div>
                    <div>
                        @if($pertandingan->status == 'live')
                            <div class="badge-live">
                                <span class="live-dot"></span> LIVE
                            </div>
                        @elseif($pertandingan->status == 'finished')
                            <span class="badge px-3 py-2 text-white"
                                style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 100px; font-size: 0.75rem; font-weight: 800;">
                                <i class="bi bi-check-circle-fill mr-1"></i> FINISHED
                            </span>
                        @else
                            <span class="badge px-3 py-2 text-white"
                                style="background: rgba(148, 163, 184, 0.1); border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 100px; font-size: 0.75rem; font-weight: 800;">
                                <i class="bi bi-clock mr-1"></i> UPCOMING
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <!-- Score Board -->
                    <div class="row align-items-center text-center mb-5">
                        <div class="col-md-5 mb-4 mb-md-0">
                            <div class="team-emblem-placeholder mx-auto mb-3 d-flex align-items-center justify-content-center text-white font-weight-bold"
                                style="width: 80px; height: 80px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 24px; font-size: 2rem;">
                                {{ strtoupper(substr($pertandingan->teamA?->name ?? 'T', 0, 1)) }}
                            </div>
                            <h3 class="font-weight-bold text-white mb-1">{{ $pertandingan->teamA?->name ?? 'TBD' }}</h3>
                            <div class="badge badge-secondary px-3 py-1" style="border-radius: 8px; font-size: 0.7rem;">
                                PRODI {{ $pertandingan->teamA ? strtoupper($pertandingan->teamA->prodi) : '-' }}</div>
                        </div>

                        <div class="col-md-2 mb-4 mb-md-0">
                            <div class="d-flex flex-column align-items-center">
                                <div class="display-2 font-weight-bold text-white mb-0" style="letter-spacing: -2px;">
                                    {{ $pertandingan->score_a }}<span class="text-muted mx-2"
                                        style="font-size: 2rem;">:</span>{{ $pertandingan->score_b }}
                                </div>
                                <div class="text-muted small font-weight-bold text-uppercase tracking-widest">Score</div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="team-emblem-placeholder mx-auto mb-3 d-flex align-items-center justify-content-center text-white font-weight-bold"
                                style="width: 80px; height: 80px; background: linear-gradient(135deg, #a855f7, #ec4899); border-radius: 24px; font-size: 2rem;">
                                {{ strtoupper(substr($pertandingan->teamB?->name ?? 'T', 0, 1)) }}
                            </div>
                            <h3 class="font-weight-bold text-white mb-1">{{ $pertandingan->teamB?->name ?? 'TBD' }}</h3>
                            <div class="badge badge-secondary px-3 py-1" style="border-radius: 8px; font-size: 0.7rem;">
                                PRODI {{ $pertandingan->teamB ? strtoupper($pertandingan->teamB->prodi) : '-' }}</div>
                        </div>
                    </div>

                    <!-- Match Info Grid -->
                    <div class="row mb-5">
                        <div class="col-6 col-md-3 mb-4 mb-md-0 text-center border-right border-secondary"
                            style="border-color: rgba(255,255,255,0.05) !important;">
                            <p class="text-muted small text-uppercase font-weight-bold mb-2">Lokasi</p>
                            <p class="text-white font-weight-bold mb-0"><i
                                    class="bi bi-geo-alt text-primary mr-2"></i>{{ $pertandingan->lokasi }}</p>
                        </div>
                        <div class="col-6 col-md-3 mb-4 mb-md-0 text-center border-md-right border-secondary"
                            style="border-color: rgba(255,255,255,0.05) !important;">
                            <p class="text-muted small text-uppercase font-weight-bold mb-2">Tanggal</p>
                            <p class="text-white font-weight-bold mb-0"><i
                                    class="bi bi-calendar3 text-primary mr-2"></i>{{ $pertandingan->waktu_tanding->format('d M Y') }}
                            </p>
                        </div>
                        <div class="col-6 col-md-3 text-center border-right border-secondary"
                            style="border-color: rgba(255,255,255,0.05) !important;">
                            <p class="text-muted small text-uppercase font-weight-bold mb-2">Waktu</p>
                            <p class="text-white font-weight-bold mb-0"><i
                                    class="bi bi-clock text-primary mr-2"></i>{{ $pertandingan->waktu_tanding->format('H:i') }}
                                WIB</p>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <p class="text-muted small text-uppercase font-weight-bold mb-2">Wasit/PJ</p>
                            <p class="text-white font-weight-bold mb-0"><i
                                    class="bi bi-person-check text-primary mr-2"></i>Panitia</p>
                        </div>
                    </div>

                    <hr class="border-secondary mb-5" style="opacity: 0.1;">

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
        .hover-white:hover {
            color: #fff !important;
        }

        .bg-black-20 {
            background: rgba(0, 0, 0, 0.2);
        }

        .bg-white-5 {
            background: rgba(255, 255, 255, 0.05);
        }

        @media (max-width: 767.98px) {
            .border-md-right {
                border-right: none !important;
            }
        }
    </style>
@endsection