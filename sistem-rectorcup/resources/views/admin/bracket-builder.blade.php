@extends('layouts.app')

@section('title', 'Custom Bracket Builder')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="font-weight-bold text-white mb-2">
            <i class="bi bi-magic mr-2"></i>Custom Bracket Builder
        </h2>
        <p class="text-muted">Flow: Konfigurasi → Pilih Tim → Generate Bracket → Drag & Drop Customisasi</p>
    </div>

    {{-- Step 1: Configuration & Team Selection --}}
    <div class="card border-0 mb-4" style="background: rgba(255,255,255,0.03); border-radius: 20px;" id="step1Section">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="text-white font-weight-bold mb-0">
                <span class="badge badge-primary mr-2">1</span>Konfigurasi & Pilih Tim
            </h5>
        </div>
        <div class="card-body p-4">
            <form id="bracketConfigForm">
                @csrf
                <div class="row">
                    {{-- Tournament Name --}}
                    <div class="col-md-6 mb-3">
                        <label class="text-white font-weight-bold">Nama Tournament</label>
                        <input type="text" name="tournament_name" class="form-control bg-dark text-white border-secondary" 
                            placeholder="Contoh: Rector Cup Futsal 2026" required>
                    </div>

                    {{-- Sport --}}
                    <div class="col-md-6 mb-3">
                        <label class="text-white font-weight-bold">Cabang Olahraga</label>
                        <select name="sport_id" class="form-control bg-dark text-white border-secondary" required>
                            <option value="">Pilih Sport...</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}">{{ $sport->nama_sport }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bracket Size --}}
                    <div class="col-md-6 mb-3">
                        <label class="text-white font-weight-bold">Ukuran Bracket</label>
                        <select name="bracket_size" id="bracketSize" class="form-control bg-dark text-white border-secondary" required>
                            <option value="4">4 Teams (Semi Final + Final)</option>
                            <option value="8" selected>8 Teams (Quarter Final + Semi + Final)</option>
                            <option value="16">16 Teams (Round of 16 + Quarter + Semi + Final)</option>
                            <option value="32">32 Teams</option>
                        </select>
                        <small class="text-muted">Tim yang dipilih harus sama dengan ukuran bracket</small>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-md-6 mb-3">
                        <label class="text-white font-weight-bold">Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" class="form-control bg-dark text-white border-secondary" 
                            placeholder="Informasi tambahan">
                    </div>
                </div>

                {{-- Team Selection --}}
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="text-white font-weight-bold mb-0">
                            Pilih Tim Peserta
                            <span class="text-muted font-weight-normal">- Pilih sesuai ukuran bracket</span>
                        </label>
                        <div id="selectionInfo" class="badge badge-secondary">
                            0 / 8 tim dipilih
                        </div>
                    </div>
                    
                    {{-- All Teams Grid --}}
                    <div id="teamGrid" class="row">
                        @foreach($teams as $prodi => $prodiTeams)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card bg-dark border-secondary h-100">
                                    <div class="card-header bg-transparent border-secondary py-2">
                                        <span class="text-white font-weight-bold">{{ $prodi }}</span>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($prodiTeams as $team)
                                                <div class="team-select-card" 
                                                     data-team-id="{{ $team->id }}"
                                                     data-team-name="{{ $team->name }}"
                                                     onclick="toggleTeam(this)">
                                                    <span class="badge badge-dark px-2 py-1" style="cursor: pointer; font-size: 0.8rem;">
                                                        {{ $team->name }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Selected Teams Preview --}}
                    <div class="mt-3 p-3 bg-dark rounded" id="selectedTeamsPreview" style="display: none;">
                        <h6 class="text-white mb-2">Tim Terpilih:</h6>
                        <div id="selectedTeamsList" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('admin.index') }}" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left mr-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary px-4" id="generateBtn" disabled>
                        <i class="bi bi-diagram-3 mr-2"></i>Generate Bracket Preview
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Step 2: Bracket Preview & Drag-Drop --}}
    <div class="card border-0 mb-4 d-none" id="previewSection" style="background: rgba(255,255,255,0.03); border-radius: 20px;">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="text-white font-weight-bold mb-0">
                    <span class="badge badge-primary mr-2">2</span>Arrange Bracket (Drag & Drop)
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-warning" id="shuffleBtn">
                        <i class="bi bi-shuffle mr-1"></i>Random
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" id="resetBtn">
                        <i class="bi bi-arrow-counterclockwise mr-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            {{-- Instructions --}}
            <div class="alert alert-info border-0 mb-4" style="background: rgba(99, 102, 241, 0.1);">
                <i class="bi bi-info-circle mr-2"></i>
                <strong>Drag & Drop:</strong> Seret tim dari kiri ke slot bracket di kanan. 
                Klik <strong>Random</strong> untuk acak otomatis, atau <strong>Reset</strong> untuk kembalikan ke awal.
            </div>

            <div class="row">
                {{-- Available Teams (Draggable Source) --}}
                <div class="col-md-3">
                    <div class="card bg-dark border-secondary h-100">
                        <div class="card-header bg-transparent border-secondary">
                            <h6 class="text-white mb-0">
                                <i class="bi bi-people mr-2"></i>Tim Tersedia
                            </h6>
                        </div>
                        <div class="card-body p-2" id="availableTeams">
                            {{-- Teams akan di-populate via JS --}}
                        </div>
                    </div>
                </div>

                {{-- Bracket Visualization --}}
                <div class="col-md-9">
                    <div id="bracketContainer" class="bracket-container">
                        {{-- Bracket akan di-generate via JS --}}
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="mt-4 text-center">
                <button type="button" class="btn btn-success btn-lg px-5" id="saveBracketBtn">
                    <i class="bi bi-check-lg mr-2"></i>Simpan Bracket
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden Form untuk Submit --}}
<form id="saveBracketForm" action="{{ route('admin.tournament.bracket.store') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="tournament_name" id="formTournamentName">
    <input type="hidden" name="sport_id" id="formSportId">
    <input type="hidden" name="bracket_size" id="formBracketSize">
    <input type="hidden" name="keterangan" id="formKeterangan">
    <input type="hidden" name="arrangement" id="formArrangement">
