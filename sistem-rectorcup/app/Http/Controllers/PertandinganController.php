<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdated;
use App\Models\Pertandingan;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PertandinganController extends Controller
{
    public function index()
    {
        $selectedSport = request('sport_id');

        // Tampilkan semua match yang sedang live atau terjadwal (termasuk dari tournament).
        // FILTER: Hanya tampilkan pertandingan yang BUKAN TBD (kedua tim sudah terisi)
        $query = Pertandingan::with(['teamA', 'teamB', 'sport', 'games'])
            ->whereIn('status', ['live', 'scheduled'])
            ->whereNotNull('team_a_id')
            ->whereNotNull('team_b_id');

        if ($selectedSport && $selectedSport !== 'all') {
            $query->where('sport_id', $selectedSport);
        }

        $pertandingans = $query->orderBy('waktu_tanding', 'asc')
            ->get()
            ->sortBy(function ($item) {
                return $item->status === 'live' ? 0 : 1;
            })->values();

        $sports = Sport::all();
        
        // Get active tournaments with their data
        $tournaments = \App\Models\Tournament::with(['sport', 'teams', 'pertandingans' => function($q) {
                $q->whereIn('status', ['live', 'finished'])
                  ->orderBy('round', 'desc')
                  ->limit(5);
            }])
            ->where('year', date('Y'))
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('dashboard', compact('pertandingans', 'sports', 'selectedSport', 'tournaments'));
    }

    public function adminDashboard()
    {
        $teams = Team::orderBy('name', 'asc')->get();
        $sports = Sport::orderBy('nama_sport', 'asc')->get();

        // Grouping pertandingans
        $pertandingans = Pertandingan::with(['teamA', 'teamB', 'sport', 'tournament'])
            ->orderBy('waktu_tanding', 'desc')
            ->get();

        $groupedMatches = $pertandingans->groupBy(function ($item) {
            return $item->tournament_id ? 'tournament_' . $item->tournament_id : 'independent';
        });

        $tournaments = Tournament::with(['sport', 'teams'])->where('year', date('Y'))->get();

        return view('admin.dashboard', compact('teams', 'sports', 'pertandingans', 'groupedMatches', 'tournaments'));
    }

    public function quickUpdate(Request $request, Pertandingan $pertandingan)
    {
        $request->validate([
            'waktu_tanding' => 'nullable|date',
            'lokasi' => 'nullable|string',
            'team_a_id' => 'nullable|exists:teams,id',
            'team_b_id' => 'nullable|exists:teams,id',
        ]);

        $pertandingan->update($request->only(['waktu_tanding', 'lokasi', 'team_a_id', 'team_b_id']));

        return back()->with('success', 'Detail pertandingan berhasil diperbarui!');
    }

    public function bulkLive(Request $request)
    {
        $request->validate([
            'match_ids' => 'required|array',
            'match_ids.*' => 'exists:pertandingans,id',
        ]);

        $matches = Pertandingan::whereIn('id', $request->match_ids)
            ->where('status', 'scheduled')
            ->get();
        
        Pertandingan::whereIn('id', $request->match_ids)
            ->where('status', 'scheduled')
            ->update(['status' => 'live']);
        
        // Broadcast status update untuk setiap match
        foreach ($matches as $match) {
            broadcast(new \App\Events\MatchStatusUpdated($match->id, 'live', [
                'id' => $match->id,
                'status' => 'live',
                'team_a' => $match->teamA?->name,
                'team_b' => $match->teamB?->name,
                'sport' => $match->sport?->nama_sport,
            ]));
        }

        return back()->with('success', count($request->match_ids) . ' pertandingan berhasil diaktifkan ke LIVE!');
    }

    public function rerollBracket(Tournament $tournament)
    {
        return DB::transaction(function () use ($tournament) {
            $teamIds = $tournament->teams()->pluck('teams.id')->toArray();
            shuffle($teamIds);

            // Ambil semua match round 1 untuk tournament ini
            $r1Matches = $tournament->pertandingans()
                ->where('round', 1)
                ->orderBy('match_number', 'asc')
                ->get();

            $numTeams = count($teamIds);

            // Reset semua tim di bracket dulu (biar bersih)
            $tournament->pertandingans()->update([
                'team_a_id' => null,
                'team_b_id' => null,
                'winner_id' => null,
                'score_a' => 0,
                'score_b' => 0,
                'status' => 'scheduled'
            ]);

            // Isi ulang Round 1
            for ($i = 0; $i < $numTeams; $i += 2) {
                $matchIdx = $i / 2;
                if (isset($r1Matches[$matchIdx])) {
                    $update = ['team_a_id' => $teamIds[$i]];
                    if (isset($teamIds[$i + 1])) {
                        $update['team_b_id'] = $teamIds[$i + 1];
                    }
                    $r1Matches[$matchIdx]->update($update);
                }
            }

            return redirect()->route('admin.tournament.bracket.view', $tournament)
            ->with('success', 'Bracket berhasil di-reroll dengan urutan tim baru!');
        });
    }

    public function history()
    {
        $selectedYear = request('year', 'all');
        $selectedSportId = request('sport_id', 'all');
        $selectedTournamentId = request('tournament_id');

        // Ambil data tournament yang dipilih jika ada
        $selectedTournament = null;
        if ($selectedTournamentId) {
            $selectedTournament = Tournament::with(['sport', 'pertandingans.teamA', 'pertandingans.teamB', 'pertandingans.winner', 'pertandingans.games'])
                ->find($selectedTournamentId);
        }

        $query = Pertandingan::where('status', 'finished')
            ->with(['teamA', 'teamB', 'sport', 'games'])
            ->orderBy('waktu_tanding', 'desc');

        if ($selectedYear !== 'all') {
            $query->whereYear('waktu_tanding', $selectedYear);
        }

        if ($selectedSportId !== 'all') {
            $query->where('sport_id', $selectedSportId);
        }

        $history = $query->get()
            ->groupBy(function ($item) {
                return $item->waktu_tanding->format('Y');
            });

        // Ambil tournament yang sudah selesai
        $tournamentsQuery = Tournament::with(['sport', 'pertandingans.teamA', 'pertandingans.teamB', 'pertandingans.winner', 'pertandingans.games'])
            ->withCount('teams')
            ->whereHas('pertandingans', function ($q) {
                $q->where('status', 'finished');
            });

        if ($selectedYear !== 'all') {
            $tournamentsQuery->where('year', $selectedYear);
        }

        if ($selectedSportId !== 'all') {
            $tournamentsQuery->where('sport_id', $selectedSportId);
        }

        $tournaments = $tournamentsQuery->orderBy('year', 'desc')->get();

        $pertandinganYears = Pertandingan::where('status', 'finished')
            ->select('waktu_tanding')
            ->get()
            ->map(function ($item) {
                return $item->waktu_tanding->format('Y');
            })
            ->unique();

        $tournamentYears = Tournament::select('year')
            ->pluck('year')
            ->unique();

        $years = $pertandinganYears->merge($tournamentYears)
            ->unique()
            ->sortDesc()
            ->values();

        $sports = Sport::all();

        return view('history', compact('history', 'years', 'selectedYear', 'tournaments', 'selectedTournament', 'sports', 'selectedSportId'));
    }

    public function store(Request $request)
    {
        $pertandingan = Pertandingan::create([
            'sport_id' => $request->sport_id,
            'team_a_id' => $request->team_a,
            'team_b_id' => $request->team_b,
            'waktu_tanding' => $request->waktu,
            'lokasi' => $request->lokasi,
            'keterangan' => $request->keterangan,
            'format_tanding' => in_array($request->format_tanding, ['BO1', 'BO3']) ? $request->format_tanding : 'BO1',
            'status' => 'scheduled',
        ]);

        // Broadcast event pertandingan baru
        broadcast(new \App\Events\MatchCreated($pertandingan));

        return redirect()->route('admin.index')->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function manageScore()
    {
        $pertandingans = Pertandingan::with(['teamA', 'teamB', 'sport', 'games', 'tournament'])
            ->orderBy('status', 'asc') // live akan muncul lebih dulu
            ->orderBy('waktu_tanding', 'asc') // yang paling awal/jadul dulu
            ->get();

        $groupedMatches = $pertandingans->groupBy(function ($item) {
            return $item->tournament_id ? 'tournament_' . $item->tournament_id : 'independent';
        });

        $tournaments = Tournament::with(['sport'])->whereHas('pertandingans', function ($q) {
            $q->whereIn('status', ['live', 'scheduled']);
        })->get();

        return view('admin.skor', compact('pertandingans', 'groupedMatches', 'tournaments'));
    }

    public function updateScore(Request $request, Pertandingan $pertandingan)
    {
        // Debug file uploads
        \Illuminate\Support\Facades\Log::info('Fungsi updateScore dipanggil oleh admin', [
            'pertandingan_id' => $pertandingan->id,
            'format_tanding' => $pertandingan->format_tanding,
            'semua_keys_input' => array_keys($request->all()),
            'punya_screenshot' => $request->hasFile('screenshot') ? 'YA' : 'TIDAK',
            'punya_game_screenshots' => $request->hasFile('game_screenshots') ? 'YA' : 'TIDAK',
            'daftar_file' => array_map(function($f) {
                return [
                    'original_name' => $f->getClientOriginalName(),
                    'mime_type' => $f->getClientMimeType(),
                    'size_kb' => $f->getSize() / 1024,
                    'error_code' => $f->getError(),
                    'error_message' => $f->getErrorMessage(),
                ];
            }, $request->allFiles()),
        ]);

        // Cegah update skor jika salah satu tim masih TBD
        if (!$pertandingan->team_a_id || !$pertandingan->team_b_id) {
            return back()->with('error', 'Tidak bisa update skor — salah satu tim masih TBD. Selesaikan pertandingan sebelumnya terlebih dahulu.');
        }

        $request->validate([
            'score_a'              => 'required|integer',
            'score_b'              => 'required|integer',
            'status'               => 'required|string',
            'keterangan'           => 'nullable|string|max:255',
            'screenshot'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'game_scores'          => 'nullable|array',
            'game_screenshots'     => 'nullable|array',
            'game_screenshots.*'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $updateData = [
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'status'  => $request->status,
        ];

        // Add keterangan if provided
        if ($request->has('keterangan')) {
            $updateData['keterangan'] = $request->keterangan;
        }

        // Persiapkan detail folder bertingkat: [Tahun] / [Nama Sport] / [Tanggal]
        $sportName = $pertandingan->sport->nama_sport ?? 'Sport';
        $year = $pertandingan->waktu_tanding ? $pertandingan->waktu_tanding->format('Y') : now()->format('Y');
        $date = $pertandingan->waktu_tanding ? $pertandingan->waktu_tanding->format('d-m-Y') : now()->format('d-m-Y');

        $cleanSportName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $sportName);
        $cleanYear = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $year);
        $cleanDate = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $date);

        $localFolder = "{$cleanYear}/{$cleanSportName}/{$cleanDate}";
        $driveFolderPath = [$cleanYear, $cleanSportName, $cleanDate];

        // Handle Screenshot Utama (untuk pertandingan independen / BO1)
        if ($request->hasFile('screenshot')) {
            if ($pertandingan->screenshot && file_exists(public_path('storage/' . $pertandingan->screenshot))) {
                @unlink(public_path('storage/' . $pertandingan->screenshot));
            }
            
            $file = $request->file('screenshot');
            $timestamp = time();
            $extension = $file->getClientOriginalExtension();
            $fileName = "{$cleanSportName} - {$cleanDate}_{$timestamp}.{$extension}";
            
            // Simpan lokal di storage/app/public/[Tahun]/[Nama Sport]/[Tanggal]/
            $path = $file->storeAs($localFolder, $fileName, 'public');
            $updateData['screenshot'] = $path;

            // Upload ke Google Drive ke dalam folder bertingkat: [Tahun] / [Nama Sport] / [Tanggal]
            try {
                $driveService = app(\App\Services\GoogleDriveService::class);
                $absolutePath = public_path('storage/' . $path);
                $driveService->uploadFileToNestedFolders($absolutePath, $fileName, $driveFolderPath);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengunggah screenshot utama ke Google Drive: ' . $e->getMessage());
            }
        }

        // Handle BO3 Game Scores
        if ($request->has('game_scores')) {
            foreach ($request->game_scores as $gameNum => $scores) {
                $pertandingan->games()->updateOrCreate(
                    ['game_number' => $gameNum],
                    [
                        'score_a'   => $scores['a'] ?? 0,
                        'score_b'   => $scores['b'] ?? 0,
                        'winner_id' => ($scores['a'] ?? 0) > ($scores['b'] ?? 0)
                            ? $pertandingan->team_a_id
                            : (($scores['b'] ?? 0) > ($scores['a'] ?? 0) ? $pertandingan->team_b_id : null),
                    ]
                );
            }
        }

        // Handle BO3 Game Screenshots — independen dari game_scores
        // Loop manual game 1-3, upload jika ada file yang dikirim
        if ($pertandingan->format_tanding === 'BO3') {
            for ($gameNum = 1; $gameNum <= 3; $gameNum++) {
                if ($request->hasFile("game_screenshots.$gameNum")) {
                    $game = $pertandingan->games()->firstOrCreate(
                        ['game_number' => $gameNum],
                        ['score_a' => 0, 'score_b' => 0, 'winner_id' => null]
                    );

                    if ($game->screenshot && file_exists(public_path('storage/' . $game->screenshot))) {
                        @unlink(public_path('storage/' . $game->screenshot));
                    }

                    $file = $request->file("game_screenshots.$gameNum");
                    $timestamp = time();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = "{$cleanSportName} - {$cleanDate}_game_{$gameNum}_{$timestamp}.{$extension}";

                    // Simpan lokal di storage/app/public/[Tahun]/[Nama Sport]/[Tanggal]/
                    $path = $file->storeAs($localFolder, $fileName, 'public');
                    $game->update(['screenshot' => $path]);

                    // Upload ke Google Drive ke dalam folder bertingkat secara otomatis
                    try {
                        $driveService = app(\App\Services\GoogleDriveService::class);
                        $absolutePath = public_path('storage/' . $path);
                        $driveService->uploadFileToNestedFolders($absolutePath, $fileName, $driveFolderPath);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Gagal mengunggah screenshot game {$gameNum} ke Google Drive: " . $e->getMessage());
                    }
                }
            }
        }

        // Logika Pengarsipan Otomatis & Auto-Advance Bracket
        if ($request->status == 'finished' && $pertandingan->status != 'finished') {
            $updateData['selesai_pada'] = now();

            if ($request->score_a > $request->score_b) {
                $updateData['winner_id'] = $pertandingan->team_a_id;
            } elseif ($request->score_b > $request->score_a) {
                $updateData['winner_id'] = $pertandingan->team_b_id;
            }

            $pertandingan->update($updateData);

            if ($pertandingan->tournament_id && $pertandingan->next_match_id && isset($updateData['winner_id'])) {
                $nextMatch = Pertandingan::find($pertandingan->next_match_id);
                $loserId = ($updateData['winner_id'] == $pertandingan->team_a_id) ? $pertandingan->team_b_id : $pertandingan->team_a_id;

                if ($nextMatch) {
                    if ($pertandingan->match_number % 2 != 0) {
                        $nextMatch->update(['team_a_id' => $updateData['winner_id']]);
                    } else {
                        $nextMatch->update(['team_b_id' => $updateData['winner_id']]);
                    }
                }

                // Jika ini Semi Final, kirim yang kalah ke Perebutan Juara 3
                if ($pertandingan->babak == 'Semi Final') {
                    $thirdPlaceMatch = Pertandingan::where('tournament_id', $pertandingan->tournament_id)
                        ->where('babak', 'Perebutan Juara 3')
                        ->first();

                    if ($thirdPlaceMatch) {
                        if ($pertandingan->match_number % 2 != 0) {
                            $thirdPlaceMatch->update(['team_a_id' => $loserId]);
                        } else {
                            $thirdPlaceMatch->update(['team_b_id' => $loserId]);
                        }
                    }
                }
            }
        } else {
            $pertandingan->update($updateData);
        }

        // Refresh model to get latest data
        $pertandingan->refresh();

        broadcast(new ScoreUpdated($pertandingan));

        // Jika status berubah ke finished, broadcast status update juga
        if ($request->status === 'finished') {
            broadcast(new \App\Events\MatchStatusUpdated($pertandingan->id, 'finished', [
                'id'     => $pertandingan->id,
                'status' => 'finished',
                'team_a' => $pertandingan->teamA?->name,
                'team_b' => $pertandingan->teamB?->name,
                'score_a'=> $pertandingan->score_a,
                'score_b'=> $pertandingan->score_b,
                'sport'  => $pertandingan->sport?->nama_sport,
            ]));
        }

        return back()->with([
            'success' => 'Data pertandingan berhasil diperbarui!',
            'updated_id' => $pertandingan->id
        ]);
    }

    public function update(Request $request, Pertandingan $pertandingan)
    {
        $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'team_a_id' => 'nullable|exists:teams,id',
            'team_b_id' => 'nullable|exists:teams,id|different:team_a_id',
            'waktu_tanding' => 'required|date',
            'lokasi' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $pertandingan->update([
            'sport_id' => $request->sport_id,
            'team_a_id' => $request->team_a_id, // Bisa null untuk TBD
            'team_b_id' => $request->team_b_id, // Bisa null untuk TBD
            'waktu_tanding' => $request->waktu_tanding,
            'lokasi' => $request->lokasi,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.index')->with('success', 'Pertandingan berhasil diperbarui!');
    }

    public function show(Pertandingan $pertandingan)
    {
        $pertandingan->load(['teamA', 'teamB', 'sport', 'games.winner']);
        return view('detail', compact('pertandingan'));
    }

    public function deleteTournament(\App\Models\Tournament $tournament)
    {
        $tournament->delete();
        return back()->with('success', 'Turnamen dan semua pertandingan terkait berhasil dihapus!');
    }

    /**
     * Render the upload diagnostic test page.
     */
    public function testUploadPage()
    {
        return view('admin.test-upload');
    }

    /**
     * Run step-by-step diagnostic tests for file upload and Google Drive.
     */
    public function handleTestUpload(Request $request)
    {
        $request->validate([
            'test_file' => 'required|file|max:10240',
        ]);

        $log = "";
        $log .= "<span class='text-cyan'>[INFO] Memulai tes diagnostik upload...</span><br>";
        
        // --- STEP 1: PHP Environment & Permissions ---
        $log .= "<br><span class='text-white font-weight-bold'>[LANGKAH 1] Memeriksa Lingkungan PHP & Perizinan</span><br>";
        $maxUpload = ini_get('upload_max_filesize');
        $maxPost = ini_get('post_max_size');
        $log .= "&nbsp;&nbsp;» upload_max_filesize: <span class='text-info'>{$maxUpload}</span><br>";
        $log .= "&nbsp;&nbsp;» post_max_size: <span class='text-info'>{$maxPost}</span><br>";
        
        $storageDir = storage_path('app/public');
        $log .= "&nbsp;&nbsp;» Folder storage fisik: <span class='text-muted'>{$storageDir}</span> ";
        if (is_dir($storageDir)) {
            $log .= "(<span class='text-success'>ADA</span>)<br>";
            if (is_writable($storageDir)) {
                $log .= "&nbsp;&nbsp;» Izin tulis folder storage: <span class='text-success'>BISA DITULIS (Writable)</span><br>";
            } else {
                $log .= "&nbsp;&nbsp;» Izin tulis folder storage: <span class='text-danger'>ERROR (Tidak bisa menulis, cek hak akses)</span><br>";
            }
        } else {
            $log .= "(<span class='text-danger'>TIDAK ADA</span>)<br>";
        }

        $symlinkPath = public_path('storage');
        $log .= "&nbsp;&nbsp;» Folder link publik (symlink): <span class='text-muted'>{$symlinkPath}</span> ";
        if (file_exists($symlinkPath)) {
            $log .= "(<span class='text-success'>ADA / TERHUBUNG</span>)<br>";
        } else {
            $log .= "(<span class='text-danger'>TIDAK ADA (Bisa menyebabkan foto 404)</span>)<br>";
        }

        // --- STEP 2: Local Storage Write Test ---
        $log .= "<br><span class='text-white font-weight-bold'>[LANGKAH 2] Pengujian Unggah ke Penyimpanan Lokal</span><br>";
        try {
            $file = $request->file('test_file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $timestamp = time();
            $fileName = "test_file_{$timestamp}.{$extension}";
            
            $log .= "&nbsp;&nbsp;» Informasi File: <span class='text-info'>{$originalName}</span> ({$file->getSize()} bytes)<br>";
            
            // Simpan lokal di storage/app/public/test/
            $path = $file->storeAs('test', $fileName, 'public');
            
            if ($path && file_exists(public_path('storage/' . $path))) {
                $log .= "&nbsp;&nbsp;» Unggah Lokal: <span class='text-success'>SUKSES!</span><br>";
                $log .= "&nbsp;&nbsp;» Path database: <span class='text-info'>{$path}</span><br>";
                $log .= "&nbsp;&nbsp;» URL Akses: <a href='" . asset('storage/' . $path) . "' target='_blank' class='text-success'>" . asset('storage/' . $path) . "</a><br>";
            } else {
                $log .= "&nbsp;&nbsp;» Unggah Lokal: <span class='text-danger'>GAGAL! Berkas tidak ditemukan di folder public/storage setelah diunggah.</span><br>";
            }
        } catch (\Exception $e) {
            $log .= "&nbsp;&nbsp;» Unggah Lokal: <span class='text-danger'>ERROR - " . $e->getMessage() . "</span><br>";
        }

        // --- STEP 3: Google Drive Credentials sanity ---
        $log .= "<br><span class='text-white font-weight-bold'>[LANGKAH 3] Memeriksa Kredensial Google Drive</span><br>";
        $clientEmail = config('services.google_drive.client_email');
        $privateKey = config('services.google_drive.private_key');
        $parentFolderId = config('services.google_drive.parent_folder_id');

        $log .= "&nbsp;&nbsp;» Client Email: " . ($clientEmail ? "<span class='text-success'>TERSEDIA ({$clientEmail})</span>" : "<span class='text-danger'>KOSONG (Wajib diisi di .env)</span>") . "<br>";
        $log .= "&nbsp;&nbsp;» Private Key: " . ($privateKey ? "<span class='text-success'>TERSEDIA (Panjang: " . strlen($privateKey) . " karakter)</span>" : "<span class='text-danger'>KOSONG (Wajib diisi di .env)</span>") . "<br>";
        $log .= "&nbsp;&nbsp;» Parent Folder ID: " . ($parentFolderId ? "<span class='text-success'>TERSEDIA ({$parentFolderId})</span>" : "<span class='text-warning'>KOSONG (Abaikan jika hanya ingin simpan lokal)</span>") . "<br>";

        // --- STEP 4: Token Generation ---
        $log .= "<br><span class='text-white font-weight-bold'>[LANGKAH 4] Menguji Pembuatan Token Akses (Authentication)</span><br>";
        $driveService = app(\App\Services\GoogleDriveService::class);
        $accessToken = null;
        try {
            $accessToken = $driveService->getAccessToken();
            if ($accessToken) {
                $log .= "&nbsp;&nbsp;» Token Akses: <span class='text-success'>SUKSES didapatkan!</span><br>";
                $log .= "&nbsp;&nbsp;» Kode token: <span class='text-muted'>" . substr($accessToken, 0, 20) . "...</span><br>";
            } else {
                $log .= "&nbsp;&nbsp;» Token Akses: <span class='text-danger'>GAGAL didapatkan! Periksa berkas log laravel untuk detail pesan error.</span><br>";
            }
        } catch (\Exception $e) {
            $log .= "&nbsp;&nbsp;» Token Akses: <span class='text-danger'>ERROR - " . $e->getMessage() . "</span><br>";
        }

        // --- STEP 5: Google Drive Nested Folder & Upload Test ---
        if ($accessToken && $parentFolderId) {
            $log .= "<br><span class='text-white font-weight-bold'>[LANGKAH 5] Menguji Pengunggahan ke Google Drive</span><br>";
            try {
                $absolutePath = public_path('storage/' . $path);
                $testFolderPath = ['Test-Tahun-' . date('Y'), 'Test-Cabor-Diagnostic', 'Test-Tanggal-' . date('d-m-Y')];
                
                $log .= "&nbsp;&nbsp;» Mencoba membuat folder bertingkat di Google Drive: <span class='text-info'>" . implode(' / ', $testFolderPath) . "</span>...<br>";
                
                $driveFileId = $driveService->uploadFileToNestedFolders($absolutePath, "DIAGNOSTIC_TEST_{$fileName}", $testFolderPath);
                
                if ($driveFileId) {
                    $log .= "&nbsp;&nbsp;» Unggah Google Drive: <span class='text-success'>SUKSES BESAR!</span><br>";
                    $log .= "&nbsp;&nbsp;» File ID Google Drive: <span class='text-info font-weight-bold'>{$driveFileId}</span><br>";
                    $log .= "&nbsp;&nbsp;» URL Drive: <a href='https://drive.google.com/file/d/{$driveFileId}/view' target='_blank' class='text-success'>Buka File di Google Drive</a><br>";
                } else {
                    $log .= "&nbsp;&nbsp;» Unggah Google Drive: <span class='text-danger'>GAGAL! Layanan tidak mengembalikan File ID.</span><br>";
                    $log .= "&nbsp;&nbsp;» Detail Error: <span class='text-warning'>" . htmlspecialchars($driveService->lastError ?: 'Gagal tanpa pesan spesifik (cek izin hak akses Service Account ke folder).') . "</span><br>";
                }
            } catch (\Exception $e) {
                $log .= "&nbsp;&nbsp;» Unggah Google Drive: <span class='text-danger'>ERROR - " . $e->getMessage() . "</span><br>";
            }
        } else {
            $log .= "<br><span class='text-warning'>[LANGKAH 5] Tes Google Drive dilewati karena Token Akses atau Parent Folder ID kosong.</span><br>";
        }

        $log .= "<br><span class='text-cyan'>[INFO] Diagnostik selesai dilakukan.</span><br>";

        // Delete test local file afterwards to avoid clutter
        if (isset($path) && file_exists(public_path('storage/' . $path))) {
            @unlink(public_path('storage/' . $path));
        }

        return back()->with('diagnostic_log', $log);
    }
}
