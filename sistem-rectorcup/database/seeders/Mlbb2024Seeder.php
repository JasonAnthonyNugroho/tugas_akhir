<?php

namespace Database\Seeders;

use App\Models\Pertandingan;
use App\Models\Sport;
use App\Models\Team;
use App\Models\Tournament;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Mlbb2024Seeder extends Seeder
{
    /**
     * Recap Mobile Legends Rector Cup 2024.
     * - Mapping nama tim dari format JSON ke nama tim di DB.
     * - Gabungkan date + time ("HH.MM-HH.MM") jadi waktu_tanding & selesai_pada.
     * - Status diset finished, winner_id terisi sesuai pemenang.
     */
    public function run(): void
    {
        $sport = Sport::firstOrCreate(
            ['nama_sport' => 'Mobile Legends'],
            ['icon' => 'bi-controller']
        );

        $tournament = Tournament::firstOrCreate(
            ['name' => 'Rector Cup 2024', 'sport_id' => $sport->id],
            [
                'type'       => 'single_elimination',
                'year'       => 2024,
                'start_date' => '2024-10-04',
                'end_date'   => '2024-10-06',
                'is_active'  => false,
            ]
        );

        // Mapping nama JSON → nama tim di DB.
        $nameMap = [
            'FKHUM A'       => ['name' => 'Hukum dan Humaniora A', 'prodi' => 'Hukum dan Humaniora'],
            'FKHUM B'       => ['name' => 'Hukum dan Humaniora B', 'prodi' => 'Hukum dan Humaniora'],
            'Teologi A'     => ['name' => 'Teologi A',             'prodi' => 'Teologi'],
            'Teologi B'     => ['name' => 'Teologi B',             'prodi' => 'Teologi'],
            'Biotek A'      => ['name' => 'Bioteknologi A',        'prodi' => 'Bioteknologi'],
            'Biotek B'      => ['name' => 'Bioteknologi B',        'prodi' => 'Bioteknologi'],
            'Manajemen A'   => ['name' => 'Manajemen A',           'prodi' => 'Manajemen'],
            'Manajemen B'   => ['name' => 'Manajemen B',           'prodi' => 'Manajemen'],
            'Despro A'      => ['name' => 'Desain Produk A',       'prodi' => 'Desain Produk'],
            'Despro B'      => ['name' => 'Desain Produk B',       'prodi' => 'Desain Produk'],
            'Akuntansi A'   => ['name' => 'Akuntansi A',           'prodi' => 'Akuntansi'],
            'Informatika A' => ['name' => 'Informatika A',         'prodi' => 'Informatika'],
            'Informatika B' => ['name' => 'Informatika B',         'prodi' => 'Informatika'],
            'SI A'          => ['name' => 'Sistem Informasi A',    'prodi' => 'Sistem Informasi'],
            'SI B'          => ['name' => 'Sistem Informasi B',    'prodi' => 'Sistem Informasi'],
            'Kedokteran A'  => ['name' => 'Kedokteran A',          'prodi' => 'Kedokteran'],
        ];

        // Resolve setiap label JSON ke instance Team (firstOrCreate).
        $teamMap = [];
        foreach ($nameMap as $label => $info) {
            $teamMap[$label] = Team::firstOrCreate(
                ['name' => $info['name']],
                ['prodi' => $info['prodi']]
            );
        }

        // Attach semua tim ke tournament (idempotent).
        $tournament->teams()->syncWithoutDetaching(
            collect($teamMap)->pluck('id')->all()
        );

        // Mapping stage → round.
        $roundMap = [
            'Penyisihan'        => 1,
            'Perempat Final'    => 2,
            'Semi Final'        => 3,
            'Perebutan Juara 3' => 4,
            'Final'             => 4,
        ];

        // Data hasil pertandingan MLBB Rector Cup 2024.
        $matches = [
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'FKHUM B',       'team_b' => 'Teologi A',     'winner' => 'Teologi A',     'time' => '16.10-16.50'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Biotek B',      'team_b' => 'Manajemen B',   'winner' => 'Manajemen B',   'time' => '16.50-17.10'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Despro B',      'team_b' => 'Akuntansi A',   'winner' => 'Despro B',      'time' => '17.10-17.40'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Informatika A', 'team_b' => 'SI B',          'winner' => 'Informatika A', 'time' => '17.40-18.20'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Despro A',      'team_b' => 'Biotek A',      'winner' => 'Biotek A',      'time' => '18.20-18.40'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Informatika B', 'team_b' => 'FKHUM A',       'winner' => 'Informatika B', 'time' => '18.40-19.10'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Teologi B',     'team_b' => 'Manajemen A',   'winner' => 'Manajemen A',   'time' => '19.10-19.40'],
            ['stage' => 'Penyisihan',     'date' => '2024-10-04', 'team_a' => 'Kedokteran A',  'team_b' => 'SI A',          'winner' => 'Kedokteran A',  'time' => '19.40-20.10'],

            ['stage' => 'Perempat Final', 'date' => '2024-10-05', 'team_a' => 'Teologi A',     'team_b' => 'Manajemen B',   'winner' => 'Manajemen B',   'time' => '17.10-17.40'],
            ['stage' => 'Perempat Final', 'date' => '2024-10-05', 'team_a' => 'Despro B',      'team_b' => 'Informatika A', 'winner' => 'Informatika A', 'time' => '17.40-18.10'],
            ['stage' => 'Perempat Final', 'date' => '2024-10-05', 'team_a' => 'Biotek A',      'team_b' => 'Informatika B', 'winner' => 'Informatika B', 'time' => '18.10-18.40'],
            ['stage' => 'Perempat Final', 'date' => '2024-10-05', 'team_a' => 'Manajemen A',   'team_b' => 'Kedokteran A',  'winner' => 'Kedokteran A',  'time' => '18.40-19.10'],

            ['stage' => 'Semi Final',     'date' => '2024-10-05', 'team_a' => 'Manajemen B',   'team_b' => 'Informatika A', 'winner' => 'Informatika A', 'time' => '19.10-20.40'],
            ['stage' => 'Semi Final',     'date' => '2024-10-05', 'team_a' => 'Informatika B', 'team_b' => 'Kedokteran A',  'winner' => 'Kedokteran A',  'time' => '20.40-22.10'],

            ['stage' => 'Perebutan Juara 3', 'date' => '2024-10-06', 'team_a' => 'Manajemen B', 'team_b' => 'Informatika B', 'winner' => 'Manajemen B', 'time' => '16.40-18.10'],
            ['stage' => 'Final',          'date' => '2024-10-06', 'team_a' => 'Informatika A', 'team_b' => 'Kedokteran A',  'winner' => 'Informatika A', 'time' => '18.20-19.50'],
        ];

        // Group per stage untuk numbering match_number.
        $stageCounters = [];

        DB::transaction(function () use ($matches, $roundMap, $teamMap, $tournament, $sport, &$stageCounters) {
            foreach ($matches as $m) {
                $teamA  = $teamMap[$m['team_a']];
                $teamB  = $teamMap[$m['team_b']];
                $winner = $teamMap[$m['winner']];

                // Parse waktu "16.10-16.50" → start "16:10", end "16:50".
                [$startRaw, $endRaw] = explode('-', $m['time']);
                $start = str_replace('.', ':', trim($startRaw));
                $end   = str_replace('.', ':', trim($endRaw));

                $waktuTanding = Carbon::parse($m['date'] . ' ' . $start);
                $selesaiPada  = Carbon::parse($m['date'] . ' ' . $end);

                // Score 1-0 untuk winner.
                $scoreA = $winner->id === $teamA->id ? 1 : 0;
                $scoreB = $winner->id === $teamB->id ? 1 : 0;

                $stage = $m['stage'];
                $isThirdPlace = $stage === 'Perebutan Juara 3';

                if ($isThirdPlace) {
                    $matchNumber = 99; // konvensi: 99 = third-place playoff (di luar bracket utama)
                } else {
                    $stageCounters[$stage] = ($stageCounters[$stage] ?? 0) + 1;
                    $matchNumber = $stageCounters[$stage];
                }

                Pertandingan::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'team_a_id'     => $teamA->id,
                        'team_b_id'     => $teamB->id,
                        'waktu_tanding' => $waktuTanding,
                    ],
                    [
                        'sport_id'       => $sport->id,
                        'score_a'        => $scoreA,
                        'score_b'        => $scoreB,
                        'lokasi'         => 'UKDW',
                        'status'         => 'finished',
                        'selesai_pada'   => $selesaiPada,
                        'match_date'     => $waktuTanding,
                        'babak'          => $stage,
                        'format_tanding' => 'BO3',
                        'round'          => $roundMap[$stage] ?? null,
                        'match_number'   => $matchNumber,
                        'winner_id'      => $winner->id,
                    ]
                );
            }
        });

        $this->command?->info('  ✓ Seeder MLBB Rector Cup 2024 selesai (' . count($matches) . ' pertandingan).');
    }
}
