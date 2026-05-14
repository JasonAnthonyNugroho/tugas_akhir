@extends('layouts.app')

@section('title', $tournament->name . ' - Bracket')

@section('content')
<div class="container py-4">
    {{-- Back Button --}}
    <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm mb-4">
        <i class="bi bi-arrow-left mr-2"></i>Kembali ke Dashboard
    </a>

    {{-- Header --}}
    <div class="mb-4">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="bg-gradient rounded-circle p-3 d-flex align-items-center justify-content-center"
                 style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1, #a855f7) !important;">
                <i class="bi {{ $tournament->sport->icon ?? 'bi-trophy' }} text-white h4 mb-0"></i>
            </div>
            <div>
                <h2 class="font-weight-bold text-white mb-1">{{ $tournament->name }}</h2>
                <p class="text-muted mb-0">
                    <span class="badge badge-primary mr-2">{{ $tournament->sport->nama_sport }}</span>
                    <span class="badge badge-secondary">{{ $tournament->year }}</span>
                    @if($tournament->start_date && $tournament->end_date)
                        <span class="badge badge-info ml-2">
                            <i class="bi bi-calendar mr-1"></i>
                            {{ \Carbon\Carbon::parse($tournament->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($tournament->end_date)->format('d M Y') }}
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-dark border-secondary text-center">
                <div class="card-body py-3">
                    <div class="h4 text-primary mb-1">{{ $tournament->teams->count() }}</div>
                    <small class="text-muted">Tim Peserta</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-dark border-secondary text-center">
                <div class="card-body py-3">
                    <div class="h4 text-success mb-1">{{ $tournament->pertandingans->where('status', 'finished')->count() }}</div>
                    <small class="text-muted">Match Selesai</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-dark border-secondary text-center">
                <div class="card-body py-3">
                    <div class="h4 text-warning mb-1">{{ $tournament->pertandingans->where('status', 'scheduled')->count() }}</div>
                    <small class="text-muted">Belum Dimulai</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-dark border-secondary text-center">
                <div class="card-body py-3">
                    <div class="h4 text-info mb-1">{{ $tournament->pertandingans->where('status', 'live')->count() }}</div>
                    <small class="text-muted">Sedang Live</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Teams List --}}
    <div class="card border-0 mb-4" style="background: rgba(255,255,255,0.03); border-radius: 16px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="text-white font-weight-bold mb-0">
                <i class="bi bi-people mr-2"></i>Tim Peserta
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                @foreach($tournament->teams as $team)
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-2">
                        <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.05);">
                            <div class="d-flex align-items-center justify-content-center mr-2 text-white font-weight-bold"
                                 style="width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 8px; font-size: 0.85rem;">
                                {{ strtoupper(substr($team->name, 0, 1)) }}
                            </div>
                            <div class="text-white font-weight-bold small text-truncate">{{ $team->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bracket Visualization --}}
    <div class="card border-0 mb-4" style="background: rgba(255,255,255,0.03); border-radius: 16px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="text-white font-weight-bold mb-0">
                <i class="bi bi-diagram-3 mr-2"></i>Tournament Bracket
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="bracket-wrapper" style="overflow-x: auto;">
                <div class="bracket-tree d-flex gap-4">
                    @foreach($rounds as $roundNum => $matches)
                        <div class="bracket-column" style="min-width: 220px;">
                            <div class="text-center text-muted font-weight-bold text-uppercase mb-3" style="font-size: 0.875rem; border-bottom: 2px solid rgba(99, 102, 241, 0.3); padding-bottom: 10px;">
                                {{ $matches->first()->babak }}
                            </div>
                            
                            <div class="d-flex flex-column gap-3">
                                @foreach($matches->sortBy('match_number') as $match)
                                    <div class="card border-0 bracket-match-card" data-match-id="{{ $match->id }}" style="background: rgba(255,255,255,0.05); border-radius: 8px; overflow: hidden;
                                            @if($match->status === 'live') border: 1px solid #ef4444 !important; @endif">
                                        {{-- Header dengan Tanggal --}}
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="background: rgba(255,255,255,0.03); gap: 8px;">
                                            <div class="text-center flex-grow-1" style="font-size: 0.7rem; color: #94a3b8;">
                                                @if($match->match_date)
                                                    <i class="bi bi-clock mr-1" style="font-size: 0.65rem;"></i>
                                                    {{ $match->match_date->format('d M, H:i') }}
                                                @else
                                                    <span class="text-muted">TBA</span>
                                                @endif
                                            </div>
                                            @if($match->status === 'live')
                                                <span class="badge badge-danger flex-shrink-0" style="font-size: 0.65rem; padding: 2px 6px;">LIVE</span>
                                            @elseif($match->status === 'finished')
                                                <span class="badge badge-success flex-shrink-0" style="font-size: 0.65rem; padding: 2px 6px;">DONE</span>
                                            @else
                                                <span class="badge badge-secondary flex-shrink-0" style="font-size: 0.65rem; padding: 2px 6px;">VS</span>
                                            @endif
                                        </div>
                                        
                                        {{-- Team A --}}
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2" 
                                             style="border-bottom: 1px solid rgba(255,255,255,0.05);
                                                    @if($match->winner_id == $match->team_a_id) background: rgba(16, 185, 129, 0.15); @endif">
                                            <span class="text-white font-weight-bold" style="font-size: 0.875rem;">
                                                {{ $match->teamA?->name ?? 'TBD' }}
                                                @if($match->winner_id == $match->team_a_id)
                                                    <i class="bi bi-trophy-fill text-success ml-2" style="font-size: 0.75rem;"></i>
                                                @endif
                                            </span>
                                            <span class="font-weight-bold" 
                                                  style="min-width: 30px; text-align: center; padding: 4px 8px; background: rgba(255,255,255,0.05); border-radius: 4px;
                                                         @if($match->winner_id == $match->team_a_id) color: #10b981; @else color: #94a3b8; @endif">
                                                @if($match->status !== 'scheduled')
                                                    {{ $match->score_a }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        
                                        {{-- Team B --}}
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2"
                                             style="@if($match->winner_id == $match->team_b_id) background: rgba(16, 185, 129, 0.15); @endif">
                                            <span class="text-white font-weight-bold" style="font-size: 0.875rem;">
                                                {{ $match->teamB?->name ?? 'TBD' }}
                                                @if($match->winner_id == $match->team_b_id)
                                                    <i class="bi bi-trophy-fill text-success ml-2" style="font-size: 0.75rem;"></i>
                                                @endif
                                            </span>
                                            <span class="font-weight-bold"
                                                  style="min-width: 30px; text-align: center; padding: 4px 8px; background: rgba(255,255,255,0.05); border-radius: 4px;
                                                         @if($match->winner_id == $match->team_b_id) color: #10b981; @else color: #94a3b8; @endif">
                                                @if($match->status !== 'scheduled')
                                                    {{ $match->score_b }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- 3rd Place Match --}}
            @if($thirdPlaceMatch)
                <div class="mt-4">
                    <h6 class="text-white font-weight-bold mb-3">
                        <i class="bi bi-award text-warning mr-2"></i>Perebutan Juara 3 (Bronze Match)
                    </h6>
                    <div class="card border-0 bracket-match-card" data-match-id="{{ $thirdPlaceMatch->id }}" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3) !important; border-radius: 8px; max-width: 300px;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="background: rgba(245, 158, 11, 0.1);">
                            <small class="text-warning font-weight-bold" style="font-size: 0.7rem;">BRONZE</small>
                            @if($thirdPlaceMatch->status === 'live')
                                <span class="badge badge-danger" style="font-size: 0.65rem;">LIVE</span>
                            @elseif($thirdPlaceMatch->status === 'finished')
                                <span class="badge badge-success" style="font-size: 0.65rem;">DONE</span>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center px-3 py-2" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <span class="text-white font-weight-bold" style="font-size: 0.875rem;">
                                {{ $thirdPlaceMatch->teamA?->name ?? 'TBD' }}
                                @if($thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_a_id)
                                    <i class="bi bi-trophy-fill text-warning ml-2" style="font-size: 0.75rem;"></i>
                                @endif
                            </span>
                            <span class="text-warning font-weight-bold" style="min-width: 30px; text-align: center; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px;">
                                @if($thirdPlaceMatch->status !== 'scheduled'){{ $thirdPlaceMatch->score_a }}@else - @endif
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center px-3 py-2">
                            <span class="text-white font-weight-bold" style="font-size: 0.875rem;">
                                {{ $thirdPlaceMatch->teamB?->name ?? 'TBD' }}
                                @if($thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_b_id)
                                    <i class="bi bi-trophy-fill text-warning ml-2" style="font-size: 0.75rem;"></i>
                                @endif
                            </span>
                            <span class="text-warning font-weight-bold" style="min-width: 30px; text-align: center; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px;">
                                @if($thirdPlaceMatch->status !== 'scheduled'){{ $thirdPlaceMatch->score_b }}@else - @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer Note --}}
    <div class="text-center text-muted">
        <small>Rector Cup {{ $tournament->year }} &bull; {{ $tournament->sport->nama_sport }}</small>
    </div>
