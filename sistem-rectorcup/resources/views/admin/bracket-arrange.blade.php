@extends('layouts.app')

@section('title', 'Arrange Bracket — ' . $tournamentName)

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h2 class="font-weight-bold text-white mb-1">
                <i class="bi bi-shuffle mr-2"></i>Arrange Bracket
            </h2>
            <p class="text-muted mb-0">
                {{ $tournamentName }} &mdash; {{ $sport->nama_sport }} &mdash; {{ $bracketSize }} Tim
            </p>
        </div>
        <a href="{{ route('admin.tournament.bracket.builder') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left mr-1"></i>Kembali
        </a>
    </div>

    {{-- Alert info + progress --}}
    <div class="d-flex align-items-center justify-content-between mb-4 p-3" style="background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2); border-radius: 10px;">
        <div style="color:#a5b4fc;">
            <i class="bi bi-hand-index-thumb mr-2"></i>
            Seret tim dari panel kiri ke slot <strong>Round 1</strong>. Babak berikutnya diisi sistem otomatis.
        </div>
        <div>
            <span class="badge badge-pill px-3 py-2" id="placedCounter" style="background:#1e2540; color:#a5b4fc; font-size:0.85rem;">
                <i class="bi bi-check2-circle mr-1"></i><span id="placedCount">0</span>/{{ $bracketSize }} ditempatkan
            </span>
        </div>
    </div>

    <div class="row">

        {{-- Panel Kiri: Tim Tersedia --}}
        <div class="col-md-3 mb-4">
            <div class="card border-0 h-100" style="background: rgba(255,255,255,0.04); border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pb-2 pt-3 px-3 d-flex justify-content-between align-items-center">
                    <span class="text-white font-weight-bold">
                        <i class="bi bi-people mr-1"></i>Tim Tersedia
                    </span>
                    <span class="badge badge-primary" id="poolCount">{{ count($selectedTeams) }}</span>
                </div>
                <div class="card-body p-2" id="availableTeams" style="max-height: 560px; overflow-y: auto;">
                    @foreach($selectedTeams as $team)
                        <div class="team-card"
                             draggable="true"
                             data-team-id="{{ $team->id }}"
                             data-team-name="{{ $team->name }}">
                            <div class="team-card-inner">
                                <i class="bi bi-grip-vertical drag-handle"></i>
                                <div class="team-avatar">{{ strtoupper(substr($team->name, 0, 1)) }}</div>
                                <span class="team-name">{{ $team->name }}</span>
                                <i class="bi bi-check-circle-fill placed-check"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-transparent border-0 px-3 pb-3 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-warning flex-fill" id="shuffleBtn">
                        <i class="bi bi-shuffle mr-1"></i>Random
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="resetBtn">
                        <i class="bi bi-arrow-counterclockwise mr-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>

        {{-- Panel Kanan: Bracket --}}
        <div class="col-md-9">
            <div class="card border-0" style="background: rgba(255,255,255,0.03); border-radius: 12px;">
                <div class="card-body p-3">
                    <div id="bracketContainer" class="bracket-wrap">
                        {{-- Di-generate JS --}}
                    </div>
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div class="mt-3 text-right">
                <button type="button" class="btn btn-success btn-lg px-5" id="saveBracketBtn">
                    <i class="bi bi-check-lg mr-2"></i>Simpan Bracket
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Hidden form submit --}}
<form id="saveBracketForm" action="{{ route('admin.tournament.bracket.store') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="tournament_name" value="{{ $tournamentName }}">
    <input type="hidden" name="sport_id"        value="{{ $sportId }}">
    <input type="hidden" name="bracket_size"    value="{{ $bracketSize }}">
    <input type="hidden" name="keterangan"      value="{{ $keterangan }}">
    <input type="hidden" name="start_date"         value="{{ $startDate }}">
    <input type="hidden" name="end_date"           value="{{ $endDate }}">
    <input type="hidden" name="external_score_url" value="{{ $externalScoreUrl ?? '' }}">
    <input type="hidden" name="arrangement"        id="formArrangement">
</form>
@endsection

