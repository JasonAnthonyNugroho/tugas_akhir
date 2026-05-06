@extends('layouts.admin')

@section('title', 'Bracket: ' . $tournament->name)

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold text-white mb-1">
                <i class="bi bi-trophy mr-2"></i>{{ $tournament->name }}
            </h2>
            <p class="text-muted mb-0">
                <span class="badge badge-primary mr-2">{{ $tournament->sport->nama_sport }}</span>
                <span class="badge badge-secondary">{{ $tournament->year }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tournament.bracket.builder') }}" class="btn btn-outline-light">
                <i class="bi bi-plus-lg mr-2"></i>Buat Baru
            </a>
            <form action="{{ route('pertandingan.reroll-bracket', $tournament) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Acak ulang bracket? Tim akan di-random ulang.')">
                    <i class="bi bi-shuffle mr-2"></i>Reroll
                </button>
            </form>
            <form action="{{ route('pertandingan.delete-tournament', $tournament) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus tournament ini?')">
                    <i class="bi bi-trash mr-2"></i>Hapus
                </button>
            </form>
        </div>
    </div>

    {{-- Tournament Stats --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <div class="h3 text-primary mb-1">{{ $tournament->teams->count() }}</div>
                    <small class="text-muted">Tim Peserta</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <div class="h3 text-info mb-1">{{ $tournament->pertandingans->where('status', 'finished')->count() }}</div>
                    <small class="text-muted">Match Selesai</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <div class="h3 text-warning mb-1">{{ $tournament->pertandingans->where('status', 'live')->count() }}</div>
                    <small class="text-muted">Sedang Live</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark border-secondary">
                <div class="card-body text-center">
                    <div class="h3 text-success mb-1">{{ $tournament->pertandingans->where('round', 1)->whereNull('winner_id')->count() }}</div>
                    <small class="text-muted">Match Tersisa</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Bracket Visualization --}}
    <div class="card border-0 mb-4" style="background: rgba(255,255,255,0.03); border-radius: 20px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="text-white font-weight-bold mb-0">
                <i class="bi bi-diagram-3 mr-2"></i>Tournament Bracket
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="bracket-wrapper">
                <div class="bracket-tree" id="bracketTree">
                    @foreach($rounds as $roundNum => $matches)
                        <div class="bracket-column" data-round="{{ $roundNum }}">
                            <div class="round-title">{{ $matches->first()->babak }}</div>
                            
                            <div class="round-matches">
                                @foreach($matches->sortBy('match_number') as $match)
                                    <div class="bracket-match-card {{ $match->winner_id ? 'completed' : '' }} {{ $match->status === 'live' ? 'live' : '' }}"
                                         data-match-id="{{ $match->id }}">
                                        
                                        {{-- Match Header --}}
                                        <div class="match-header">
                                            <small class="match-number">M{{ $match->match_number }}</small>
                                            @if($match->status === 'live')
                                                <span class="live-badge">LIVE</span>
                                            @elseif($match->status === 'finished')
                                                <span class="finished-badge">DONE</span>
                                            @endif
                                        </div>
                                        
                                        {{-- Team A --}}
                                        <div class="team-row {{ $match->winner_id == $match->team_a_id ? 'winner' : ($match->winner_id ? 'loser' : '') }}
                                                    {{ $match->team_a_id ? '' : 'tbd' }}
                                                    {{ !$match->team_a_id && $match->team_b_id ? 'bye' : '' }}">
                                            <div class="team-info">
                                                @if($match->team_a_id)
                                                    <span class="team-name">{{ $match->teamA->name }}</span>
                                                @else
                                                    <span class="team-name tbd">TBD</span>
                                                @endif
                                            </div>
                                            <div class="team-score">
                                                @if($match->status !== 'scheduled')
                                                    {{ $match->score_a }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Team B --}}
                                        <div class="team-row {{ $match->winner_id == $match->team_b_id ? 'winner' : ($match->winner_id ? 'loser' : '') }}
                                                    {{ $match->team_b_id ? '' : 'tbd' }}
                                                    {{ !$match->team_b_id && $match->team_a_id ? 'bye' : '' }}">
                                            <div class="team-info">
                                                @if($match->team_b_id)
                                                    <span class="team-name">{{ $match->teamB->name }}</span>
                                                @else
                                                    <span class="team-name tbd">TBD</span>
                                                @endif
                                            </div>
                                            <div class="team-score">
                                                @if($match->status !== 'scheduled')
                                                    {{ $match->score_b }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Match Actions --}}
                                        <div class="match-actions">
                                            @if($match->status === 'scheduled' && $match->team_a_id && $match->team_b_id)
                                                <form action="{{ route('pertandingan.bulk-live') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="match_ids[]" value="{{ $match->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                                        <i class="bi bi-play-fill mr-1"></i>Start
                                                    </button>
                                                </form>
                                            @elseif($match->status === 'live' || $match->status === 'finished')
                                                <a href="{{ route('admin.skor') }}" class="btn btn-sm btn-primary w-100">
                                                    <i class="bi bi-pencil mr-1"></i>Manage
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-secondary w-100" disabled>
                                                    <i class="bi bi-clock mr-1"></i>Wait
                                                </button>
                                            @endif
                                        </div>
                                        
                                        {{-- Connector Lines (for visual) --}}
                                        @if($roundNum < $rounds->keys()->last())
                                            <div class="connector-right"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- 3rd Place Match (if exists) --}}
            @if($thirdPlaceMatch)
                <div class="mt-4">
                    <h6 class="text-white font-weight-bold mb-3">
                        <i class="bi bi-award mr-2"></i>Perebutan Juara 3
                    </h6>
                    <div class="bracket-match-card bronze-match" style="max-width: 300px;">
                        <div class="match-header">
                            <small class="match-number">Bronze</small>
                            @if($thirdPlaceMatch->status === 'live')
                                <span class="live-badge">LIVE</span>
                            @elseif($thirdPlaceMatch->status === 'finished')
                                <span class="finished-badge">DONE</span>
                            @endif
                        </div>
                        
                        <div class="team-row {{ $thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_a_id ? 'winner' : ($thirdPlaceMatch->winner_id ? 'loser' : '') }}
                                    {{ $thirdPlaceMatch->team_a_id ? '' : 'tbd' }}">
                            <div class="team-info">
                                @if($thirdPlaceMatch->team_a_id)
                                    <span class="team-name">{{ $thirdPlaceMatch->teamA->name }}</span>
                                @else
                                    <span class="team-name tbd">TBD</span>
                                @endif
                            </div>
                            <div class="team-score">
                                @if($thirdPlaceMatch->status !== 'scheduled')
                                    {{ $thirdPlaceMatch->score_a }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        
                        <div class="team-row {{ $thirdPlaceMatch->winner_id == $thirdPlaceMatch->team_b_id ? 'winner' : ($thirdPlaceMatch->winner_id ? 'loser' : '') }}
                                    {{ $thirdPlaceMatch->team_b_id ? '' : 'tbd' }}">
                            <div class="team-info">
                                @if($thirdPlaceMatch->team_b_id)
                                    <span class="team-name">{{ $thirdPlaceMatch->teamB->name }}</span>
                                @else
                                    <span class="team-name tbd">TBD</span>
                                @endif
                            </div>
                            <div class="team-score">
                                @if($thirdPlaceMatch->status !== 'scheduled')
                                    {{ $thirdPlaceMatch->score_b }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        
                        <div class="match-actions">
                            @if($thirdPlaceMatch->status === 'scheduled' && $thirdPlaceMatch->team_a_id && $thirdPlaceMatch->team_b_id)
                                <form action="{{ route('pertandingan.bulk-live') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="match_ids[]" value="{{ $thirdPlaceMatch->id }}">
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-play-fill mr-1"></i>Start
                                    </button>
                                </form>
                            @elseif($thirdPlaceMatch->status === 'live' || $thirdPlaceMatch->status === 'finished')
                                <a href="{{ route('admin.skor') }}" class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-pencil mr-1"></i>Manage
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Participating Teams --}}
    <div class="card border-0" style="background: rgba(255,255,255,0.03); border-radius: 20px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="text-white font-weight-bold mb-0">
                <i class="bi bi-people mr-2"></i>Tim Peserta
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                @foreach($tournament->teams as $team)
                    <div class="col-md-3 col-lg-2 mb-3">
                        <div class="card bg-dark border-secondary">
                            <div class="card-body p-3 text-center">
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1, #a855f7) !important;">
                                    <i class="bi bi-shield text-white"></i>
                                </div>
                                <div class="text-white font-weight-bold small">{{ $team->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $team->prodi }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    {{-- Bracket Tree Styles --}}
    .bracket-wrapper {
        overflow-x: auto;
        padding: 20px 0;
    }
    
    .bracket-tree {
        display: flex;
        gap: 40px;
        min-width: max-content;
    }
    
    .bracket-column {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 220px;
    }
    
    .round-title {
        text-align: center;
        color: #94a3b8;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid rgba(99, 102, 241, 0.3);
    }
    
    .round-matches {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    {{-- Match Card Styles --}}
    .bracket-match-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .bracket-match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    .bracket-match-card.completed {
        border-color: rgba(16, 185, 129, 0.3);
    }
    
    .bracket-match-card.live {
        border-color: #ef4444;
        animation: livePulse 2s infinite;
    }
    
    @keyframes livePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    }
    
    .match-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 10px;
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .match-number {
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .live-badge {
        background: #ef4444;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        animation: blink 1s infinite;
    }
    
    .finished-badge {
        background: #10b981;
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    {{-- Team Row Styles --}}
    .team-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.2s;
    }
    
    .team-row:last-child {
        border-bottom: none;
    }
    
    .team-row.winner {
        background: rgba(16, 185, 129, 0.15);
    }
    
    .team-row.winner .team-name {
        color: #10b981;
        font-weight: 700;
    }
    
    .team-row.winner .team-score {
        color: #10b981;
        font-weight: 700;
    }
    
    .team-row.loser {
        opacity: 0.6;
    }
    
    .team-row.loser .team-score {
        color: #ef4444;
    }
    
    .team-row.tbd {
        background: rgba(255, 255, 255, 0.02);
    }
    
    .team-row.tbd .team-name {
        color: #64748b;
        font-style: italic;
    }
    
    .team-row.bye {
        background: rgba(16, 185, 129, 0.1);
    }
    
    .team-row.bye .team-name::after {
        content: '(BYE)';
        margin-left: 8px;
        font-size: 0.7rem;
        color: #10b981;
    }
    
    .team-info {
        flex: 1;
        min-width: 0;
    }
    
    .team-name {
        color: #f8fafc;
        font-weight: 600;
        font-size: 0.875rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .team-name.tbd {
        color: #64748b;
    }
    
    .team-score {
        color: #94a3b8;
        font-weight: 600;
        font-size: 1rem;
        min-width: 30px;
        text-align: center;
        padding: 4px 8px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
    }
    
    {{-- Match Actions --}}
    .match-actions {
        padding: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .match-actions .btn {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    
    {{-- Bronze Match --}}
    .bronze-match {
        border-color: rgba(245, 158, 11, 0.3);
    }
    
    .bronze-match .match-header {
        background: rgba(245, 158, 11, 0.1);
    }
    
    {{-- Connector Lines --}}
    .connector-right {
        position: absolute;
        right: -20px;
        top: 50%;
        width: 20px;
        height: 2px;
        background: rgba(99, 102, 241, 0.4);
    }
    
    .connector-right::after {
        content: '';
        position: absolute;
        right: 0;
        top: -4px;
        width: 0;
        height: 0;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 8px solid rgba(99, 102, 241, 0.4);
    }
    
    {{-- Responsive --}}
    @media (max-width: 768px) {
        .bracket-tree {
            flex-direction: column;
            gap: 20px;
        }
        
        .bracket-column {
            min-width: 100%;
        }
        
        .round-matches {
            gap: 10px;
        }
    }
</style>
@endsection