</form>
@endsection

@section('styles')
<style>
    {{-- Drag & Drop Styles --}}
    .team-card {
        cursor: grab;
        transition: all 0.2s ease;
        user-select: none;
    }
    
    .team-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .team-card.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    
    .team-card.selected {
        border: 2px solid #6366f1 !important;
        background: rgba(99, 102, 241, 0.2) !important;
    }

    {{-- Bracket Styles --}}
    .bracket-container {
        overflow-x: auto;
        padding: 20px;
    }
    
    .bracket-round {
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin-right: 40px;
        min-width: 200px;
    }
    
    .bracket-round-title {
        text-align: center;
        color: #94a3b8;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 15px;
        text-transform: uppercase;
    }
    
    .bracket-match {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        margin-bottom: 20px;
        position: relative;
    }
    
    .bracket-match::after {
        content: '';
        position: absolute;
        right: -20px;
        top: 50%;
        width: 20px;
        height: 2px;
        background: rgba(255, 255, 255, 0.2);
    }
    
    .bracket-match:last-child::after {
        display: none;
    }
    
    .match-slot {
        padding: 10px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        min-height: 45px;
        display: flex;
        align-items: center;
        transition: all 0.2s;
    }
    
    .match-slot:last-child {
        border-bottom: none;
    }
    
    .match-slot.drag-over {
        background: rgba(99, 102, 241, 0.3);
        border: 2px dashed #6366f1;
    }
    
    .match-slot.occupied {
        background: rgba(16, 185, 129, 0.1);
        border-left: 3px solid #10b981;
    }
    
    .match-slot .team-name {
        color: #f8fafc;
        font-weight: 500;
        font-size: 0.875rem;
    }
    
    .match-slot .placeholder-text {
        color: #64748b;
        font-size: 0.75rem;
        font-style: italic;
    }
    
    .match-slot .remove-btn {
        margin-left: auto;
        opacity: 0;
        transition: opacity 0.2s;
        cursor: pointer;
        color: #ef4444;
    }
    
    .match-slot:hover .remove-btn {
        opacity: 1;
    }
    
    {{-- Connector Lines --}}
    .bracket-connector {
        display: flex;
        align-items: center;
    }
    
    .connector-line {
        width: 20px;
        height: 2px;
        background: rgba(255, 255, 255, 0.2);
    }
    
    .connector-join {
        width: 2px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
    }
</style>
@endsection

@section('scripts')
<script>
let selectedTeams = [];
let currentArrangement = [];