@section('styles')
<style>
    /* ── Drag source cards ── */
    .team-card {
        cursor: grab;
        user-select: none;
        margin-bottom: 8px;
    }
    .team-card-inner {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 12px;
        background: #1a1f2e;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        transition: all 0.15s;
    }
    .team-card:hover .team-card-inner {
        transform: translateX(3px);
        border-color: rgba(99,102,241,0.6);
        box-shadow: 0 4px 14px rgba(99,102,241,0.25);
        background: #1f2540;
    }
    .team-card.dragging .team-card-inner {
        opacity: 0.4;
        cursor: grabbing;
        border-style: dashed;
    }
    .team-card.placed .team-card-inner {
        opacity: 0.55;
        background: rgba(16,185,129,0.08);
        border-color: rgba(16,185,129,0.4);
    }
    .team-card.placed:hover .team-card-inner {
        opacity: 0.85;
        transform: translateX(2px);
    }
    .drag-handle {
        color: #475569;
        font-size: 1rem;
        cursor: grab;
    }
    .team-avatar {
        width: 30px; height: 30px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        box-shadow: 0 2px 6px rgba(99,102,241,0.4);
    }
    .team-card .team-name {
        color: #e2e8f0;
        font-weight: 600;
        font-size: 0.85rem;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .placed-check {
        display: none;
        color: #10b981;
        font-size: 1rem;
    }
    .team-card.placed .placed-check { display: inline; }
    .team-card.placed .drag-handle { display: none; }

    /* ── Bracket layout (HORIZONTAL tree) ── */
    .bracket-wrap {
        overflow-x: auto;
        padding: 24px 20px;
        background: rgba(0,0,0,0.2);
        border-radius: 14px;
        min-height: 520px;
    }
    .bracket-wrap::-webkit-scrollbar { height: 10px; }
    .bracket-wrap::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 10px; }
    .bracket-wrap::-webkit-scrollbar-thumb {
        background: rgba(99,102,241,0.5);
        border-radius: 10px;
    }
    .bracket-wrap::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,0.75); }

    .bracket-rounds {
        display: inline-flex;
        gap: 50px;
        align-items: stretch;
        min-width: 100%;
    }
    .bracket-round {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        flex: 0 0 auto;
        min-width: 220px;
    }
    /* Round header pill */
    .bracket-round-title {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: linear-gradient(135deg, rgba(99,102,241,0.18), rgba(168,85,247,0.12));
        border: 1px solid rgba(99,102,241,0.3);
        color: #c7d2fe;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 8px 10px;
        border-radius: 8px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(99,102,241,0.15);
    }
    .bracket-round-title i { font-size: 0.85rem; }

    .bracket-round-matches {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        flex: 1;
        gap: 18px;
    }

    /* ── Match card ── */
    .bk-match {
        position: relative;
        background: #161b2a;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        z-index: 2;
    }
    .bk-match > .bk-match-header { border-top-left-radius: 9px; border-top-right-radius: 9px; }
    .bk-match > .bk-slot:last-child, .bk-match > .bk-slot-static:last-child {
        border-bottom-left-radius: 9px; border-bottom-right-radius: 9px;
        overflow: hidden;
    }
    .bk-match:hover {
        border-color: rgba(99,102,241,0.4);
        box-shadow: 0 4px 14px rgba(99,102,241,0.2);
        transform: translateY(-1px);
    }
    .bk-match.match-ready {
        border-color: rgba(16,185,129,0.45);
        box-shadow: 0 0 16px rgba(16,185,129,0.12);
    }

    /* ── Tree connector lines ── */
    /* Garis masuk dari kiri (round ≥ 2): horizontal dari bracket sebelumnya */
    .bracket-round:not(:first-child) .bk-match::before {
        content: "";
        position: absolute;
        left: -25px;
        top: 50%;
        width: 25px;
        height: 2px;
        background: rgba(99,102,241,0.35);
        z-index: 1;
    }
    /* Garis keluar: L-shape pakai border.
       Match ganjil (atas) → horizontal kanan lalu turun ke merge point.
       Match genap (bawah) → horizontal kanan lalu naik ke merge point. */
    .bracket-round:not(:last-child) .bk-match::after {
        content: "";
        position: absolute;
        right: -25px;
        width: 25px;
        background: transparent;
        z-index: 1;
    }
    .bracket-round:not(:last-child) .bracket-round-matches > .bk-match:nth-child(odd)::after {
        top: 50%;
        bottom: -50%;
        border-top: 2px solid rgba(99,102,241,0.35);
        border-right: 2px solid rgba(99,102,241,0.35);
        border-top-right-radius: 6px;
    }
    .bracket-round:not(:last-child) .bracket-round-matches > .bk-match:nth-child(even)::after {
        top: -50%;
        bottom: 50%;
        border-bottom: 2px solid rgba(99,102,241,0.35);
        border-right: 2px solid rgba(99,102,241,0.35);
        border-bottom-right-radius: 6px;
    }
    /* Highlight ketika match sudah siap */
    .bk-match.match-ready::before { background: rgba(16,185,129,0.55); }
    .bracket-round:not(:last-child) .bracket-round-matches > .bk-match.match-ready:nth-child(odd)::after {
        border-top-color: rgba(16,185,129,0.55);
        border-right-color: rgba(16,185,129,0.55);
    }
    .bracket-round:not(:last-child) .bracket-round-matches > .bk-match.match-ready:nth-child(even)::after {
        border-bottom-color: rgba(16,185,129,0.55);
        border-right-color: rgba(16,185,129,0.55);
    }

    /* Match header bar */
    .bk-match-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #0f1320;
        padding: 6px 10px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .bk-match-header .match-label {
        font-size: 0.66rem;
        font-weight: 700;
        color: #6366f1;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .bk-match-header .match-status {
        font-size: 0.62rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .bk-match-header .match-status .dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #475569;
    }
    .bk-match.match-ready .bk-match-header .match-status {
        color: #10b981;
    }
    .bk-match.match-ready .bk-match-header .match-status .dot {
        background: #10b981;
        box-shadow: 0 0 6px rgba(16,185,129,0.7);
    }

    .bk-divider {
        height: 1px;
        background: rgba(255,255,255,0.06);
        margin: 0 10px;
    }

    /* ── Droppable slot (Round 1) ── */
    .bk-slot {
        display: flex;
        align-items: stretch;
        min-height: 54px;
        cursor: pointer;
        transition: background 0.15s;
        position: relative;
        background: #1a1f2e;
    }
    .bk-slot:hover { background: #1f2540; }

    /* Label index slot (A / B) di paling kiri */
    .bk-slot .slot-index {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        background: #0f1320;
        color: #475569;
        font-size: 0.7rem;
        font-weight: 700;
        border-right: 1px solid rgba(255,255,255,0.06);
        flex-shrink: 0;
    }
    .bk-slot.occupied .slot-index {
        background: #1e1b4b;
        color: #a5b4fc;
    }

    /* Area badge utama */
    .bk-slot .slot-body {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 8px 10px;
        gap: 8px;
        min-width: 0;
    }

    /* saat ada drag aktif: highlight target + pulse */
    body.is-dragging .bk-slot:not(.occupied) {
        background: rgba(99,102,241,0.1);
        animation: pulseGlow 1.3s ease-in-out infinite;
    }
    body.is-dragging .bk-slot:not(.occupied) .slot-badge {
        border-color: rgba(99,102,241,0.9);
        color: #a5b4fc;
    }
    @keyframes pulseGlow {
        0%, 100% { background: rgba(99,102,241,0.08); }
        50%      { background: rgba(99,102,241,0.2); }
    }

    .bk-slot.drag-over {
        background: rgba(99,102,241,0.35) !important;
        animation: none !important;
        box-shadow: inset 0 0 0 2px #6366f1;
    }
    .bk-slot.drag-over .slot-badge {
        background: #6366f1 !important;
        border: 1px solid #a5b4fc !important;
        color: #fff !important;
        transform: scale(1.03);
    }

    /* badge kosong */
    .bk-slot .slot-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: transparent;
        border: 1.5px dashed rgba(99,102,241,0.35);
        color: #64748b;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.83rem;
        font-weight: 500;
        pointer-events: none;
        transition: all 0.15s;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        justify-content: center;
    }
    .bk-slot:hover .slot-badge {
        border-color: rgba(99,102,241,0.75);
        color: #94a3b8;
    }

    /* badge terisi: team card mini */
    .bk-slot.occupied .slot-badge {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        border: 1px solid transparent;
        color: #fff;
        font-weight: 600;
        justify-content: flex-start;
        box-shadow: 0 2px 6px rgba(79,70,229,0.4);
    }
    .bk-slot.occupied:hover .slot-badge {
        background: linear-gradient(135deg, #6366f1, #818cf8);
    }
    .slot-badge i { font-size: 0.8rem; flex-shrink: 0; }

    /* Mini avatar inisial di slot terisi */
    .slot-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px; height: 18px;
        background: rgba(255,255,255,0.2);
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }

    /* Kolom actions (kanan) */
    .bk-slot .slot-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        border-left: 1px solid rgba(255,255,255,0.06);
        flex-shrink: 0;
    }
    .bk-slot:not(.occupied) .slot-actions {
        opacity: 0;
    }

    /* tombol hapus: real button merah */
    .rm-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px; height: 24px;
        color: #f87171;
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.3);
        border-radius: 6px;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .rm-btn:hover {
        background: #ef4444;
        color: #fff;
        border-color: #ef4444;
        transform: scale(1.1);
    }

    /* ── Static slot (Round 2+) ── */
    .bk-slot-static {
        display: flex;
        align-items: stretch;
        min-height: 54px;
        background: rgba(255,255,255,0.01);
    }
    .bk-slot-static .slot-index {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        background: rgba(0,0,0,0.2);
        color: #334155;
        font-size: 0.7rem;
        font-weight: 700;
        border-right: 1px solid rgba(255,255,255,0.04);
        flex-shrink: 0;
    }
    .bk-slot-static .slot-body {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 8px 10px;
        justify-content: center;
    }
    .bk-slot-static .tbd-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.05);
        color: #334155;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.78rem;
        font-style: italic;
    }
