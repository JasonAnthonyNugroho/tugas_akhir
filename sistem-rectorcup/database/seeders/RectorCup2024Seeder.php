<?php

namespace Database\Seeders;

use App\Models\Pertandingan;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RectorCup2024Seeder extends Seeder
{
    private array $teamCache = [];
    private const STAGE_ALIASES = [
        'Quarter Final' => 'Perempat Final',
        'Juara 3' => 'Perebutan Juara 3',
    ];
    private const VALID_PRODIS = [
        'Kedokteran',
        'Filsafat Keilahian',
        'Manajemen',
        'Akuntansi',
        'Arsitektur',
        'Desain Produk',
        'Informatika',
        'Sistem Informasi',
        'Biologi',
        'Teknologi Pangan',
        'Pendidikan Bahasa Inggris',
        'Studi Humanitas',
    ];

    /**
     * Nama tim dari arsip → nama prodi baku (sesuai DatabaseSeeder).
     */
    private function canonicalTeamName(string $rawName): string
    {
        $name = trim($rawName);
        $upper = strtoupper(preg_replace('/\s+/', ' ', $name));

        if (in_array($name, self::VALID_PRODIS, true)) {
            return $name;
        }

        // Slot A/B per prodi (mis. Manajemen A) → entitas prodi tunggal untuk recap 2024
        if (preg_match('/^(.+)\s+[AB]$/u', $name, $m)) {
            $base = trim($m[1]);
            if (in_array($base, self::VALID_PRODIS, true)) {
                return $base;
            }
        }

        if ($upper === 'DESPRO' || str_starts_with($upper, 'DESPRO ') || $upper === 'FAD') {
            return 'Desain Produk';
        }
        if (str_contains($upper, 'HOLLY')) {
            return 'Filsafat Keilahian';
        }
        if ($upper === 'BISNIS' || preg_match('/^MENE(\s+[AB])?$/', $upper) || str_contains($upper, 'MANAJEMEN') || str_contains($upper, 'MANEJEMEN')) {
            return 'Manajemen';
        }
        if (preg_match('/^AKUN(\s+[AB])?$/', $upper) || preg_match('/^AKUNTANSI(\s+[AB])?$/', $upper)) {
            return 'Akuntansi';
        }
        if (preg_match('/^SI(\s+[AB])?$/', $upper) || str_contains($upper, 'SISTEM INFORMASI')) {
            return 'Sistem Informasi';
        }
        if (preg_match('/^FK(\s+[AB])?$/', $upper) || $upper === 'EFKA' || str_contains($upper, 'KEDOKTERAN')) {
            return 'Kedokteran';
        }
        if (preg_match('/^TI(\s+[AB])?$/', $upper) || str_contains($upper, 'INFORMATIKA')) {
            return 'Informatika';
        }
        if (str_contains($upper, 'BIOTEK')) {
            return 'Biologi';
        }
        if (preg_match('/^BIO(\s+[AB])?$/', $upper)) {
            return 'Biologi';
        }
        if (preg_match('/^ARSI(\s+[AB])?$/', $upper) || str_contains($upper, 'ARSITEKTUR')) {
            return 'Arsitektur';
        }
        if (preg_match('/^FKHUM(\s+[AB])?$/', $upper)) {
            return 'Studi Humanitas';
        }
        if (preg_match('/^TEOL(\s+[AB])?$/', $upper) || str_contains($upper, 'TEOLOGI')) {
            return 'Filsafat Keilahian';
        }
        if ($upper === 'PBI' || str_contains($upper, 'PENDIDIKAN BAHASA INGGRIS')) {
            return 'Pendidikan Bahasa Inggris';
        }

        return $name;
    }

    private function team(string $name): Team
    {
        $canonical = $this->canonicalTeamName($name);
        $key = $canonical;
        if (!isset($this->teamCache[$key])) {
            $this->teamCache[$key] = Team::firstOrCreate(
                ['name' => $canonical],
                ['prodi' => $canonical]
            );
        }
        return $this->teamCache[$key];
    }

    private function sport(string $nama): Sport
    {
        return Sport::where('nama_sport', $nama)->firstOrFail();
    }

    private function waktu(string $date, string $time): Carbon
    {
        $time = trim($time);
        if (str_contains($time, '-')) {
            $time = explode('-', $time)[0];
        }
        $time = str_replace('.', ':', $time);
        return Carbon::parse("{$date} {$time}");
    }

    private function upsertTournament(array $payload): Tournament
    {
        return Tournament::updateOrCreate(
            ['name' => $payload['name']],
            $payload
        );
    }

    private function normalizeStage(string $stage): string
    {
        return self::STAGE_ALIASES[$stage] ?? $stage;
    }

    private function buildRoundMap(array $stages): array
    {
        $ordered = ['Penyisihan', 'Perempat Final', 'Semi Final', 'Final'];
        $filtered = [];
        foreach ($ordered as $stage) {
            if (in_array($stage, $stages, true)) {
                $filtered[] = $stage;
            }
        }

        $roundMap = [];
        foreach ($filtered as $idx => $stage) {
            $roundMap[$stage] = $idx + 1;
        }

        if (isset($roundMap['Final']) && in_array('Perebutan Juara 3', $stages, true)) {
            $roundMap['Perebutan Juara 3'] = $roundMap['Final'];
        } elseif (in_array('Perebutan Juara 3', $stages, true)) {
            $roundMap['Perebutan Juara 3'] = max(1, count($roundMap));
        }

        if (in_array('8 Besar', $stages, true)) {
            $roundMap['8 Besar'] = 1;
        }

        return $roundMap;
    }

    private function seedMatches(
        Tournament $tournament,
        Sport $sport,
        array $matches,
        ?string $category = null,
        string $defaultLocation = 'TBA',
        bool $buildBracket = true
    ): void
    {
        $teamIds = [];
        $createdByStage = [];
        $normalizedStages = [];

        foreach ($matches as $m) {
            $normalizedStages[] = $this->normalizeStage($m['stage'] ?? ($m['round'] ?? 'Penyisihan'));
        }
        $roundMap = $this->buildRoundMap(array_values(array_unique($normalizedStages)));
        $stageCounters = [];

        foreach ($matches as $index => $m) {
            $teamA = $this->team($m['team_a']);
            $teamB = isset($m['team_b']) ? $this->team($m['team_b']) : null;
            $winner = isset($m['winner']) ? $this->team($m['winner']) : null;
            $time = $m['time'] ?? sprintf('%02d.%02d', 8 + intdiv($index, 2), ($index % 2) * 30);
            if (strtoupper((string) $time) === 'TBA') {
                $time = sprintf('%02d.%02d', 8 + intdiv($index, 2), ($index % 2) * 30);
            }
            $stage = $this->normalizeStage($m['stage'] ?? ($m['round'] ?? 'Penyisihan'));
            $isThirdPlace = $stage === 'Perebutan Juara 3';
            $matchNumber = $isThirdPlace ? 99 : (($stageCounters[$stage] = ($stageCounters[$stage] ?? 0) + 1));

            $hasExplicitScores = array_key_exists('score_a', $m) || array_key_exists('score_b', $m);
            if ($hasExplicitScores) {
                $scoreA = (int) ($m['score_a'] ?? 0);
                $scoreB = (int) ($m['score_b'] ?? 0);
            } elseif ($winner && $teamB) {
                if ($sport->nama_sport === 'Badminton') {
                    $scoreA = $winner->id === $teamA->id ? 2 : 0;
                    $scoreB = $winner->id === $teamB->id ? 2 : 0;
                } else {
                    $scoreA = $winner->id === $teamA->id ? 1 : 0;
                    $scoreB = $winner->id === $teamB->id ? 1 : 0;
                }
            } else {
                $scoreA = 0;
                $scoreB = 0;
            }

            $isFinished = $hasExplicitScores || ($winner !== null && $teamB !== null);

            $payload = [
                'tournament_id' => $tournament->id,
                'sport_id'      => $sport->id,
                'team_a_id'     => $teamA->id,
                'team_b_id'     => $teamB?->id,
                'babak'         => $stage,
                'waktu_tanding' => $this->waktu($m['date'], $time),
                'match_date'    => $this->waktu($m['date'], $time),
                'score_a'       => $scoreA,
                'score_b'       => $scoreB,
                'winner_id'     => $winner?->id,
                'status'        => $isFinished ? 'finished' : 'scheduled',
                'lokasi'        => $m['location'] ?? $defaultLocation,
                'keterangan'    => $category,
                'round'         => $roundMap[$stage] ?? null,
                'match_number'  => $m['match'] ?? $matchNumber,
                'next_match_id' => null,
            ];

            $pertandingan = Pertandingan::updateOrCreate(
                [
                    'tournament_id' => $payload['tournament_id'],
                    'sport_id'      => $payload['sport_id'],
                    'team_a_id'     => $payload['team_a_id'],
                    'team_b_id'     => $payload['team_b_id'],
                    'waktu_tanding' => $payload['waktu_tanding'],
                    'babak'         => $payload['babak'],
                ],
                $payload
            );

            $createdByStage[$stage][] = $pertandingan;

            $teamIds[] = $teamA->id;
            if ($teamB) {
                $teamIds[] = $teamB->id;
            }
        }

        if ($buildBracket) {
            // Link basic single-elimination path: round N -> round N+1.
            $orderedStages = ['Penyisihan', 'Perempat Final', 'Semi Final', 'Final'];
            for ($i = 0; $i < count($orderedStages) - 1; $i++) {
                $current = $orderedStages[$i];
                $next = $orderedStages[$i + 1];
                if (empty($createdByStage[$current]) || empty($createdByStage[$next])) {
                    continue;
                }

                foreach ($createdByStage[$current] as $idx => $match) {
                    $targetIdx = intdiv($idx, 2);
                    if (isset($createdByStage[$next][$targetIdx])) {
                        $match->update(['next_match_id' => $createdByStage[$next][$targetIdx]->id]);
                    }
                }
            }
        }

        if (!empty($teamIds)) {
            $tournament->teams()->syncWithoutDetaching(array_values(array_unique($teamIds)));
        }
    }

    // ──────────────────────────────────────────────
    //  SEEDERS PER SPORT
    // ──────────────────────────────────────────────
    private function seedBadmintonAndBasket(): void
    {
        $badminton = $this->sport('Badminton');
        $basket = $this->sport('Basket');

        $datasets = [
            [
                'sport' => $basket,
                'name' => 'Rector Cup 2024 - Basket Putra',
                'category' => 'Putra',
                'start_date' => '2024-10-07',
                'end_date' => '2024-10-13',
                'matches' => [
                    ["stage" => "Penyisihan", "date" => "2024-10-07", "time" => "18.50-20.00", "team_a" => "Manajemen", "team_b" => "Kedokteran", "score_a" => 45, "score_b" => 21, "winner" => "Manajemen"],
                    ["stage" => "Penyisihan", "date" => "2024-10-07", "time" => "17.40-18.50", "team_a" => "Manajemen", "team_b" => "Studi Humanitas", "score_a" => 29, "score_b" => 24, "winner" => "Manajemen"],
                    ["stage" => "Penyisihan", "date" => "2024-10-09", "time" => "18.50-20.00", "team_a" => "Biologi", "team_b" => "Filsafat Keilahian", "score_a" => 9, "score_b" => 28, "winner" => "Filsafat Keilahian"],
                    ["stage" => "Penyisihan", "date" => "2024-10-07", "time" => "16.30-17.40", "team_a" => "Sistem Informasi", "team_b" => "Desain Produk", "score_a" => 66, "score_b" => 11, "winner" => "Sistem Informasi"],
                    ["stage" => "Penyisihan", "date" => "2024-10-09", "time" => "16.30-17.40", "team_a" => "Informatika", "team_b" => "Akuntansi", "score_a" => 50, "score_b" => 7, "winner" => "Informatika"],
                    ["stage" => "Semi Final", "date" => "2024-10-10", "time" => "17.20-18.30", "team_a" => "Manajemen", "team_b" => "Filsafat Keilahian", "score_a" => 44, "score_b" => 6, "winner" => "Manajemen"],
                    ["stage" => "Semi Final", "date" => "2024-10-10", "time" => "18.30-19.40", "team_a" => "Sistem Informasi", "team_b" => "Informatika", "score_a" => 29, "score_b" => 56, "winner" => "Informatika"],
                    ["stage" => "Final", "date" => "2024-10-12", "time" => "18.40-20.00", "team_a" => "Manajemen", "team_b" => "Informatika", "score_a" => 70, "score_b" => 38, "winner" => "Manajemen"],
                    ["stage" => "Juara 3", "date" => "2024-10-12", "time" => "17.20-18.40", "team_a" => "Sistem Informasi", "team_b" => "Filsafat Keilahian", "score_a" => 83, "score_b" => 24, "winner" => "Sistem Informasi"],
                ],
            ],
            [
                'sport' => $basket,
                'name' => 'Rector Cup 2024 - Basket Putri',
                'category' => 'Putri',
                'start_date' => '2024-10-08',
                'end_date' => '2024-10-12',
                'matches' => [
                    ['stage' => 'Penyisihan', 'date' => '2024-10-08', 'team_a' => 'Manajemen A', 'team_b' => 'Akuntansi A', 'winner' => 'Manajemen A', 'time' => '16.30-16.50'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-08', 'team_a' => 'SI A', 'team_b' => 'Kedokteran', 'winner' => 'Kedokteran', 'time' => '16.50-17.10'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-10', 'team_a' => 'Despro', 'team_b' => 'Biologi', 'winner' => 'Despro', 'time' => '16.30-16.50'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-10', 'team_a' => 'Teologi', 'team_b' => 'PBI', 'winner' => 'Teologi', 'time' => '16.50-17.10'],
                    ['stage' => 'Semi Final', 'date' => '2024-10-11', 'team_a' => 'Manajemen A', 'team_b' => 'Kedokteran', 'winner' => 'Kedokteran', 'time' => '16.30-16.50'],
                    ['stage' => 'Semi Final', 'date' => '2024-10-11', 'team_a' => 'Despro', 'team_b' => 'Teologi', 'winner' => 'Despro', 'time' => '16.50-17.10'],
                    ['stage' => 'Perebutan Juara 3', 'date' => '2024-10-12', 'team_a' => 'Manajemen A', 'team_b' => 'Teologi', 'winner' => 'Manajemen A', 'time' => '16.30-16.50'],
                    ['stage' => 'Final', 'date' => '2024-10-12', 'team_a' => 'Kedokteran', 'team_b' => 'Despro', 'winner' => 'Kedokteran', 'time' => '16.50-17.10'],
                ],
            ],
            [
                'sport' => $badminton,
                'name' => 'Rector Cup 2024 - Badminton Ganda Putri',
                'category' => 'Ganda Putri',
                'start_date' => '2024-10-16',
                'end_date' => '2024-10-17',
                'matches' => [
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'SI A', 'team_b' => 'ARSI A', 'winner' => 'SI A', 'time' => '17.10-17.50'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'TEOL A', 'team_b' => 'TI A', 'winner' => 'TEOL A', 'time' => '17.10-17.50'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'BIOTEK A', 'team_b' => 'FK A', 'winner' => 'FK A', 'time' => '16.30-17.00'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'SI B', 'team_b' => 'FK B', 'winner' => 'SI B', 'time' => '17.10-17.50'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'TEOL B', 'team_b' => 'AKUN A', 'winner' => 'AKUN A', 'time' => '18.00-18.40'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'FKHUM A', 'team_b' => 'DESPRO A', 'winner' => 'FKHUM A', 'time' => '18.00-18.40'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'FKHUM B', 'team_b' => 'TI B', 'winner' => 'TI B', 'time' => '18.00-18.40'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-16', 'team_a' => 'MENE A', 'team_b' => 'DESPRO B', 'winner' => 'MENE A', 'time' => '17.10-17.50'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-17', 'team_a' => 'SI A', 'team_b' => 'TEOL A', 'winner' => 'SI A', 'time' => '16.30-17.00'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-17', 'team_a' => 'FK A', 'team_b' => 'SI B', 'winner' => 'FK A', 'time' => '16.30-17.00'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-17', 'team_a' => 'AKUN A', 'team_b' => 'FKHUM A', 'winner' => 'FKHUM A', 'time' => '17.10-17.50'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-17', 'team_a' => 'TI B', 'team_b' => 'MENE A', 'winner' => 'MENE A', 'time' => '17.10-17.50'],
                    ['stage' => 'Semi Final', 'date' => '2024-10-17', 'team_a' => 'SI A', 'team_b' => 'FK A', 'winner' => 'FK A', 'time' => '18.00-18.40'],
                    ['stage' => 'Semi Final', 'date' => '2024-10-17', 'team_a' => 'FKHUM A', 'team_b' => 'MENE A', 'winner' => 'MENE A', 'time' => '18.00-18.40'],
                    ['stage' => 'Perebutan Juara 3', 'date' => '2024-10-17', 'team_a' => 'SI A', 'team_b' => 'FKHUM A', 'winner' => 'SI A', 'time' => '18.00-18.40'],
                    ['stage' => 'Final', 'date' => '2024-10-17', 'team_a' => 'FK A', 'team_b' => 'MENE A', 'winner' => 'FK A', 'time' => '18.00-18.40'],
                ],
            ],
            [
                'sport' => $badminton,
                'name' => 'Rector Cup 2024 - Badminton Ganda Putra',
                'category' => 'Ganda Putra',
                'start_date' => '2024-10-14',
                'end_date' => '2024-10-15',
                'matches' => [
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'FK A', 'team_b' => 'BIO A', 'winner' => 'FK A', 'time' => '17.10-19.10'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'ARSI A', 'team_b' => 'FKHUM A', 'winner' => 'ARSI A', 'time' => '17.10-19.10'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'TI A', 'team_b' => 'MENE A', 'winner' => 'MENE A', 'time' => '17.10-19.10'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'TI B', 'team_b' => 'AKUN A', 'winner' => 'TI B', 'time' => '17.10-19.10'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'MENE B', 'team_b' => 'FK B', 'winner' => 'FK B', 'time' => '19.20-20.20'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'SI A', 'team_b' => 'DESPRO A', 'winner' => 'DESPRO A', 'time' => '19.20-20.20'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'TEOL A', 'team_b' => 'SI B', 'winner' => 'SI B', 'time' => '19.20-20.20'],
                    ['stage' => 'Penyisihan', 'date' => '2024-10-14', 'team_a' => 'DESPRO B', 'team_b' => 'TEOL B', 'winner' => 'TEOL B', 'time' => '19.20-20.20'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-15', 'team_a' => 'FK A', 'team_b' => 'ARSI A', 'winner' => 'FK A', 'time' => '16.30-17.00'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-15', 'team_a' => 'MENE A', 'team_b' => 'TI B', 'winner' => 'MENE A', 'time' => '16.30-17.00'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-15', 'team_a' => 'FK B', 'team_b' => 'DESPRO A', 'winner' => 'DESPRO A', 'time' => '18.50-19.20'],
                    ['stage' => 'Perempat Final', 'date' => '2024-10-15', 'team_a' => 'SI B', 'team_b' => 'TEOL B', 'winner' => 'SI B', 'time' => '18.50-19.20'],
                    ['stage' => 'Semi Final', 'date' => '2024-10-15', 'team_a' => 'FK A', 'team_b' => 'MENE A', 'winner' => 'MENE A', 'time' => '18.00-18.40'],
                    ['stage' => 'Semi Final', 'date' => '2024-10-15', 'team_a' => 'DESPRO A', 'team_b' => 'SI B', 'winner' => 'SI B', 'time' => '18.00-18.40'],
                    ['stage' => 'Perebutan Juara 3', 'date' => '2024-10-15', 'team_a' => 'FK A', 'team_b' => 'DESPRO A', 'winner' => 'FK A', 'time' => '16.30-17.00'],
                    ['stage' => 'Final', 'date' => '2024-10-15', 'team_a' => 'MENE A', 'team_b' => 'SI B', 'winner' => 'MENE A', 'time' => '18.00-18.40'],
                ],
            ],
        ];

        foreach ($datasets as $dataset) {
            $tournament = $this->upsertTournament([
                'name'       => $dataset['name'],
                'sport_id'   => $dataset['sport']->id,
                'year'       => 2024,
                'type'       => 'single_elimination',
                'is_active'  => false,
                'start_date' => $dataset['start_date'] ?? '2024-10-07',
                'end_date'   => $dataset['end_date'] ?? '2024-10-17',
            ]);

            $this->seedMatches($tournament, $dataset['sport'], $dataset['matches'], $dataset['category']);
        }
    }

    private function seedFutsal(): void
    {
        $sport = $this->sport('Futsal');
        $tournament = $this->upsertTournament([
            'name'               => 'Rector Cup Futsal 2024',
            'sport_id'           => $sport->id,
            'year'               => 2024,
            'type'               => 'group_stage',
            'external_score_url' => 'https://docs.google.com/spreadsheets/d/1-SaHP6g9e9cJ5MmuH6c-FSX0-zTGpW5mqv2G-K-GUY0/edit?gid=1229337401#gid=1229337401',
            'is_active'          => false,
            'start_date'         => '2024-11-02',
            'end_date'           => '2024-11-03',
        ]);

        $independentMatches = [
            ["date" => "2024-11-02", "time" => "08.10", "team_a" => "Akuntansi", "team_b" => "Manajemen", "stage" => "Group A"],
            ["date" => "2024-11-02", "time" => "08.40", "team_a" => "Kedokteran", "team_b" => "Informatika", "stage" => "Group B"],
            ["date" => "2024-11-02", "time" => "09.10", "team_a" => "Manajemen", "team_b" => "Arsitektur", "stage" => "Group C"],
            ["date" => "2024-11-02", "time" => "09.40", "team_a" => "Desain Produk", "team_b" => "Sistem Informasi", "stage" => "Group D"],
        ];

        $bracketDay2Matches = [
            ["date" => "2024-11-03", "time" => "TBA", "round" => "8 Besar", "match" => 1, "team_a" => "Manajemen", "team_b" => "Manajemen"],
            ["date" => "2024-11-03", "time" => "TBA", "round" => "8 Besar", "match" => 2, "team_a" => "Informatika", "team_b" => "Sistem Informasi"],
            ["date" => "2024-11-03", "time" => "TBA", "round" => "8 Besar", "match" => 3, "team_a" => "Akuntansi", "team_b" => "Arsitektur"],
            ["date" => "2024-11-03", "time" => "TBA", "round" => "8 Besar", "match" => 4, "team_a" => "Sistem Informasi", "team_b" => "Studi Humanitas"],
        ];

        $this->seedMatches($tournament, $sport, $independentMatches, 'Penyisihan Grup', 'Lapangan Futsal', false);
        $this->seedMatches($tournament, $sport, $bracketDay2Matches, 'Babak Gugur', 'Lapangan Futsal', false);
    }

    private function seedPubgm(): void
    {
        $sport = $this->sport('PUBG MOBILE');
        $tournament = $this->upsertTournament([
            'name' => 'Rector Cup PUBGM 2024',
            'sport_id' => $sport->id,
            'year' => 2024,
            'type' => 'group_stage',
            'external_score_url' => 'https://docs.google.com/spreadsheets/d/1-SaHP6g9e9cJ5MmuH6c-FSX0-zTGpW5mqv2G-K-GUY0/edit?gid=1229337401#gid=1229337401',
            'is_active' => false,
            'start_date' => '2024-09-30',
            'end_date' => '2024-09-30',
        ]);

        $participants = [
            'Informatika', 'Sistem Informasi', 'Manajemen', 'Akuntansi',
            'Kedokteran', 'Biologi', 'Arsitektur', 'Desain Produk', 'Filsafat Keilahian', 'Studi Humanitas',
        ];

        $teamIds = [];
        foreach ($participants as $name) {
            $teamIds[] = $this->team($name)->id;
        }
        $tournament->teams()->syncWithoutDetaching(array_values(array_unique($teamIds)));
    }

    private function seedBilliard(): void
    {
        $sport = $this->sport('Billiard');
        $tournament = $this->upsertTournament([
            'name' => 'Rector Cup Billiard 2024',
            'sport_id' => $sport->id,
            'year' => 2024,
            'type' => 'single_elimination',
            'is_active' => false,
            'start_date' => '2024-10-20',
            'end_date' => '2024-10-20',
        ]);

        $matches = [
            ["stage" => "Penyisihan", "date" => "2024-10-20", "time" => "14.30", "team_a" => "Kedokteran", "team_b" => "Studi Humanitas", "score_a" => 3, "score_b" => 0, "winner" => "Kedokteran", "location" => "Mille Billiard Jogja"],
            ["stage" => "Penyisihan", "date" => "2024-10-20", "time" => "14.30", "team_a" => "Studi Humanitas", "team_b" => "Manajemen", "score_a" => 0, "score_b" => 3, "winner" => "Manajemen", "location" => "Mille Billiard Jogja"],
            ["stage" => "Penyisihan", "date" => "2024-10-20", "time" => "14.30", "team_a" => "Filsafat Keilahian", "team_b" => "Manajemen", "score_a" => 3, "score_b" => 1, "winner" => "Filsafat Keilahian", "location" => "Mille Billiard Jogja"],
            ["stage" => "Penyisihan", "date" => "2024-10-20", "time" => "14.30", "team_a" => "Akuntansi", "team_b" => "Desain Produk", "score_a" => 3, "score_b" => 0, "winner" => "Akuntansi", "location" => "Mille Billiard Jogja"],
            ["stage" => "Penyisihan", "date" => "2024-10-20", "time" => "14.30", "team_a" => "Informatika", "team_b" => "Sistem Informasi", "score_a" => 0, "score_b" => 3, "winner" => "Sistem Informasi", "location" => "Mille Billiard Jogja"],
            ["stage" => "Penyisihan", "date" => "2024-10-20", "time" => "14.30", "team_a" => "Informatika", "team_b" => "Biologi", "score_a" => 2, "score_b" => 3, "winner" => "Biologi", "location" => "Mille Billiard Jogja"],
        ];

        $this->seedMatches($tournament, $sport, $matches, null, 'Mille Billiard Jogja', false);
    }

    // ──────────────────────────────────────────────
    //  MAIN RUN
    // ──────────────────────────────────────────────
    public function run(): void
    {
        $this->command->info('Seeding Rector Cup 2024 Complete Historical Data...');

        $this->seedBadmintonAndBasket();
        $this->command->info('  ✓ Badminton + Basket');

        $this->seedFutsal();
        $this->command->info('  ✓ Futsal (Group Stage + Bracket)');

        $this->seedPubgm();
        $this->command->info('  ✓ PUBG Mobile (All-in-one)');

        $this->seedBilliard();
        $this->command->info('  ✓ Billiard');

        $this->command->info('Seeding 2024 Finished!');
    }
}