{{-- STEP 1: Team Selection --}}
function toggleTeam(element) {
    const teamId = element.dataset.teamId;
    const teamName = element.dataset.teamName;
    const badge = element.querySelector('.badge');
    const bracketSize = parseInt(document.getElementById('bracketSize').value);
    
    if (element.classList.contains('selected')) {
        {{-- Deselect --}}
        element.classList.remove('selected');
        badge.classList.remove('badge-primary');
        badge.classList.add('badge-dark');
        selectedTeams = selectedTeams.filter(t => t.id != teamId);
    } else {
        {{-- Select --}}
        if (selectedTeams.length >= bracketSize) {
            Swal.fire({
                icon: 'warning',
                title: 'Maksimum Tim',
                text: `Anda hanya bisa memilih ${bracketSize} tim untuk bracket ini!`,
                confirmButtonColor: '#6366f1'
            });
            return;
        }
        element.classList.add('selected');
        badge.classList.remove('badge-dark');
        badge.classList.add('badge-primary');
        selectedTeams.push({ id: teamId, name: teamName });
    }
    
    updateSelectionUI();
}

function updateSelectionUI() {
    const bracketSize = parseInt(document.getElementById('bracketSize').value);
    const count = selectedTeams.length;
    
    {{-- Update counter badge --}}
    const infoBadge = document.getElementById('selectionInfo');
    infoBadge.textContent = `${count} / ${bracketSize} tim dipilih`;
    
    if (count === bracketSize) {
        infoBadge.classList.remove('badge-secondary');
        infoBadge.classList.add('badge-success');
    } else {
        infoBadge.classList.remove('badge-success');
        infoBadge.classList.add('badge-secondary');
    }
    
    {{-- Update selected teams preview --}}
    const previewDiv = document.getElementById('selectedTeamsPreview');
    const listDiv = document.getElementById('selectedTeamsList');
    
    if (count > 0) {
        previewDiv.style.display = 'block';
        listDiv.innerHTML = selectedTeams.map(t => `
            <span class="badge badge-primary px-3 py-2">${t.name}</span>
        `).join('');
    } else {
        previewDiv.style.display = 'none';
    }
    
    {{-- Enable/disable generate button --}}
    const generateBtn = document.getElementById('generateBtn');
    generateBtn.disabled = count !== bracketSize;
    if (count === bracketSize) {
        generateBtn.classList.remove('btn-primary');
        generateBtn.classList.add('btn-success');
        generateBtn.innerHTML = '<i class="bi bi-check-lg mr-2"></i>Generate Bracket Preview';
    } else {
        generateBtn.classList.remove('btn-success');
        generateBtn.classList.add('btn-primary');
        generateBtn.innerHTML = '<i class="bi bi-diagram-3 mr-2"></i>Generate Bracket Preview';
    }
}

{{-- Update counter when bracket size changes --}}
document.getElementById('bracketSize').addEventListener('change', function() {
    const newSize = parseInt(this.value);
    const currentCount = selectedTeams.length;
    
    if (currentCount > newSize) {
        Swal.fire({
            icon: 'info',
            title: 'Tim Berlebih',
            text: `Anda sudah memilih ${currentCount} tim. Silakan kurangi menjadi ${newSize} tim.`,
            confirmButtonColor: '#6366f1'
        });
    }
    updateSelectionUI();
});

{{-- STEP 2: Generate Bracket with Auto-Fill --}}
document.getElementById('bracketConfigForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const bracketSize = parseInt(document.getElementById('bracketSize').value);
    
    if (selectedTeams.length !== bracketSize) {
        Swal.fire({
            icon: 'warning',
            title: 'Tim Belum Lengkap',
            text: `Pilih tepat ${bracketSize} tim untuk melanjutkan!`,
            confirmButtonColor: '#6366f1'
        });
        return;
    }
    
    {{-- Show preview section --}}
    document.getElementById('previewSection').classList.remove('d-none');
    
    {{-- Generate bracket with teams auto-filled --}}
    generateBracketWithTeams();
    
    {{-- Scroll to preview --}}
    document.getElementById('previewSection').scrollIntoView({ behavior: 'smooth' });
});