</style>
@endsection

@section('scripts')
<script>
const teamsData  = @json($selectedTeams->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));
const bracketSize = {{ $bracketSize }};
const numRounds   = Math.log2(bracketSize);
let arrangement   = [];

for (let i = 0; i < bracketSize / 2; i++) arrangement[i] = [null, null];

const roundNames = {
    0: 'Final', 1: 'Semi Final', 2: 'Quarter Final', 3: 'Round of 16', 4: 'Round of 32'
};
const roundIcons = {
    0: 'bi-trophy-fill', 1: 'bi-award-fill', 2: 'bi-star-fill', 3: 'bi-diagram-3-fill', 4: 'bi-grid-3x3-gap-fill'
};
function getRoundName(round) {
    const diff = numRounds - round;
    return roundNames[diff] || `Round ${round}`;
}
function getRoundIcon(round) {
    const diff = numRounds - round;
    return roundIcons[diff] || 'bi-diagram-3';
}

/* ── Build bracket HTML ── */
function buildBracket() {
    let html = '<div class="bracket-rounds">';
    for (let round = 1; round <= numRounds; round++) {
        const numMatches = bracketSize / Math.pow(2, round);
        html += `<div class="bracket-round">
                    <div class="bracket-round-title"><i class="bi ${getRoundIcon(round)}"></i>${getRoundName(round)}</div>
                    <div class="bracket-round-matches">`;

        for (let m = 0; m < numMatches; m++) {
            const matchLabel = `Match ${m + 1}`;
            if (round === 1) {
                const tA = teamsData[(m * 2)];
                const tB = teamsData[(m * 2) + 1];
                if (tA) arrangement[m][0] = tA.id;
                if (tB) arrangement[m][1] = tB.id;

                const ready = tA && tB;
                html += `<div class="bk-match ${ready ? 'match-ready' : ''}" id="match-${m}">
                    <div class="bk-match-header">
                        <span class="match-label"><i class="bi bi-trophy"></i>${matchLabel}</span>
                        <span class="match-status"><span class="dot"></span><span class="status-text">${ready ? 'Siap' : 'Menunggu'}</span></span>
                    </div>
                    ${slotDropHtml(m, 'a', tA)}
                    <div class="bk-divider"></div>
                    ${slotDropHtml(m, 'b', tB)}
                </div>`;
            } else {
                html += `<div class="bk-match">
                    <div class="bk-match-header">
                        <span class="match-label"><i class="bi bi-lock"></i>${matchLabel}</span>
                        <span class="match-status"><span class="dot"></span>Otomatis</span>
                    </div>
                    <div class="bk-slot-static">
                        <div class="slot-index">A</div>
                        <div class="slot-body"><span class="tbd-badge"><i class="bi bi-hourglass"></i>Pemenang sebelumnya</span></div>
                    </div>
                    <div class="bk-divider"></div>
                    <div class="bk-slot-static">
                        <div class="slot-index">B</div>
                        <div class="slot-body"><span class="tbd-badge"><i class="bi bi-hourglass"></i>Pemenang sebelumnya</span></div>
                    </div>
                </div>`;
            }
        }
        html += `</div></div>`;
    }
    html += '</div>';
    document.getElementById('bracketContainer').innerHTML = html;
}

