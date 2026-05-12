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
        // Auto-update status scheduled ke live jika waktu sudah terlewati
        Pertandingan::autoUpdateLiveStatus();

        $selectedSport = request('sport_id');

        // Tampilkan semua match yang sedang live atau terjadwal (termasuk dari tournament).
        $query = Pertandingan::with(['teamA', 'teamB', 'sport', 'games'])
            ->whereIn('status', ['live', 'scheduled']);

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
        // Auto-update status scheduled ke live jika waktu sudah terlewati
        Pertandingan::autoUpdateLiveStatus();

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
        // Auto-update status scheduled ke live jika waktu sudah terlewati
        Pertandingan::autoUpdateLiveStatus();

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
        $request->validate([
            'score_a' => 'required|integer',
            'score_b' => 'required|integer',
            'status' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'game_scores' => 'nullable|array',
            'game_screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $updateData = [
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'status' => $request->status,
        ];

        // Add keterangan if provided
        if ($request->has('keterangan')) {
            $updateData['keterangan'] = $request->keterangan;
        }

        // Handle Screenshot Utama
        if ($request->hasFile('screenshot')) {
            if ($pertandingan->screenshot && file_exists(public_path('storage/' . $pertandingan->screenshot))) {
                unlink(public_path('storage/' . $pertandingan->screenshot));
            }
            $path = $request->file('screenshot')->store('screenshots', 'public');
            $updateData['screenshot'] = $path;
        }

        // Handle BO3 Games Data
        if ($request->has('game_scores')) {
            foreach ($request->game_scores as $gameNum => $scores) {
                $game = $pertandingan->games()->updateOrCreate(
                    ['game_number' => $gameNum],
                    [
                        'score_a' => $scores['a'] ?? 0,
                        'score_b' => $scores['b'] ?? 0,
                        'winner_id' => ($scores['a'] ?? 0) > ($scores['b'] ?? 0) ? $pertandingan->team_a_id : (($scores['b'] ?? 0) > ($scores['a'] ?? 0) ? $pertandingan->team_b_id : null),
                    ]
                );

                // Handle Game Screenshot
                if ($request->hasFile("game_screenshots.$gameNum")) {
                    if ($game->screenshot && file_exists(public_path('storage/' . $game->screenshot))) {
                        unlink(public_path('storage/' . $game->screenshot));
                    }
                    $path = $request->file("game_screenshots.$gameNum")->store('screenshots/games', 'public');
                    $game->update(['screenshot' => $path]);
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
        
        // Debug: Log broadcast attempt
        \Log::info('Broadcasting ScoreUpdated', [
            'match_id' => $pertandingan->id,
            'score_a' => $pertandingan->score_a,
            'score_b' => $pertandingan->score_b,
            'status' => $pertandingan->status,
        ]);
        
        broadcast(new ScoreUpdated($pertandingan));
        
        // Jika status berubah ke finished, broadcast status update juga
        if ($request->status === 'finished') {
            \Log::info('Broadcasting MatchStatusUpdated for finished match', [
                'match_id' => $pertandingan->id,
            ]);
            
            broadcast(new \App\Events\MatchStatusUpdated($pertandingan->id, 'finished', [
                'id' => $pertandingan->id,
                'status' => 'finished',
                'team_a' => $pertandingan->teamA?->name,
                'team_b' => $pertandingan->teamB?->name,
                'score_a' => $pertandingan->score_a,
                'score_b' => $pertandingan->score_b,
                'sport' => $pertandingan->sport?->nama_sport,
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

    public function generateBracket(Request $request)
    {
        $request->validate([
            'tournament_name' => 'required|string',
            'sport_id' => 'required|exists:sports,id',
            'team_ids' => 'nullable|array',
            'manual_team_count' => 'nullable|integer|in:4,8,16',
            'keterangan' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format_tanding' => 'nullable|in:BO1,BO3',
        ]);

        return DB::transaction(function () use ($request) {
            $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now();
            $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now()->addDays(7);
            $formatTanding = in_array($request->format_tanding, ['BO1', 'BO3']) ? $request->format_tanding : 'BO1';
            
            $tournament = Tournament::create([
                'name' => $request->tournament_name,
                'sport_id' => $request->sport_id,
                'type' => 'single_elimination',
                'year' => date('Y'),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $teamIds = $request->team_ids ?? [];
            if ($request->manual_team_count && count($teamIds) == 0) {
                $numTeams = (int) $request->manual_team_count;
            } else {
                $numTeams = count($teamIds);
                shuffle($teamIds);
                $tournament->teams()->attach($teamIds);
            }

            if ($numTeams < 2) {
                return back()->with('error', 'Minimal pilih 2 tim atau tentukan jumlah tim manual.');
            }

            $numRounds = ceil(log($numTeams, 2));
            $totalMatchesNeeded = pow(2, $numRounds) - 1;

            $matches = [];
            $matchIndex = 1;

            // Buat semua placeholder match dulu dari babak akhir ke awal agar bisa link next_match_id
            // Tapi lebih mudah buat per babak dan simpan ref-nya.

            $roundMatches = [];
            $totalDays = $endDate->diffInDays($startDate);
            $daysPerRound = $totalDays / ($numRounds + 1);

            // 1. Buat struktur match kosong untuk setiap babak
            for ($r = $numRounds; $r >= 1; $r--) {
                $numMatchesInRound = pow(2, $numRounds - $r);
                $roundMatches[$r] = [];
                
                // Hitung tanggal untuk round ini (Round 1 paling awal)
                $roundDayOffset = ($numRounds - $r + 1) * $daysPerRound;
                $roundDate = $startDate->copy()->addDays($roundDayOffset);

                for ($m = 1; $m <= $numMatchesInRound; $m++) {
                    $nextMatch = null;
                    if ($r < $numRounds) {
                        $nextMatchIndex = ceil($m / 2) - 1;
                        $nextMatch = $roundMatches[$r + 1][$nextMatchIndex];
                    }

                    $babakName = $this->getBabakName($r, $numRounds);
                    
                    // Tambahkan offset jam untuk setiap match
                    $matchHour = 9 + (($m - 1) % 3) * 4; // 9:00, 13:00, 17:00
                    $matchDateTime = $roundDate->copy()->setTime($matchHour, 0);

                    $match = Pertandingan::create([
                        'sport_id' => $request->sport_id,
                        'tournament_id' => $tournament->id,
                        'round' => $r,
                        'match_number' => $m,
                        'next_match_id' => $nextMatch ? $nextMatch->id : null,
                        'status' => 'scheduled',
                        'babak' => $babakName,
                        'format_tanding' => $formatTanding,
                        'waktu_tanding' => $matchDateTime,
                        'match_date' => $matchDateTime,
                        'lokasi' => 'TBA',
                        'keterangan' => $request->keterangan,
                    ]);

                    $roundMatches[$r][] = $match;
                }
            }

            // 1.5. Buat Perebutan Juara 3 (Bronze Match) jika ada minimal 4 tim (Semi Final)
            if ($numRounds >= 2) {
                $bronzeDate = $startDate->copy()->addDays($totalDays - 1)->setTime(14, 0);
                Pertandingan::create([
                    'sport_id' => $request->sport_id,
                    'tournament_id' => $tournament->id,
                    'round' => $numRounds - 1, // Sama level dengan Semi Final tapi tidak lanjut ke Final
                    'match_number' => 99, // Special number for 3rd place match
                    'status' => 'scheduled',
                    'babak' => 'Perebutan Juara 3',
                    'format_tanding' => $formatTanding,
                    'waktu_tanding' => $bronzeDate,
                    'match_date' => $bronzeDate,
                    'lokasi' => 'TBA',
                    'keterangan' => $request->keterangan,
                ]);
            }

            // 2. Isi Round 1 dengan tim yang ada
            $round1Matches = array_reverse($roundMatches[1]); // Karena kita buat r=1 terakhir di loop
            // Tunggu, loop saya r=numRounds down to 1. Jadi r=1 adalah yang terakhir dibuat.
            // Mari perbaiki urutan loop agar r=1 dibuat pertama atau simpan dengan benar.

            // Re-logic:
            // Round 1 (paling banyak match) -> Round Final (1 match)
            // Tapi untuk link next_match_id, kita butuh match di Round r+1 sudah ada.
            // Jadi buat Final dulu (Round numRounds), lalu Semi-Final, dst.

            // Isi Round 1 (yang dibuat terakhir di loop r=numRounds down to 1)
            $r1Matches = $roundMatches[1];
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

            return redirect()->route('admin.index')->with('success', 'Bracket Tournament berhasil digenerate!');
        });
    }

    private function getBabakName($round, $totalRounds)
    {
        $diff = $totalRounds - $round;
        if ($diff == 0)
            return 'Final';
        if ($diff == 1)
            return 'Semi Final';
        if ($diff == 2)
            return 'Quarter Final';
        return 'Babak ' . $round;
    }

    public function deleteTournament(\App\Models\Tournament $tournament)
    {
        $tournament->delete();
        return back()->with('success', 'Turnamen berhasil dihapus!');
    }

    /**
     * Hapus semua data pertandingan (games, pertandingans, tournaments) saja
     * Data master (users, teams, sports) tetap aman
     */
    public function clearAllMatches()
    {
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Hapus games
            $gamesCount = \App\Models\MatchGame::count();
            \App\Models\MatchGame::query()->delete();
            
            // Hapus pertandingans
            $matchesCount = \App\Models\Pertandingan::count();
            \App\Models\Pertandingan::query()->delete();
            
            // Hapus tournaments
            $tournamentsCount = \App\Models\Tournament::count();
            \App\Models\Tournament::query()->delete();
            
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return back()->with('success', 
                "Berhasil hapus: {$gamesCount} games, {$matchesCount} pertandingan, {$tournamentsCount} tournament. " .
                "Data master (tim, sport, admin) tetap aman."
            );
        } catch (\Exception $e) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
}
