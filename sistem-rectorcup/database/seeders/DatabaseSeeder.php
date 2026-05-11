<?php

namespace Database\Seeders;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun panitia inti (tidak di-overwrite jika sudah ada)
        User::firstOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'admin',
            'email' => 'admin@ukdw.ac.id',
            'password' => bcrypt('admin#1234'),
        ]);

        // Data Cabang Olahraga (icon: Bootstrap Icons 1.11.3 yang valid)
        $sports = [
            ['nama_sport' => 'PES',            'icon' => 'bi-controller'],
            ['nama_sport' => 'PUBG MOBILE',    'icon' => 'bi-phone-fill'],
            ['nama_sport' => 'Mobile Legends', 'icon' => 'bi-phone-vibrate-fill'],
            ['nama_sport' => 'Basket',         'icon' => 'bi-dribbble'],
            ['nama_sport' => 'Badminton',      'icon' => 'bi-feather'],
            ['nama_sport' => 'Billiard',       'icon' => 'bi-8-circle-fill'],
            ['nama_sport' => 'Volleyball',     'icon' => 'bi-circle-half'],
            ['nama_sport' => 'Futsal',         'icon' => 'bi-bullseye'],
            ['nama_sport' => 'Vocal Group',    'icon' => 'bi-mic-fill'],
            ['nama_sport' => 'Catur',          'icon' => 'bi-grid-3x3-gap-fill'],
        ];

        foreach ($sports as $sport) {
            // updateOrCreate biar icon yang sebelumnya invalid ikut diperbarui.
            Sport::updateOrCreate(
                ['nama_sport' => $sport['nama_sport']],
                ['icon' => $sport['icon']]
            );
        }

        // Data Program Studi Sarjana UKDW (sumber: list resmi biaya kuliah)
        // Setiap prodi mendapat 2 slot tim: A & B
        $prodis = [
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

        // Tim Khusus Battle Royale / cabang yang melibatkan semua prodi (mis. PUBG Mobile, Catur)
        Team::firstOrCreate([
            'name' => 'Seluruh Prodi',
        ], [
            'prodi' => 'Semua Prodi',
        ]);

        // Generate 2 tim (A & B) per prodi
        foreach ($prodis as $prodi) {
            Team::firstOrCreate(
                ['name' => $prodi . ' A'],
                ['prodi' => $prodi]
            );
            Team::firstOrCreate(
                ['name' => $prodi . ' B'],
                ['prodi' => $prodi]
            );
        }

        // Cleanup: hapus tim dari prodi lama yang tidak ada di list baru,
        // HANYA jika tim tersebut belum dipakai di pertandingan/turnamen manapun.
        $validProdis = array_merge($prodis, ['Semua Prodi']);
        $orphanTeams = Team::whereNotIn('prodi', $validProdis)->get();
        foreach ($orphanTeams as $team) {
            $usedInMatch = \App\Models\Pertandingan::where('team_a_id', $team->id)
                ->orWhere('team_b_id', $team->id)
                ->exists();
            $usedInTournament = \DB::table('tournament_teams')->where('team_id', $team->id)->exists();

            if (!$usedInMatch && !$usedInTournament) {
                $team->delete();
            } else {
                $this->command->warn("Tim '{$team->name}' (prodi: {$team->prodi}) tidak dihapus karena masih dipakai di pertandingan/turnamen.");
            }
        }

        // Recap data turnamen historis
        $this->call([
            Mlbb2024Seeder::class,
            RectorCup2024Seeder::class,
        ]);
    }
}