function slotDropHtml(idx, side, team) {
    const cls      = team ? 'bk-slot occupied' : 'bk-slot';
    const label    = side === 'a' ? 'A' : 'B';
    const bodyHtml = team
        ? `<span class="slot-badge"><span class="slot-avatar">${team.name.charAt(0).toUpperCase()}</span>${team.name}</span>`
        : `<span class="slot-badge"><i class="bi bi-plus-lg"></i>Drop tim di sini</span>`;
    const actions  = team
        ? `<button type="button" class="rm-btn" onclick="removeTeam(this,${idx},'${side}')" title="Hapus tim"><i class="bi bi-x-lg"></i></button>`
        : '';
    return `<div class="${cls}"
                 data-idx="${idx}" data-side="${side}"
                 ondrop="onDrop(event)" ondragover="onDragOver(event)" ondragleave="onDragLeave(event)">
                <div class="slot-index">${label}</div>
                <div class="slot-body">${bodyHtml}</div>
                <div class="slot-actions">${actions}</div>
            </div>`;
}

/* ── Drag source ── */
document.querySelectorAll('.team-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        e.dataTransfer.setData('teamId',   card.dataset.teamId);
        e.dataTransfer.setData('teamName', card.dataset.teamName);
        card.classList.add('dragging');
        document.body.classList.add('is-dragging');
    });
    card.addEventListener('dragend', () => {
        card.classList.remove('dragging');
        document.body.classList.remove('is-dragging');
    });
});