</div>
@endsection

@section('styles')
<style>
    .bracket-column {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .gap-4 { gap: 2rem; }
    .gap-3 { gap: 1rem; }
    @keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tournamentId = {{ $tournament->id }};
    var lastSnapshot = '';
    var pollInterval = null;

    function showToast(msg) {
        var t = document.createElement('div');
        t.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;background:#10b981;color:#fff;padding:12px 20px;border-radius:12px;font-size:0.85rem;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:slideIn .3s ease';
        t.innerHTML = '<i class="bi bi-bell-fill mr-2"></i>' + msg;
        document.body.appendChild(t);
        setTimeout(function(){ t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(function(){t.remove()},300); }, 3000);
    }

    function poll() {
        fetch('/api/tournament/' + tournamentId + '/matches')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var snapshot = JSON.stringify(data.matches.map(function(m) {
                    return [m.id, m.score_a, m.score_b, m.status, m.team_a, m.team_b, m.winner_id];
                }));

                if (lastSnapshot === '') {
                    lastSnapshot = snapshot;
                    return;
                }

                if (snapshot !== lastSnapshot) {
                    lastSnapshot = snapshot;
                    showToast('Bracket diperbarui!');

                    // Reload page content seamlessly
                    fetch(window.location.href)
                        .then(function(r) { return r.text(); })
                        .then(function(html) {
                            var parser = new DOMParser();
                            var newDoc = parser.parseFromString(html, 'text/html');

                            // Swap bracket
                            var oldBracket = document.querySelector('.bracket-wrapper');
                            var newBracket = newDoc.querySelector('.bracket-wrapper');
                            if (oldBracket && newBracket) oldBracket.innerHTML = newBracket.innerHTML;

                            // Swap 3rd place match
                            var oldThird = document.querySelector('.bi-award');
                            var newThird = newDoc.querySelector('.bi-award');
                            if (oldThird && newThird) {
                                var oldParent = oldThird.closest('.mt-4');
                                var newParent = newThird.closest('.mt-4');
                                if (oldParent && newParent) oldParent.innerHTML = newParent.innerHTML;
                            }

                            // Swap stat cards
                            var oldStats = document.querySelectorAll('.card.bg-dark.border-secondary');
                            var newStats = newDoc.querySelectorAll('.card.bg-dark.border-secondary');
                            oldStats.forEach(function(el, i) {
                                if (newStats[i]) el.innerHTML = newStats[i].innerHTML;
                            });
                        })
                        .catch(function(e) { console.warn('Reload error:', e); });
                }
            })
            .catch(function(err) { console.warn('Poll error:', err); });
    }

    poll();
    pollInterval = setInterval(poll, 5000);

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(pollInterval);
        } else {
            poll();
            pollInterval = setInterval(poll, 5000);
        }
    });
});
</script>
@endsection
