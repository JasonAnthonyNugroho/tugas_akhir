@extends('layouts.app')

@section('title', 'Generate Bracket')

@section('content')
<div class="container-fluid py-4">

    {{-- Header + Stepper --}}
    <div class="bb-header mb-4">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="font-weight-bold text-white mb-1">
                    <i class="bi bi-diagram-3 mr-2"></i>Generate Bracket
                </h2>
                <p class="text-muted mb-0">Konfigurasi turnamen dan pilih tim peserta. Lanjut ke atur posisi bracket di langkah berikutnya.</p>
            </div>
            <div class="bb-stepper d-flex align-items-center">
                <div class="bb-step active">
                    <div class="bb-step-num">1</div>
                    <div class="bb-step-label">Konfigurasi & Pilih Tim</div>
                </div>
                <div class="bb-step-line"></div>
                <div class="bb-step">
                    <div class="bb-step-num">2</div>
                    <div class="bb-step-label">Atur Bracket</div>
                </div>
            </div>
        </div>
    </div>

    <form id="bracketConfigForm" action="{{ route('admin.tournament.bracket.arrange') }}" method="POST">
        @csrf

        {{-- ===== Section 1: Info Turnamen ===== --}}
        <div class="bb-section mb-4">
            <div class="bb-section-header">
                <div class="bb-section-icon"><i class="bi bi-info-circle"></i></div>
                <div>
                    <h6 class="text-white font-weight-bold mb-0">Informasi Turnamen</h6>
                    <small class="text-muted">Nama, jadwal, dan keterangan turnamen</small>
                </div>
            </div>
            <div class="bb-section-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="bb-label"><i class="bi bi-trophy mr-1"></i>Nama Tournament</label>
                        <input type="text" name="tournament_name" class="bb-input" placeholder="Contoh: Rector Cup Futsal 2026" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="bb-label"><i class="bi bi-pencil mr-1"></i>Keterangan <span class="text-muted">(opsional)</span></label>
                        <input type="text" name="keterangan" class="bb-input" placeholder="Informasi tambahan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="bb-label"><i class="bi bi-geo-alt mr-1"></i>Lokasi / GOR</label>
                        <input type="text" name="lokasi" class="bb-input" placeholder="Contoh: GOR UKDW">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="bb-label"><i class="bi bi-calendar-event mr-1"></i>Tanggal Mulai</label>
                        <input type="date" name="start_date" id="startDate" class="bb-input" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="bb-label"><i class="bi bi-calendar-check mr-1"></i>Tanggal Selesai</label>
                        <input type="date" name="end_date" id="endDate" class="bb-input" required>
                    </div>
                    <div class="col-12 mb-1">
                        <label class="bb-label">
                            <i class="bi bi-table mr-1"></i>Link Google Sheet Skor <span class="text-muted">(opsional)</span>
                        </label>
                        <input type="url" name="external_score_url" id="externalScoreUrl"
                               class="bb-input" placeholder="https://docs.google.com/spreadsheets/d/...">
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle mr-1"></i>
                            Untuk cabang dengan poin manual seperti <strong>Catur</strong> atau <strong>PUBG Mobile</strong>.
                            Jika diisi, halaman riwayat akan menampilkan tombol link ke sheet (bukan podium juara).
                            Pastikan sheet sudah di-share <em>"Anyone with the link"</em>.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Section 2: Sport (visual picker) ===== --}}
        <div class="bb-section mb-4">
            <div class="bb-section-header">
                <div class="bb-section-icon"><i class="bi bi-controller"></i></div>
                <div>
                    <h6 class="text-white font-weight-bold mb-0">Cabang Olahraga</h6>
                    <small class="text-muted">Pilih satu cabang untuk turnamen ini</small>
                </div>
            </div>
            <div class="bb-section-body">
                <div class="sport-grid">
                    @foreach($sports as $sport)
                        <label class="sport-card">
                            <input type="radio" name="sport_id" value="{{ $sport->id }}" required>
                            <div class="sport-card-inner">
                                <i class="bi {{ $sport->icon ?? 'bi-trophy' }} sport-icon"></i>
                                <span class="sport-name">{{ $sport->nama_sport }}</span>
                                <i class="bi bi-check-circle-fill sport-check"></i>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Section 2b: Format Pertandingan (BO1/BO3) ===== --}}
        <div class="bb-section mb-4">
            <div class="bb-section-header">
                <div class="bb-section-icon"><i class="bi bi-controller"></i></div>
                <div>
                    <h6 class="text-white font-weight-bold mb-0">Format Pertandingan</h6>
                    <small class="text-muted">Berlaku untuk <strong>semua match</strong> di bracket ini</small>
                </div>
            </div>
            <div class="bb-section-body">
                <div class="size-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <label class="size-card">
                        <input type="radio" name="format_tanding" value="BO1" checked required>
                        <div class="size-card-inner">
                            <div class="size-num">BO1</div>
                            <div class="size-label">Best of 1</div>
                            <div class="size-desc">Satu match per ronde (skor tunggal)</div>
                        </div>
                    </label>
                    <label class="size-card">
                        <input type="radio" name="format_tanding" value="BO3" required>
                        <div class="size-card-inner">
                            <div class="size-num">BO3</div>
                            <div class="size-label">Best of 3</div>
                            <div class="size-desc">3 game per match (mis. MLBB)</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- ===== Section 3: Bracket Size (card visual) ===== --}}
        <div class="bb-section mb-4">
            <div class="bb-section-header">
                <div class="bb-section-icon"><i class="bi bi-diagram-3"></i></div>
                <div>
                    <h6 class="text-white font-weight-bold mb-0">Ukuran Bracket</h6>
                    <small class="text-muted">Jumlah tim akan menyesuaikan ukuran</small>
                </div>
            </div>
            <div class="bb-section-body">
                <div class="size-grid">
                    @php
                        $sizes = [
                            ['v' => 4,  'label' => '4 Tim',  'desc' => 'Semi Final + Final',                'rounds' => 2],
                            ['v' => 8,  'label' => '8 Tim',  'desc' => 'Quarter + Semi + Final',            'rounds' => 3],
                            ['v' => 16, 'label' => '16 Tim', 'desc' => 'Round of 16 + Quarter + Semi + Final','rounds' => 4],
                            ['v' => 32, 'label' => '32 Tim', 'desc' => 'Round of 32 + 16 + Quarter + Semi + Final', 'rounds' => 5],
                        ];
                    @endphp
                    @foreach($sizes as $sz)
                        <label class="size-card">
                            <input type="radio" name="bracket_size" value="{{ $sz['v'] }}" {{ $sz['v'] === 8 ? 'checked' : '' }} required>
                            <div class="size-card-inner">
                                <div class="size-num">{{ $sz['v'] }}</div>
                                <div class="size-label">{{ $sz['label'] }}</div>
                                <div class="size-desc">{{ $sz['desc'] }}</div>
                                <div class="size-rounds">
                                    @for($i = 0; $i < $sz['rounds']; $i++)
                                        <span class="size-round-dot"></span>
                                    @endfor
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Section 4: Pilih Tim ===== --}}
        <div class="bb-section mb-4">
            <div class="bb-section-header">
                <div class="bb-section-icon"><i class="bi bi-people-fill"></i></div>
                <div class="flex-grow-1">
                    <h6 class="text-white font-weight-bold mb-0">Pilih Tim Peserta</h6>
                    <small class="text-muted">Klik tim untuk memilih. Pilih tepat sejumlah ukuran bracket.</small>
                </div>
                <div class="bb-progress-pill" id="selectionInfo">
                    <i class="bi bi-check2-circle mr-1"></i>
                    <span id="countCurrent">0</span>/<span id="countTarget">8</span> tim
                </div>
            </div>
            <div class="bb-section-body">
                <div class="prodi-grid">
                    @foreach($teams as $prodi => $prodiTeams)
                        <div class="prodi-card-bb">
                            <div class="prodi-head">
                                <span class="prodi-name"><i class="bi bi-mortarboard mr-1"></i>{{ $prodi }}</span>
                                <span class="prodi-count">{{ count($prodiTeams) }}</span>
                            </div>
                            <div class="prodi-body">
                                @foreach($prodiTeams as $team)
                                    <button type="button" class="team-pick"
                                            data-team-id="{{ $team->id }}"
                                            data-team-name="{{ $team->name }}"
                                            onclick="toggleTeam(this)">
                                        <span class="team-pick-avatar">{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                                        <span class="team-pick-name">{{ $team->name }}</span>
                                        <i class="bi bi-check-circle-fill team-pick-check"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Selected preview --}}
                <div class="selected-preview mt-4" id="selectedTeamsPreview" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-white mb-0"><i class="bi bi-list-check mr-1"></i>Tim Terpilih</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllTeams()">
                            <i class="bi bi-x-lg mr-1"></i>Hapus Semua
                        </button>
                    </div>
                    <div id="selectedTeamsList" class="selected-list"></div>
                </div>
            </div>
        </div>

        {{-- Hidden team_ids container --}}
        <div id="teamIdsContainer"></div>

        {{-- Sticky Footer --}}
        <div class="bb-footer">
            <a href="{{ route('admin.index') }}" class="btn btn-outline-light">
                <i class="bi bi-arrow-left mr-2"></i>Kembali
            </a>
            <div class="bb-footer-status text-muted small d-none d-md-block">
                <span id="footerStatus">Lengkapi konfigurasi & pilih tim</span>
            </div>
            <button type="submit" class="btn btn-primary px-4" id="generateBtn" disabled>
                Lanjut Atur Bracket <i class="bi bi-arrow-right ml-2"></i>
            </button>
        </div>
    </form>