/* ── Drop handlers ── */
function onDragOver(e) { e.preventDefault(); e.currentTarget.classList.add('drag-over'); }
function onDragLeave(e) { e.currentTarget.classList.remove('drag-over'); }

function onDrop(e) {
    e.preventDefault();
    const slot     = e.currentTarget;
    slot.classList.remove('drag-over');
    document.body.classList.remove('is-dragging');
    const teamId   = e.dataTransfer.getData('teamId');
    const teamName = e.dataTransfer.getData('teamName');
    const idx      = parseInt(slot.dataset.idx);
    const side     = slot.dataset.side;

    /* Swap-aware: kalau slot target sudah terisi, tukar isinya ke slot asal */
    const existingInTarget = arrangement[idx][side === 'a' ? 0 : 1];

    /* Cari slot asal dari tim yang di-drag */
    let sourceIdx = null, sourcePos = null;
    arrangement.forEach((arr, i) => {
        if (arr[0] == teamId) { sourceIdx = i; sourcePos = 0; }
        if (arr[1] == teamId) { sourceIdx = i; sourcePos = 1; }
    });

    if (sourceIdx !== null) {
        /* Tim dipindah dari slot lain → letakkan existing target ke slot asal (swap) */
        arrangement[sourceIdx][sourcePos] = existingInTarget;
        const sourceSlot = document.querySelector(`.bk-slot[data-idx="${sourceIdx}"][data-side="${sourcePos === 0 ? 'a' : 'b'}"]`);
        if (sourceSlot) {
            if (existingInTarget) {
                const swapTeam = teamsData.find(t => t.id == existingInTarget);
                fillSlot(sourceSlot, sourceIdx, sourcePos === 0 ? 'a' : 'b', swapTeam.name);
            } else {
                clearSlot(sourceSlot);
            }
        }
    }

    arrangement[idx][side === 'a' ? 0 : 1] = teamId;
    fillSlot(slot, idx, side, teamName);
    updatePlacedUI();
}

