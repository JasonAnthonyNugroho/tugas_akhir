<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PertandinganController;
use App\Http\Controllers\CustomBracketController;
use App\Http\Middleware\PreventBackHistory;
use Illuminate\Support\Facades\Route;

// Jalur Publik (Mahasiswa)
Route::get('/', [PertandinganController::class, 'index'])->name('home');
Route::get('/history', [PertandinganController::class, 'history'])->name('history');
Route::get('/pertandingan/{pertandingan}', [PertandinganController::class, 'show'])->name('pertandingan.show');
Route::get('/tournament/{tournament}/bracket', [CustomBracketController::class, 'publicBracket'])->name('tournament.public.bracket');

// API Polling — dipakai guest dashboard untuk update real-time tanpa WebSocket
Route::get('/api/live-matches', function () {
    \App\Models\Pertandingan::autoUpdateLiveStatus();
    
    $matches = \App\Models\Pertandingan::with(['teamA', 'teamB', 'sport'])
        ->whereIn('status', ['live', 'scheduled'])
        ->orderBy('waktu_tanding', 'asc')
        ->get()
        ->map(function ($p) {
            return [
                'id'          => $p->id,
                'status'      => $p->status,
                'score_a'     => $p->score_a,
                'score_b'     => $p->score_b,
                'team_a'      => $p->teamA?->name ?? 'TBD',
                'team_b'      => $p->teamB?->name ?? 'TBD',
                'team_a_id'   => $p->team_a_id,
                'team_b_id'   => $p->team_b_id,
                'sport'       => $p->sport?->nama_sport,
                'sport_icon'  => $p->sport?->icon ?? 'bi-trophy',
                'lokasi'      => $p->lokasi,
                'waktu'       => $p->waktu_tanding->format('d M, H:i'),
                'detail_url'  => route('pertandingan.show', $p->id),
            ];
        });

    return response()->json([
        'matches'   => $matches,
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.live-matches');

// Jalur Autentikasi
Route::middleware(['guest', PreventBackHistory::class])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Jalur Khusus (Panitia)
Route::middleware(['auth', PreventBackHistory::class])->group(function () {
    Route::get('/admin', [PertandinganController::class, 'adminDashboard'])->name('admin.index');
    Route::get('/admin/skor', [PertandinganController::class, 'manageScore'])->name('admin.skor');
    Route::post('/admin/store', [PertandinganController::class, 'store'])->name('pertandingan.store');
    Route::post('/admin/bracket/{tournament}/reroll', [PertandinganController::class, 'rerollBracket'])->name('admin.bracket.reroll');
    Route::delete('/admin/tournament/{tournament}', [PertandinganController::class, 'deleteTournament'])->name('admin.tournament.delete');
    Route::patch('/admin/pertandingan/{pertandingan}/quick-update', [PertandinganController::class, 'quickUpdate'])->name('pertandingan.quick-update');
    Route::post('/admin/pertandingan/bulk-live', [PertandinganController::class, 'bulkLive'])->name('pertandingan.bulk-live');
    Route::patch('/pertandingan/{pertandingan}/update-score', [PertandinganController::class, 'updateScore']);
    Route::patch('/pertandingan/{pertandingan}', [PertandinganController::class, 'update'])->name('pertandingan.update');

    // Generate Bracket Routes
    Route::get('/admin/bracket-builder', [CustomBracketController::class, 'builder'])->name('admin.tournament.bracket.builder');
    Route::post('/admin/bracket-builder/arrange', [CustomBracketController::class, 'showArrange'])->name('admin.tournament.bracket.arrange');
    Route::post('/admin/bracket-builder/store', [CustomBracketController::class, 'store'])->name('admin.tournament.bracket.store');
    Route::get('/admin/tournament/{tournament}/bracket', [CustomBracketController::class, 'viewBracket'])->name('admin.tournament.bracket.view');
    Route::patch('/admin/tournament/{tournament}/update', [CustomBracketController::class, 'updateTournament'])->name('admin.tournament.update');
});