</div>
@endsection

@section('styles')
<style>
    /* ── Header + Stepper ── */
    .bb-stepper { gap: 0; }
    .bb-step {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 999px;
    }
    .bb-step.active {
        background: linear-gradient(135deg, rgba(99,102,241,0.18), rgba(168,85,247,0.12));
        border-color: rgba(99,102,241,0.4);
    }
    .bb-step-num {
        width: 24px; height: 24px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        color: #94a3b8;
        font-size: 0.75rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }
    .bb-step.active .bb-step-num {
        background: #6366f1; color: #fff;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.2);
    }
    .bb-step-label { font-size: 0.78rem; color: #94a3b8; font-weight: 600; }
    .bb-step.active .bb-step-label { color: #c7d2fe; }
    .bb-step-line { width: 32px; height: 2px; background: rgba(255,255,255,0.1); margin: 0 8px; }

    /* ── Section ── */
    .bb-section {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 14px;
        overflow: hidden;
    }
    .bb-section-header {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 18px;
        background: rgba(255,255,255,0.02);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .bb-section-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(168,85,247,0.18));
        color: #c7d2fe;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .bb-section-body { padding: 20px 18px; }

    /* ── Form input ── */
    .bb-label {
        display: block;
        color: #cbd5e1;
        font-weight: 600;
        font-size: 0.82rem;
        margin-bottom: 6px;
    }
    .bb-input {
        width: 100%;
        background: #0f1320;
        border: 1px solid rgba(255,255,255,0.08);
        color: #e2e8f0;
        padding: 9px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.15s;
    }
    .bb-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        background: #131829;
    }
    .bb-input::placeholder { color: #475569; }

    /* ── Sport grid (radio cards) ── */
    .sport-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
    }
    .sport-card { margin: 0; cursor: pointer; }
    .sport-card input { display: none; }
    .sport-card-inner {
        position: relative;
        display: flex; flex-direction: column;
        align-items: center; gap: 6px;
        padding: 16px 10px;
        background: #0f1320;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        transition: all 0.15s;
    }
    .sport-card-inner:hover {
        border-color: rgba(99,102,241,0.5);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.2);
    }
    .sport-card input:checked ~ .sport-card-inner {
        background: linear-gradient(135deg, rgba(99,102,241,0.22), rgba(168,85,247,0.15));
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.18);
    }
    .sport-icon { font-size: 1.6rem; color: #818cf8; }
    .sport-name { color: #e2e8f0; font-weight: 600; font-size: 0.82rem; text-align: center; }
    .sport-check {
        position: absolute; top: 6px; right: 8px;
        color: #10b981; font-size: 1rem;
        opacity: 0; transition: opacity 0.15s;
    }
    .sport-card input:checked ~ .sport-card-inner .sport-check { opacity: 1; }

    /* ── Bracket size cards ── */
    .size-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .size-card { margin: 0; cursor: pointer; }
    .size-card input { display: none; }
    .size-card-inner {
        padding: 16px;
        background: #0f1320;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        text-align: center;
        transition: all 0.15s;
    }
    .size-card-inner:hover {
        border-color: rgba(99,102,241,0.5);
        transform: translateY(-2px);
    }
    .size-card input:checked ~ .size-card-inner {
        background: linear-gradient(135deg, rgba(99,102,241,0.22), rgba(168,85,247,0.15));
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.18);
    }
    .size-num {
        font-size: 1.8rem; font-weight: 800;
        background: linear-gradient(135deg, #818cf8, #c084fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
    }
    .size-label { color: #e2e8f0; font-weight: 700; font-size: 0.85rem; margin-top: 4px; }
    .size-desc { color: #64748b; font-size: 0.7rem; margin-top: 2px; line-height: 1.3; }
    .size-rounds {
        display: flex; justify-content: center; gap: 4px; margin-top: 8px;
    }
    .size-round-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: rgba(99,102,241,0.4);
    }
    .size-card input:checked ~ .size-card-inner .size-round-dot { background: #818cf8; }

    /* ── Selection counter pill ── */
    .bb-progress-pill {
        background: #1e2540;
        color: #a5b4fc;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .bb-progress-pill.complete {
        background: rgba(16,185,129,0.15);
        color: #34d399;
        box-shadow: 0 0 0 2px rgba(16,185,129,0.3);
    }

    /* ── Prodi grid ── */
    .prodi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }
    .prodi-card-bb {
        background: #0f1320;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 10px;
        overflow: hidden;
    }
    .prodi-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 8px 12px;
        background: rgba(255,255,255,0.03);
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .prodi-name { color: #cbd5e1; font-weight: 600; font-size: 0.82rem; }
    .prodi-count {
        background: rgba(99,102,241,0.15);
        color: #a5b4fc;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .prodi-body { padding: 8px; display: flex; flex-direction: column; gap: 6px; }

    /* ── Team pick button ── */
    .team-pick {
        display: flex; align-items: center; gap: 8px;
        background: #1a1f2e;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 8px;
        padding: 7px 10px;
        color: #e2e8f0;
        font-weight: 600;
        font-size: 0.82rem;
        cursor: pointer;
        transition: all 0.15s;
        text-align: left;
        position: relative;
    }
    .team-pick:hover {
        border-color: rgba(99,102,241,0.55);
        background: #1f2540;
        transform: translateX(2px);
    }
    .team-pick-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        width: 26px; height: 26px;
        background: linear-gradient(135deg, #475569, #64748b);
        border-radius: 6px;
        font-weight: 800;
        font-size: 0.78rem;
        flex-shrink: 0;
        transition: all 0.15s;
    }
    .team-pick-name { flex: 1; }
    .team-pick-check {
        color: #10b981;
        font-size: 1rem;
        opacity: 0;
        transition: opacity 0.15s;
    }
    .team-pick.selected {
        background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.12));
        border-color: #6366f1;
        box-shadow: 0 0 0 1px rgba(99,102,241,0.3);
    }
    .team-pick.selected .team-pick-avatar {
        background: linear-gradient(135deg, #6366f1, #a855f7);
        box-shadow: 0 2px 6px rgba(99,102,241,0.5);
    }
    .team-pick.selected .team-pick-check { opacity: 1; }

    /* ── Selected preview ── */
    .selected-preview {
        background: rgba(16,185,129,0.05);
        border: 1px solid rgba(16,185,129,0.2);
        border-radius: 10px;
        padding: 14px 16px;
    }
    .selected-list {
        display: flex; flex-wrap: wrap; gap: 8px;
    }
    .selected-chip {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
        padding: 5px 10px 5px 5px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(79,70,229,0.35);
    }
    .selected-chip .chip-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px;
        background: rgba(255,255,255,0.25);
        border-radius: 50%;
        font-size: 0.7rem; font-weight: 800;
    }
    .selected-chip .chip-remove {
        cursor: pointer;
        opacity: 0.7;
        font-size: 0.78rem;
        margin-left: 4px;
        transition: opacity 0.15s;
    }
    .selected-chip .chip-remove:hover { opacity: 1; }

    /* ── Sticky footer ── */
    .bb-footer {
        position: sticky;
        bottom: 0;
        background: rgba(15,19,32,0.95);
        backdrop-filter: blur(8px);
        border-top: 1px solid rgba(255,255,255,0.08);
        padding: 12px 18px;
        margin: 24px -18px -16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-radius: 0 0 14px 14px;
        z-index: 10;
    }

    /* date input dark hint */
    .bb-input::-webkit-calendar-picker-indicator { filter: invert(0.7); }
</style>
@endsection

@section('scripts')
<script>
let selectedTeams = [];

function getBracketSize() {
    const r = document.querySelector('input[name="bracket_size"]:checked');
    return r ? parseInt(r.value) : 8;
}

function toggleTeam(btn) {
    const id   = btn.dataset.teamId;
    const name = btn.dataset.teamName;
    const size = getBracketSize();

    if (btn.classList.contains('selected')) {
        btn.classList.remove('selected');
        selectedTeams = selectedTeams.filter(t => t.id != id);
    } else {
        if (selectedTeams.length >= size) {
            Swal.fire({
                icon: 'warning', title: 'Maksimum Tim',
                text: `Hanya bisa memilih ${size} tim. Hapus tim lain dulu.`,
                confirmButtonColor: '#6366f1'
            });
            return;
        }
        btn.classList.add('selected');
        selectedTeams.push({ id, name });
    }
    updateUI();
}

function removeTeamById(id) {
    selectedTeams = selectedTeams.filter(t => t.id != id);
    const btn = document.querySelector(`.team-pick[data-team-id="${id}"]`);
    if (btn) btn.classList.remove('selected');
    updateUI();
}

function clearAllTeams() {
    selectedTeams = [];
    document.querySelectorAll('.team-pick.selected').forEach(b => b.classList.remove('selected'));
    updateUI();
}

function updateUI() {
    const size = getBracketSize();
    const n = selectedTeams.length;

    document.getElementById('countCurrent').textContent = n;
    document.getElementById('countTarget').textContent  = size;

    const pill = document.getElementById('selectionInfo');
    pill.classList.toggle('complete', n === size);

    /* Selected chips */
    const wrap = document.getElementById('selectedTeamsPreview');
    const list = document.getElementById('selectedTeamsList');
    if (n > 0) {
        wrap.style.display = 'block';
        list.innerHTML = selectedTeams.map(t => `
            <span class="selected-chip">
                <span class="chip-avatar">${t.name.charAt(0).toUpperCase()}</span>
                ${t.name}
                <i class="bi bi-x-circle-fill chip-remove" onclick="removeTeamById('${t.id}')"></i>
            </span>`).join('');
    } else {
        wrap.style.display = 'none';
    }

    /* Submit button + status */
    const btn      = document.getElementById('generateBtn');
    const status   = document.getElementById('footerStatus');
    const sportOk  = !!document.querySelector('input[name="sport_id"]:checked');
    const nameOk   = !!document.querySelector('input[name="tournament_name"]').value.trim();
    const startOk  = !!document.getElementById('startDate').value;
    const endOk    = !!document.getElementById('endDate').value;
    const teamsOk  = n === size;

    if (!nameOk)            status.textContent = 'Isi nama tournament dulu';
    else if (!sportOk)      status.textContent = 'Pilih cabang olahraga';
    else if (!startOk || !endOk) status.textContent = 'Lengkapi tanggal turnamen';
    else if (!teamsOk)      status.textContent = `Pilih ${size - n} tim lagi`;
    else                    status.textContent = '✓ Siap lanjut atur bracket';

    btn.disabled = !(nameOk && sportOk && startOk && endOk && teamsOk);
}

/* Listeners */
document.querySelectorAll('input[name="bracket_size"]').forEach(r => {
    r.addEventListener('change', () => {
        const size = getBracketSize();
        if (selectedTeams.length > size) {
            Swal.fire({
                icon: 'info', title: 'Tim Berlebih',
                text: `Bracket ${size} hanya butuh ${size} tim. Lebihan akan otomatis dihapus.`,
                confirmButtonColor: '#6366f1'
            });
            selectedTeams = selectedTeams.slice(0, size);
            document.querySelectorAll('.team-pick.selected').forEach((b, i) => {
                if (i >= size) b.classList.remove('selected');
            });
        }
        updateUI();
    });
});

document.querySelectorAll('input[name="sport_id"], input[name="tournament_name"], #startDate, #endDate')
    .forEach(el => el.addEventListener('input', updateUI));

/* Date validation: end >= start */
document.getElementById('startDate').addEventListener('change', function() {
    document.getElementById('endDate').min = this.value;
});

/* Submit: inject team_ids hidden inputs */
document.getElementById('bracketConfigForm').addEventListener('submit', function(e) {
    const size = getBracketSize();
    if (selectedTeams.length !== size) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Tim Belum Lengkap',
            text: `Pilih tepat ${size} tim!`, confirmButtonColor: '#6366f1' });
        return;
    }
    const c = document.getElementById('teamIdsContainer');
    c.innerHTML = '';
    selectedTeams.forEach(t => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'team_ids[]';
        inp.value = t.id;
        c.appendChild(inp);
    });
});

/* Init */
updateUI();
</script>
@endsection