function fillSlot(slot, idx, side, teamName) {
    slot.classList.add('occupied');
    const label = side === 'a' ? 'A' : 'B';
    slot.innerHTML = `
        <div class="slot-index">${label}</div>
        <div class="slot-body"><span class="slot-badge"><span class="slot-avatar">${teamName.charAt(0).toUpperCase()}</span>${teamName}</span></div>
        <div class="slot-actions">
            <button type="button" class="rm-btn" onclick="removeTeam(this,${idx},'${side}')" title="Hapus tim"><i class="bi bi-x-lg"></i></button>
        </div>`;
    updateMatchReadyState(idx);
}

function clearSlot(slot) {
    slot.classList.remove('occupied');
    const label = slot.dataset.side === 'a' ? 'A' : 'B';
    slot.innerHTML = `
        <div class="slot-index">${label}</div>
        <div class="slot-body"><span class="slot-badge"><i class="bi bi-plus-lg"></i>Drop tim di sini</span></div>
        <div class="slot-actions"></div>`;
    updateMatchReadyState(parseInt(slot.dataset.idx));
}

/* Update status siap/menunggu di match header */
function updateMatchReadyState(idx) {
    const match = document.getElementById(`match-${idx}`);
    if (!match) return;
    const ready = arrangement[idx][0] && arrangement[idx][1];
    match.classList.toggle('match-ready', !!ready);
    const statusText = match.querySelector('.status-text');
    if (statusText) statusText.textContent = ready ? 'Siap' : 'Menunggu';
}

function removeTeam(btn, idx, side) {
    arrangement[idx][side === 'a' ? 0 : 1] = null;
    clearSlot(btn.closest('.bk-slot'));
    updatePlacedUI();
}

/* Update panel kiri: tandai tim yang sudah ditempatkan + counter */
function updatePlacedUI() {
    const placedIds = new Set();
    arrangement.forEach(([a, b]) => { if (a) placedIds.add(String(a)); if (b) placedIds.add(String(b)); });
    document.querySelectorAll('.team-card').forEach(card => {
        card.classList.toggle('placed', placedIds.has(card.dataset.teamId));
    });
    document.getElementById('placedCount').textContent = placedIds.size;
}

/* ── Shuffle ── */
document.getElementById('shuffleBtn').addEventListener('click', () => {
    const ids = [...teamsData.map(t => t.id)];
    for (let i = ids.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [ids[i], ids[j]] = [ids[j], ids[i]];
    }
    arrangement = arrangement.map(() => [null, null]);
    let ti = 0;
    document.querySelectorAll('.bk-slot').forEach(slot => {
        if (ti >= ids.length) return;
        const idx  = parseInt(slot.dataset.idx);
        const side = slot.dataset.side;
        const pos  = side === 'a' ? 0 : 1;
        const team = teamsData.find(t => t.id == ids[ti]);
        arrangement[idx][pos] = ids[ti];
        fillSlot(slot, idx, side, team.name);
        ti++;
    });
    updatePlacedUI();
});

/* ── Reset ── */
document.getElementById('resetBtn').addEventListener('click', () => {
    arrangement = arrangement.map(() => [null, null]);
    document.querySelectorAll('.bk-slot').forEach(s => clearSlot(s));
    updatePlacedUI();
});

/* ── Save ── */
document.getElementById('saveBracketBtn').addEventListener('click', () => {
    const filled = arrangement.filter(a => a[0] || a[1]);
    if (!filled.length) {
        Swal.fire({ icon: 'warning', title: 'Bracket Kosong',
            text: 'Isi minimal satu slot dulu!', confirmButtonColor: '#6366f1' });
        return;
    }
    document.getElementById('formArrangement').value = JSON.stringify(arrangement);
    document.getElementById('saveBracketForm').submit();
});

/* ── Init ── */
buildBracket();
updatePlacedUI();

/* Safety: kalau drag dibatalkan di luar drop zone, clear class is-dragging */
document.addEventListener('dragend', () => document.body.classList.remove('is-dragging'));
</script>
@endsection
