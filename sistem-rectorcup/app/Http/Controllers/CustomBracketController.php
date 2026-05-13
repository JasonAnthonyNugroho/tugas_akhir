<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\Pertandingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomBracketController extends Controller
{
    /**
     * Tampilkan halaman bracket builder
     */
    public function builder()
    {
        $sports = Sport::all();
        $teams = Team::where('name', '!=', 'Seluruh Prodi')
            ->orderBy('prodi')
            ->orderBy('name')
            ->get()
            ->groupBy('prodi');
        
        return view('admin.bracket-builder', compact('sports', 'teams'));
    }


    /**
     * Tampilkan halaman arrange bracket (drag & drop) - page terpisah
     */
    public function showArrange(Request $request)
    {
        $request->validate([
            'tournament_name'    => 'required|string|max:255',
            'sport_id'           => 'required|exists:sports,id',
            'bracket_size'       => 'required|integer|in:4,8,16,32',
            'team_ids'           => 'required|array|min:2',
            'team_ids.*'         => 'exists:teams,id',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'external_score_url' => 'nullable|url|max:500',
            'format_tanding'     => 'nullable|in:BO1,BO3',
            'lokasi'             => 'nullable|string|max:255',
        ]);

        $sport        = Sport::find($request->sport_id);
        $selectedTeams = Team::whereIn('id', $request->team_ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $request->team_ids) . ')')
            ->get();

        return view('admin.bracket-arrange', [
            'tournamentName'   => $request->tournament_name,
            'sportId'          => $request->sport_id,
            'bracketSize'      => (int) $request->bracket_size,
            'keterangan'       => $request->keterangan,
            'lokasi'           => $request->lokasi,
            'startDate'        => $request->start_date,
            'endDate'          => $request->end_date,
            'externalScoreUrl' => $request->external_score_url,
            'formatTanding'    => $request->format_tanding ?? 'BO1',
            'sport'            => $sport,
            'selectedTeams'    => $selectedTeams,
            'teamIds'          => $request->team_ids,
        ]);
    }

    /**
     * Save custom bracket dengan arrangement dari drag-drop
     */
    public function store(Request $request)
    {
        // Arrangement dikirim sebagai JSON string dari JavaScript, decode dulu
        if (is_string($request->arrangement)) {
            $request->merge(['arrangement' => json_decode($request->arrangement, true)]);
        }

        $request->validate([
            'tournament_name'    => 'required|string|max:255',
            'sport_id'           => 'required|exists:sports,id',
            'arrangement'        => 'required|array', // Format: [match_index => [team_a_id, team_b_id]]
            'bracket_size'       => 'required|integer|in:4,8,16,32',
            'keterangan'         => 'nullable|string|max:500',
            'lokasi'             => 'nullable|string|max:255',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'external_score_url' => 'nullable|url|max:500',
            'format_tanding'     => 'nullable|in:BO1,BO3',
        ]);

        return DB::transaction(function () use ($request) {
            // Buat tournament
            $tournament = Tournament::create([
                'name'               => $request->tournament_name,
                'sport_id'           => $request->sport_id,
                'type'               => 'single_elimination',
                'is_active'          => true,
                'year'               => date('Y'),
                'start_date'         => $request->start_date,
                'end_date'           => $request->end_date,
                'external_score_url' => $request->external_score_url,
            ]);

            // Attach teams ke tournament
            $teamIds = collect($request->arrangement)->flatten()->unique()->filter()->values();
            $tournament->teams()->attach($teamIds);

            $bracketSize = $request->bracket_size;
            $numRounds = log($bracketSize, 2);
            $formatTanding = in_array($request->format_tanding, ['BO1', 'BO3']) ? $request->format_tanding : 'BO1';

            // Generate matches dengan arrangement yang sudah di-set
            $lokasi = $request->lokasi ?: 'TBA';
            $this->createBracketMatches($tournament, $request->sport_id, $bracketSize, $numRounds, $request->arrangement, $request->keterangan, $formatTanding, $lokasi);

            return redirect()->route('admin.tournament.bracket.view', $tournament)
                ->with('success', 'Bracket berhasil di-generate! 🎉');
        });
    }

    /**
     * View bracket yang sudah dibuat (Liquipedia style)
     */
    public function viewBracket(Tournament $tournament)
    {
        $tournament->load(['sport', 'teams', 'pertandingans.teamA', 'pertandingans.teamB', 'pertandingans.winner']);
        
        // Group matches by round
        $rounds = $tournament->pertandingans
            ->where('babak', '!=', 'Perebutan Juara 3')
            ->groupBy('round')
            ->sortKeys();
        
        // Get 3rd place match if exists
        $thirdPlaceMatch = $tournament->pertandingans
            ->where('babak', 'Perebutan Juara 3')
            ->first();

        return view('admin.bracket-view', compact('tournament', 'rounds', 'thirdPlaceMatch'));
    }

    /**
     * Public view bracket untuk guest
     */
    public function publicBracket(Tournament $tournament)
    {
        $tournament->load(['sport', 'teams', 'pertandingans.teamA', 'pertandingans.teamB', 'pertandingans.winner']);
        
        // Group matches by round
        $rounds = $tournament->pertandingans
            ->where('babak', '!=', 'Perebutan Juara 3')
            ->groupBy('round')
            ->sortKeys();
        
        // Get 3rd place match if exists
        $thirdPlaceMatch = $tournament->pertandingans
            ->where('babak', 'Perebutan Juara 3')
            ->first();

        return view('public.bracket-view', compact('tournament', 'rounds', 'thirdPlaceMatch'));
    }


    /**
     * Create actual matches di database dengan distribusi tanggal
     */
    private function createBracketMatches($tournament, $sportId, $bracketSize, $numRounds, $arrangement, $keterangan, $formatTanding = 'BO1', $lokasi = 'TBA')
    {
        $roundMatches = [];
        
        // Parse tanggal tournament
        $startDate = \Carbon\Carbon::parse($tournament->start_date);
        $endDate = \Carbon\Carbon::parse($tournament->end_date);
        $totalDays = $endDate->diffInDays($startDate);
        
        // Distribusi: Round 1 di awal, Final di akhir
        $daysPerRound = $totalDays / ($numRounds + 1);

        // Buat matches dari Final ke Round 1
        for ($round = $numRounds; $round >= 1; $round--) {
            $numMatches = $bracketSize / pow(2, $round);
            $roundMatches[$round] = [];
            
            // Hitung tanggal untuk round ini (Round 1 paling awal)
            $roundDayOffset = ($numRounds - $round + 1) * $daysPerRound;
            $roundDate = $startDate->copy()->addDays($roundDayOffset);

            for ($matchNum = 1; $matchNum <= $numMatches; $matchNum++) {
                $nextMatch = null;
                if ($round < $numRounds) {
                    $parentMatchIndex = ceil($matchNum / 2) - 1;
                    $nextMatch = $roundMatches[$round + 1][$parentMatchIndex] ?? null;
                }
                
                // Tambahkan offset jam untuk setiap match (misal match 1 jam 9, match 2 jam 13, dll)
                $matchHour = 9 + (($matchNum - 1) % 3) * 4; // 9:00, 13:00, 17:00
                $matchDateTime = $roundDate->copy()->setTime($matchHour, 0);

                $match = Pertandingan::create([
                    'sport_id' => $sportId,
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'match_number' => $matchNum,
                    'next_match_id' => $nextMatch ? $nextMatch->id : null,
                    'status' => 'scheduled',
                    'babak' => $this->getBabakName($round, $numRounds),
                    'format_tanding' => $formatTanding,
                    'waktu_tanding' => $matchDateTime,
                    'match_date' => $matchDateTime,
                    'lokasi' => $lokasi,
                    'keterangan' => $keterangan,
                ]);

                $roundMatches[$round][] = $match;
            }
        }

        // Isi Round 1 dengan arrangement dari drag-drop
        $round1Matches = $roundMatches[1];
        foreach ($arrangement as $index => $teamIds) {
            if (isset($round1Matches[$index])) {
                $round1Matches[$index]->update([
                    'team_a_id' => $teamIds[0] ?? null,
                    'team_b_id' => $teamIds[1] ?? null,
                ]);
            }
        }

        // Buat match Perebutan Juara 3 jika ada minimal 4 tim
        if ($bracketSize >= 4) {
            Pertandingan::create([
                'sport_id' => $sportId,
                'tournament_id' => $tournament->id,
                'round' => $numRounds - 1,
                'match_number' => 99,
                'next_match_id' => null,
                'status' => 'scheduled',
                'babak' => 'Perebutan Juara 3',
                'format_tanding' => $formatTanding,
                'waktu_tanding' => now()->addDays($numRounds),
                'lokasi' => 'TBA',
                'keterangan' => $keterangan,
            ]);
        }
    }

    /**
     * Get nama babak berdasarkan round
     */
    private function getBabakName($round, $totalRounds)
    {
        $diff = $totalRounds - $round;
        
        switch ($diff) {
            case 0: return 'Grand Final';
            case 1: return 'Semi Final';
            case 2: return 'Quarter Final';
            case 3: return 'Round of 16';
            case 4: return 'Round of 32';
            default: return 'Round ' . $round;
        }
    }

    /**
     * Update tournament details (name, dates, lokasi)
     */
    public function updateTournament(Request $request, Tournament $tournament)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'lokasi'     => 'nullable|string|max:255',
        ]);

        $tournament->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        // Update lokasi di semua pertandingan turnamen ini
        if ($request->filled('lokasi')) {
            $tournament->pertandingans()->update([
                'lokasi' => $request->lokasi,
            ]);
        }

        return back()->with('success', "Tournament \"{$tournament->name}\" berhasil diperbarui!");
    }
}
