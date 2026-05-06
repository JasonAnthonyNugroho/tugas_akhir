<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PertandinganController;
use App\Http\Controllers\CustomBracketController;
use Illuminate\Support\Facades\Route;

// Jalur Publik (Mahasiswa)
Route::get('/', [PertandinganController::class, 'index'])->name('home');
Route::get('/history', [PertandinganController::class, 'history'])->name('history');
Route::get('/pertandingan/{pertandingan}', [PertandinganController::class, 'show'])->name('pertandingan.show');
Route::get('/tournament/{tournament}/bracket', [CustomBracketController::class, 'publicBracket'])->name('tournament.public.bracket');

// Jalur Autentikasi
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Jalur Khusus (Panitia)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [PertandinganController::class, 'adminDashboard'])->name('admin.index');
    Route::get('/admin/skor', [PertandinganController::class, 'manageScore'])->name('admin.skor');
    Route::post('/admin/store', [PertandinganController::class, 'store'])->name('pertandingan.store');
    Route::post('/admin/bracket/generate', [PertandinganController::class, 'generateBracket'])->name('admin.bracket.generate');
    Route::post('/admin/bracket/{tournament}/reroll', [PertandinganController::class, 'rerollBracket'])->name('admin.bracket.reroll');
    Route::delete('/admin/tournament/{tournament}', [PertandinganController::class, 'deleteTournament'])->name('admin.tournament.delete');
    Route::patch('/admin/pertandingan/{pertandingan}/quick-update', [PertandinganController::class, 'quickUpdate'])->name('pertandingan.quick-update');
    Route::post('/admin/pertandingan/bulk-live', [PertandinganController::class, 'bulkLive'])->name('pertandingan.bulk-live');
    Route::patch('/pertandingan/{pertandingan}/update-score', [PertandinganController::class, 'updateScore']);
    Route::patch('/pertandingan/{pertandingan}', [PertandinganController::class, 'update'])->name('pertandingan.update');
    
    // Clear all matches (tanpa hapus data master)
    Route::post('/admin/clear-matches', [PertandinganController::class, 'clearAllMatches'])->name('admin.clear-matches');
    
    // Custom Bracket Builder Routes
    Route::get('/admin/bracket-builder', [CustomBracketController::class, 'builder'])->name('admin.tournament.bracket.builder');
    Route::post('/admin/bracket-builder/preview', [CustomBracketController::class, 'preview'])->name('admin.tournament.bracket.preview');
    Route::post('/admin/bracket-builder/store', [CustomBracketController::class, 'store'])->name('admin.tournament.bracket.store');
    Route::get('/admin/tournament/{tournament}/bracket', [CustomBracketController::class, 'viewBracket'])->name('admin.tournament.bracket.view');
    Route::post('/admin/tournament/{tournament}/arrangement', [CustomBracketController::class, 'updateArrangement'])->name('admin.tournament.bracket.arrangement');
});