function generateBracketWithTeams() {
    const bracketSize = parseInt(document.getElementById('bracketSize').value);
    const numRounds = Math.log2(bracketSize);
    const container = document.getElementById('bracketContainer');
    const availableTeamsDiv = document.getElementById('availableTeams');
    
    {{-- Initialize arrangement with selected teams --}}
    currentArrangement = [];
    for (let i = 0; i < bracketSize / 2; i++) {
        currentArrangement[i] = [null, null];
    }
    
    {{-- Populate available teams pool for drag source --}}
    availableTeamsDiv.innerHTML = `
        <div class="mb-2 small text-muted">Drag tim ke bracket:</div>
        ${selectedTeams.map(team => `
            <div class="team-card card bg-dark border-secondary mb-2" 
                 draggable="true" 
                 data-team-id="${team.id}"
                 data-team-name="${team.name}">
                <div class="card-body p-2">
                    <div class="text-white font-weight-bold" style="font-size: 0.875rem;">${team.name}</div>
                </div>
            </div>
        `).join('')}
    `;
    
    {{-- Initialize drag for available teams --}}
    document.querySelectorAll('#availableTeams .team-card').forEach(card => {
        card.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('teamId', this.dataset.teamId);
            e.dataTransfer.setData('teamName', this.dataset.teamName);
            this.classList.add('dragging');
        });
        
        card.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });
    
    {{-- Generate bracket HTML --}}
    let html = '<div class="d-flex">';
    
    for (let round = 1; round <= numRounds; round++) {
        const numMatches = bracketSize / Math.pow(2, round);
        const roundName = getRoundName(round, numRounds);
        
        html += `
            <div class="bracket-round">
                <div class="bracket-round-title">${roundName}</div>
        `;
        
        for (let match = 0; match < numMatches; match++) {
            const teamIndex = match * 2;
            const teamA = selectedTeams[teamIndex];
            const teamB = selectedTeams[teamIndex + 1];
            
            {{-- Fill Round 1 with selected teams --}}
            if (round === 1 && teamA) {
                currentArrangement[match][0] = teamA.id;
            }
            if (round === 1 && teamB) {
                currentArrangement[match][1] = teamB.id;
            }
            
            const slotAHasTeam = round === 1 && teamA;
            const slotBHasTeam = round === 1 && teamB;
            
            html += `
                <div class="bracket-match">
                    <div class="match-slot ${slotAHasTeam ? 'occupied' : ''}" 
                         data-round="${round}" 
                         data-match="${match}"
                         data-slot="a"
                         ${round === 1 ? `data-arrangement-index="${match}"` : ''}
                         ondrop="drop(event)" 
                         ondragover="allowDrop(event)"
                         ondragleave="leaveDrop(event)">
                        ${slotAHasTeam ? `
                            <span class="team-name">${teamA.name}</span>
                            <i class="bi bi-x-circle remove-btn" onclick="removeTeam(this, '${match}', 'a')"></i>
                        ` : '<span class="placeholder-text">Drop tim di sini</span>'}
                    </div>
                    <div class="match-slot ${slotBHasTeam ? 'occupied' : ''}" 
                         data-round="${round}" 
                         data-match="${match}"
                         data-slot="b"
                         ${round === 1 ? `data-arrangement-index="${match}"` : ''}
                         ondrop="drop(event)" 
                         ondragover="allowDrop(event)"
                         ondragleave="leaveDrop(event)">
                        ${slotBHasTeam ? `
                            <span class="team-name">${teamB.name}</span>
                            <i class="bi bi-x-circle remove-btn" onclick="removeTeam(this, '${match}', 'b')"></i>
                        ` : '<span class="placeholder-text">Drop tim di sini</span>'}
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

function getRoundName(round, totalRounds) {
    const diff = totalRounds - round;
    const names = {
        0: 'Final',
        1: 'Semi Final',
        2: 'Quarter Final',
        3: 'Round of 16',
        4: 'Round of 32'
    };
    return names[diff] || `Round ${round}`;
}

function allowDrop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.add('drag-over');
}

function leaveDrop(ev) {
    ev.currentTarget.classList.remove('drag-over');
}

function drop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.remove('drag-over');
    
    const teamId = ev.dataTransfer.getData('teamId');
    const teamName = ev.dataTransfer.getData('teamName');
    const slot = ev.currentTarget;
    const arrangementIndex = slot.dataset.arrangementIndex;
    const slotPosition = slot.dataset.slot;
    
    {{-- Check if team already placed elsewhere in round 1 --}}
    if (arrangementIndex !== undefined) {
        const existingIndex = currentArrangement.findIndex(arr => arr && (arr[0] == teamId || arr[1] == teamId));
        if (existingIndex !== -1) {
            {{-- Remove from previous position --}}
            if (currentArrangement[existingIndex][0] == teamId) {
                currentArrangement[existingIndex][0] = null;
            } else {
                currentArrangement[existingIndex][1] = null;
            }
            
            {{-- Update UI for previous slot --}}
            const prevSlots = document.querySelectorAll(`[data-arrangement-index="${existingIndex}"]`);
            prevSlots.forEach(s => {
                if (s.dataset.slot === (currentArrangement[existingIndex][0] == teamId ? 'a' : 'b')) {
                    clearSlot(s);
                }
            });
        }
        
        {{-- Place in new position --}}
        if (!currentArrangement[arrangementIndex]) {
            currentArrangement[arrangementIndex] = [null, null];
        }
        currentArrangement[arrangementIndex][slotPosition === 'a' ? 0 : 1] = teamId;
    }
    
    {{-- Update slot UI --}}
    slot.classList.add('occupied');
    slot.innerHTML = `
        <span class="team-name">${teamName}</span>
        <i class="bi bi-x-circle remove-btn" onclick="removeTeam(this, '${arrangementIndex}', '${slotPosition}')"></i>
    `;
}

function clearSlot(slot) {
    slot.classList.remove('occupied');
    slot.innerHTML = '<span class="placeholder-text">Drop tim di sini</span>';
}

function removeTeam(btn, arrangementIndex, slotPosition) {
    const slot = btn.closest('.match-slot');
    
    if (arrangementIndex !== 'undefined') {
        currentArrangement[arrangementIndex][slotPosition === 'a' ? 0 : 1] = null;
    }
    
    clearSlot(slot);
}

{{-- Shuffle Button --}}
document.getElementById('shuffleBtn').addEventListener('click', function() {
    const teamIds = selectedTeams.map(t => t.id);
    
    {{-- Fisher-Yates shuffle --}}
    for (let i = teamIds.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [teamIds[i], teamIds[j]] = [teamIds[j], teamIds[i]];
    }
    
    {{-- Fill bracket with shuffled teams --}}
    const slots = document.querySelectorAll('[data-arrangement-index]');
    let teamIndex = 0;
    
    currentArrangement = currentArrangement.map(() => [null, null]);
    
    slots.forEach(slot => {
        const index = parseInt(slot.dataset.arrangementIndex);
        const position = slot.dataset.slot === 'a' ? 0 : 1;
        
        if (teamIndex < teamIds.length && currentArrangement[index]) {
            const teamId = teamIds[teamIndex];
            const team = selectedTeams.find(t => t.id == teamId);
            
            currentArrangement[index][position] = teamId;
            
            slot.classList.add('occupied');
            slot.innerHTML = `
                <span class="team-name">${team.name}</span>
                <i class="bi bi-x-circle remove-btn" onclick="removeTeam(this, '${index}', '${slot.dataset.slot}')"></i>
            `;
            
            teamIndex++;
        }
    });
});

{{-- Reset Button --}}
document.getElementById('resetBtn').addEventListener('click', function() {
    const slots = document.querySelectorAll('[data-arrangement-index]');
    slots.forEach(slot => clearSlot(slot));
    currentArrangement = currentArrangement.map(() => [null, null]);
});

{{-- Save Bracket --}}
document.getElementById('saveBracketBtn').addEventListener('click', function() {
    {{-- Filter out empty arrangements --}}
    const validArrangement = currentArrangement.filter(arr => arr && (arr[0] || arr[1]));
    
    if (validArrangement.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Bracket Kosong',
            text: 'Isi minimal satu match untuk menyimpan bracket!',
            confirmButtonColor: '#6366f1'
        });
        return;
    }
    
    {{-- Populate hidden form --}}
    document.getElementById('formTournamentName').value = document.querySelector('input[name="tournament_name"]').value;
    document.getElementById('formSportId').value = document.querySelector('select[name="sport_id"]').value;
    document.getElementById('formBracketSize').value = document.querySelector('select[name="bracket_size"]').value;
    document.getElementById('formKeterangan').value = document.querySelector('input[name="keterangan"]').value;
    document.getElementById('formArrangement').value = JSON.stringify(currentArrangement);
    
    {{-- Submit form --}}
    document.getElementById('saveBracketForm').submit();
});
</script>
@endsection
