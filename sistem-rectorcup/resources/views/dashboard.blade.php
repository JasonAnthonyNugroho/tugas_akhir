@extends('layouts.app')

@section('title', 'Dashboard Live')

@section('content')
    @php
        $liveMatches = $pertandingans->where('status', 'live');
        $scheduledMatches = $pertandingans->where('status', 'scheduled');
    @endphp

    {{-- Connection Status --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="font-weight-bold mb-1">Dashboard Rector Cup</h2>
                <p class="text-muted">Pantau pertandingan live, bracket tournament, dan jadwal mendatang.</p>
            </div>
            <div id="connectionStatus" class="small">
                <span class="badge badge-secondary" style="border-radius: 100px;">
                    <i class="bi bi-wifi-off mr-1"></i> Connecting...
                </span>
            </div>
        </div>
    </div>

    {{-- SECTION 1: LIVE MATCHES (Paling Atas) --}}
    @if($liveMatches->isNotEmpty())
        <div class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <div class="badge-live mr-3" style="padding: 8px 16px; font-size: 0.875rem;">
                    <span class="live-dot"></span> LIVE NOW
                </div>
                <div class="flex-grow-1 border-bottom border-danger" style="opacity: 0.3;"></div>
            </div>
            
            <div class="row" id="liveMatchContainer">
                @foreach($liveMatches as $p)
                    <div class="col-md-6 col-xl-4 mb-4 match-card" data-id="{{ $p->id }}">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 24px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border) !important; transition: all 0.3s ease;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <span class="badge badge-primary px-3 py-1" style="border-radius: 100px;">
                                        <i class="bi {{ $p->sport->icon ?? 'bi-trophy' }} mr-2"></i>
                                        {{ $p->sport->nama_sport ?? 'Tournament' }}
                                    </span>
                                    <div class="badge-live-container">
                                        <div class="badge-live">
                                            <span class="live-dot"></span> LIVE
                                        </div>
                                    </div>
                                </div>

                                <div class="row text-center align-items-center py-3">
                                    <div class="col-5">
                                        <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">{{ $p->teamA?->name ?? 'TBD' }}</h4>
                                        <div class="display-4 font-weight-bold text-white score-a">{{ $p->score_a }}</div>
                                    </div>
                                    <div class="col-2 p-0">
                                        <div class="text-muted font-weight-bold small">VS</div>
                                    </div>
                                    <div class="col-5">
                                        <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">{{ $p->teamB?->name ?? 'TBD' }}</h4>
                                        <div class="display-4 font-weight-bold text-white score-b">{{ $p->score_b }}</div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-top border-secondary d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
                                    <div class="small text-muted">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-geo-alt mr-2 text-primary"></i> {{ $p->lokasi }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar3 mr-2 text-primary"></i>
                                            {{ \Carbon\Carbon::parse($p->waktu_tanding)->format('d M, H:i') }}
                                        </div>
                                    </div>
                                    <a href="{{ route('pertandingan.show', $p->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                        Detail <i class="bi bi-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- SECTION 2: TOURNAMENT BRACKETS (Di Tengah) --}}
    @if($tournaments->isNotEmpty())
        <div class="mb-5">
            <div class="d-flex align-items-center mb-4">
                <h4 class="text-white font-weight-bold mb-0 mr-3">
                    <i class="bi bi-trophy text-warning mr-2"></i>Tournament Brackets
                </h4>
                <div class="flex-grow-1 border-bottom border-warning" style="opacity: 0.3;"></div>
            </div>
            
            <div class="row">
                @foreach($tournaments as $tournament)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 h-100" style="background: rgba(255,255,255,0.03); border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.2) !important;">
                            <div class="card-body p-4">
                                {{-- Header --}}
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-gradient rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b, #d97706) !important;">
                                        <i class="bi {{ $tournament->sport->icon ?? 'bi-trophy' }} text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-weight-bold text-white mb-0">{{ $tournament->name }}</h6>
                                        <small class="text-muted">{{ $tournament->sport->nama_sport }} • {{ $tournament->year }}</small>
                                    </div>
                                </div>
                                
                                {{-- Stats --}}
                                <div class="row mb-3">
                                    <div class="col-4 text-center">
                                        <div class="text-warning font-weight-bold">{{ $tournament->teams->count() }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Tim</small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="text-success font-weight-bold">{{ $tournament->pertandingans->where('status', 'finished')->count() }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Selesai</small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="text-info font-weight-bold">{{ $tournament->pertandingans->where('status', 'live')->count() }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Live</small>
                                    </div>
                                </div>
                                
                                {{-- Recent Matches --}}
                                @php
                                    $recentMatches = $tournament->pertandingans->whereIn('status', ['live', 'finished'])->take(2);
                                @endphp
                                @if($recentMatches->isNotEmpty())
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-2">Pertandingan Terbaru:</small>
                                        @foreach($recentMatches as $match)
                                            <div class="d-flex justify-content-between align-items-center py-1 px-2 rounded mb-1" 
                                                 style="background: rgba(255,255,255,0.03);">
                                                <div class="d-flex align-items-center">
                                                    @if($match->status === 'live')
                                                        <span class="badge badge-danger mr-2" style="font-size: 0.6rem; padding: 2px 6px;">LIVE</span>
                                                    @elseif($match->winner_id)
                                                        <span class="badge badge-success mr-2" style="font-size: 0.6rem; padding: 2px 6px;">DONE</span>
                                                    @endif
                                                    <small class="text-white">{{ $match->teamA?->name ?? 'TBD' }} vs {{ $match->teamB?->name ?? 'TBD' }}</small>
                                                </div>
                                                @if($match->status !== 'scheduled')
                                                    <small class="text-warning font-weight-bold">{{ $match->score_a }} - {{ $match->score_b }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                {{-- Action --}}
                                <a href="{{ route('tournament.public.bracket', $tournament) }}" class="btn btn-outline-warning btn-sm w-100">
                                    <i class="bi bi-eye mr-2"></i>Lihat Bracket
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- SECTION 3: SCHEDULED MATCHES (Paling Bawah) --}}
    @if($scheduledMatches->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex align-items-center mb-4">
                <h4 class="text-white font-weight-bold mb-0 mr-3">
                    <i class="bi bi-calendar-event text-info mr-2"></i>Jadwal Mendatang
                </h4>
                <div class="flex-grow-1 border-bottom border-info" style="opacity: 0.3;"></div>
            </div>
            
            <div class="row" id="scheduledMatchContainer">
                @foreach($scheduledMatches as $p)
                    <div class="col-md-6 col-xl-4 mb-4 match-card" data-id="{{ $p->id }}">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 24px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border) !important; transition: all 0.3s ease; opacity: 0.9;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <span class="badge badge-secondary px-3 py-1" style="border-radius: 100px;">
                                        <i class="bi {{ $p->sport->icon ?? 'bi-trophy' }} mr-2"></i>
                                        {{ $p->sport->nama_sport ?? 'Tournament' }}
                                    </span>
                                    <div class="badge-live-container">
                                        <span class="badge badge-dark px-3 py-1 text-uppercase" style="border-radius: 100px; background: rgba(255,255,255,0.05);">
                                            Terjadwal
                                        </span>
                                    </div>
                                </div>

                                <div class="row text-center align-items-center py-3">
                                    <div class="col-5">
                                        <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">{{ $p->teamA?->name ?? 'TBD' }}</h4>
                                        <div class="h3 font-weight-bold text-muted">-</div>
                                    </div>
                                    <div class="col-2 p-0">
                                        <div class="text-muted font-weight-bold small">VS</div>
                                    </div>
                                    <div class="col-5">
                                        <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">{{ $p->teamB?->name ?? 'TBD' }}</h4>
                                        <div class="h3 font-weight-bold text-muted">-</div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-top border-secondary d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
                                    <div class="small text-muted">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-geo-alt mr-2 text-primary"></i> {{ $p->lokasi }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar3 mr-2 text-primary"></i>
                                            {{ \Carbon\Carbon::parse($p->waktu_tanding)->format('d M, H:i') }}
                                        </div>
                                    </div>
                                    <span class="badge badge-info px-3 py-2" style="border-radius: 100px; font-size: 0.75rem;">
                                        {{ \Carbon\Carbon::parse($p->waktu_tanding)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if($liveMatches->isEmpty() && $scheduledMatches->isEmpty() && $tournaments->isEmpty())
        <div class="card border-0 py-5 text-center" style="background: rgba(255,255,255,0.02); border-radius: 24px;">
            <div class="card-body">
                <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 80px; height: 80px; background: rgba(255,255,255,0.05) !important;">
                    <i class="bi bi-calendar-x text-muted h2 mb-0"></i>
                </div>
                <h5 class="font-weight-bold text-white">Tidak Ada Pertandingan</h5>
                <p class="text-muted mx-auto mb-0" style="max-width: 400px;">Saat ini tidak ada pertandingan yang sedang berlangsung atau terjadwal.</p>
            </div>
        </div>
    @endif
@endsection

@section('styles')
<style>
    .match-card:hover .card {
        transform: translateY(-5px);
        background: rgba(255,255,255,0.06) !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
    }

    .badge-live {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        padding: 4px 12px;
        border-radius: 100px;
        font-weight: bold;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background-color: #ef4444;
        border-radius: 50%;
        margin-right: 6px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    
    /* Fix untuk mencegah kemiringan UI */
    .card {
        transform: none !important;
    }
    
    .match-card {
        transform: none !important;
    }
    
    .score-a, .score-b {
        transform-origin: center !important;
    }
    
    .live-dot {
        transform-origin: center !important;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Echo listeners...');
    
    const statusBadge = document.getElementById('connectionStatus');
    
    function updateConnectionStatus(connected) {
        if (statusBadge) {
            if (connected) {
                statusBadge.innerHTML = `
                    <span class="badge badge-success" style="border-radius: 100px;">
                        <i class="bi bi-wifi mr-1"></i> Live
                    </span>
                `;
            } else {
                statusBadge.innerHTML = `
                    <span class="badge badge-danger" style="border-radius: 100px;">
                        <i class="bi bi-wifi-off mr-1"></i> Disconnected
                    </span>
                `;
            }
        }
    }
    
    // Check Echo connection
    if (typeof window.Echo === 'undefined') {
        console.error('Echo is not defined! Reverb not initialized properly.');
        updateConnectionStatus(false);
        return;
    }
    
    // Monitor connection status
    window.Echo.connector.pusher.connection.bind('connected', function() {
        console.log('Reverb connected!');
        updateConnectionStatus(true);
    });
    
    window.Echo.connector.pusher.connection.bind('disconnected', function() {
        console.warn('Reverb disconnected!');
        updateConnectionStatus(false);
    });
    
    window.Echo.connector.pusher.connection.bind('error', function(err) {
        console.error('Reverb error:', err);
        updateConnectionStatus(false);
    });
    
    console.log('Echo is ready, subscribing to scores channel...');
    
    // Listen for score updates
    window.Echo.channel('scores')
        .listen('.score.updated', function(data) {
            console.log('Score updated received:', data);
            updateMatchScore(data);
        })
        .listen('.match.created', function(data) {
            console.log('Match created received:', data);
            addNewMatch(data);
        })
        .listen('.match.status.updated', function(data) {
            console.log('Match status updated received:', data);
            updateMatchStatus(data);
        });
    
    console.log('Echo listeners registered successfully');

    // Function to update match score in UI
    function updateMatchScore(data) {
        const matchCard = document.querySelector(`[data-id="${data.id}"]`);
        if (matchCard) {
            // Jika status finished, hapus dari dashboard dengan animasi
            if (data.status === 'finished') {
                console.log('Match finished, removing from dashboard:', data.id);
                
                // Animasi fade out
                matchCard.style.transition = 'all 0.5s ease';
                matchCard.style.opacity = '0';
                matchCard.style.transform = 'scale(0.8)';
                
                // Hapus dari DOM setelah animasi
                setTimeout(() => {
                    matchCard.remove();
                    
                    // Cek jika tidak ada pertandingan lagi, tampilkan pesan kosong
                    const remainingCards = document.querySelectorAll('.match-card');
                    if (remainingCards.length === 0) {
                        showEmptyState();
                    }
                }, 500);
                
                showNotification('Pertandingan selesai! Dipindahkan ke history.', 'success');
                return;
            }
            
            // Update scores with animation
            const scoreAElement = matchCard.querySelector('.score-a');
            const scoreBElement = matchCard.querySelector('.score-b');
            
            if (scoreAElement) {
                scoreAElement.textContent = data.score_a;
                scoreAElement.style.transition = 'all 0.3s ease';
                scoreAElement.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    scoreAElement.style.transform = 'scale(1)';
                }, 300);
            }
            
            if (scoreBElement) {
                scoreBElement.textContent = data.score_b;
                scoreBElement.style.transition = 'all 0.3s ease';
                scoreBElement.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    scoreBElement.style.transform = 'scale(1)';
                }, 300);
            }
            
            // Update status badge if needed
            updateMatchStatus(matchCard, data.status);
            
            // Show notification
            showNotification('Skor diperbarui!', 'success');
        }
    }
    
    // Function to show empty state when no matches
    function showEmptyState() {
        const matchContainer = document.getElementById('matchContainer');
        if (matchContainer && matchContainer.children.length === 0) {
            const parent = matchContainer.parentElement;
            parent.innerHTML = `
                <div class="card border-0 py-5 text-center" style="background: rgba(255,255,255,0.02); border-radius: 24px;">
                    <div class="card-body">
                        <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                            style="width: 80px; height: 80px; background: rgba(255,255,255,0.05) !important;">
                            <i class="bi bi-calendar-x text-muted h2 mb-0"></i>
                        </div>
                        <h5 class="font-weight-bold text-white">Tidak Ada Pertandingan</h5>
                        <p class="text-muted mx-auto mb-0" style="max-width: 400px;">Semua pertandingan sudah selesai. Cek halaman History untuk melihat hasil.</p>
                    </div>
                </div>
            `;
        }
    }
    
    // Function to update match status
    function updateMatchStatus(data) {
        const matchCard = document.querySelector(`[data-id="${data.id}"]`);
        if (matchCard) {
            const badgeContainer = matchCard.querySelector('.badge-live-container');
            if (badgeContainer) {
                if (data.status === 'live') {
                    badgeContainer.innerHTML = `
                        <div class="badge-live">
                            <span class="live-dot"></span> LIVE
                        </div>
                    `;
                    
                    // Add flash animation to card
                    matchCard.style.transition = 'all 0.5s ease';
                    matchCard.style.boxShadow = '0 0 30px rgba(239, 68, 68, 0.5)';
                    setTimeout(() => {
                        matchCard.style.boxShadow = '';
                    }, 2000);
                } else if (data.status === 'scheduled') {
                    badgeContainer.innerHTML = `
                        <span class="badge badge-dark px-3 py-1 text-uppercase" style="border-radius: 100px; background: rgba(255,255,255,0.05);">
                            Terjadwal
                        </span>
                    `;
                } else if (data.status === 'finished') {
                    badgeContainer.innerHTML = `
                        <span class="badge badge-success px-3 py-1 text-uppercase" style="border-radius: 100px;">
                            Selesai
                        </span>
                    `;
                    
                    // Hapus dari dashboard setelah 2 detik (kasih waktu user lihat hasil)
                    setTimeout(() => {
                        console.log('Match finished, removing from dashboard:', data.id);
                        matchCard.style.transition = 'all 0.5s ease';
                        matchCard.style.opacity = '0';
                        matchCard.style.transform = 'scale(0.8)';
                        
                        setTimeout(() => {
                            matchCard.remove();
                            const remainingCards = document.querySelectorAll('.match-card');
                            if (remainingCards.length === 0) {
                                showEmptyState();
                            }
                        }, 500);
                        
                        showNotification('Pertandingan selesai! Dipindahkan ke history.', 'success');
                    }, 2000);
                }
                
                console.log('Status updated for match', data.id, 'to', data.status);
            }
        }
    }
    
    // Function to add new match to UI
    function addNewMatch(data) {
        const matchContainer = document.getElementById('matchContainer');
        if (matchContainer) {
            // Check if match already exists
            if (document.querySelector(`[data-id="${data.id}"]`)) {
                return;
            }
            
            // Create new match card HTML
            const newMatchHTML = `
                <div class="col-md-6 col-xl-4 mb-4 match-card" data-id="${data.id}">
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 24px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border) !important; transition: all 0.3s ease; animation: slideInUp 0.5s ease;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <span class="badge badge-primary px-3 py-1" style="border-radius: 100px;">
                                    <i class="bi ${data.sport_icon} mr-2"></i>
                                    ${data.sport_nama}
                                </span>
                                <div class="badge-live-container">
                                    ${data.status === 'live' ? `
                                        <div class="badge-live">
                                            <span class="live-dot"></span> LIVE
                                        </div>
                                    ` : `
                                        <span class="badge badge-dark px-3 py-1 text-uppercase" style="border-radius: 100px; background: rgba(255,255,255,0.05);">
                                            Terjadwal
                                        </span>
                                    `}
                                </div>
                            </div>

                            <div class="row text-center align-items-center py-3">
                                <div class="col-5">
                                    <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">${data.team_a_name}</h4>
                                    <div class="display-4 font-weight-bold text-white score-a">${data.score_a}</div>
                                </div>
                                <div class="col-2 p-0">
                                    <div class="text-muted font-weight-bold small">VS</div>
                                </div>
                                <div class="col-5">
                                    <h4 class="h6 font-weight-bold text-truncate mb-3 text-white">${data.team_b_name}</h4>
                                    <div class="display-4 font-weight-bold text-white score-b">${data.score_b}</div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-top border-secondary d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.05) !important;">
                                <div class="small text-muted">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-geo-alt mr-2 text-primary"></i> ${data.lokasi}
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar3 mr-2 text-primary"></i>
                                        ${data.waktu_tanding}
                                    </div>
                                </div>
                                <a href="${data.detail_url}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                    Detail <i class="bi bi-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add new match to the beginning of container
            matchContainer.insertAdjacentHTML('afterbegin', newMatchHTML);
            
            // Show notification
            showNotification('Pertandingan baru ditambahkan!', 'info');
        }
    }
    
    // Function to update match status
    function updateMatchStatus(matchCard, status) {
        const statusContainer = matchCard.querySelector('.badge-live-container');
        if (statusContainer) {
            if (status === 'live') {
                statusContainer.innerHTML = `
                    <div class="badge-live">
                        <span class="live-dot"></span> LIVE
                    </div>
                `;
            } else {
                statusContainer.innerHTML = `
                    <span class="badge badge-dark px-3 py-1 text-uppercase" style="border-radius: 100px; background: rgba(255,255,255,0.05);">
                        Terjadwal
                    </span>
                `;
            }
        }
    }
    
    // Function to show notifications
    function showNotification(message, type = 'info') {
        const colors = {
            success: '#10b981',
            info: '#3b82f6',
            warning: '#f59e0b',
            error: '#ef4444'
        };
        
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 right-0 m-3 p-3 text-white rounded shadow-lg';
        toast.style.cssText = `
            background: ${colors[type] || colors.info};
            z-index: 9999;
            animation: slideInRight 0.3s ease;
            min-width: 250px;
        `;
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-bell-fill mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection
