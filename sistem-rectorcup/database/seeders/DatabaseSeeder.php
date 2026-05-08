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

        // Data Program Studi Sarjana UKDW (Dukungan Multi-Tim: A & B)
        $prodis = [
            'Informatika',
            'Sistem Informasi',
            'Arsitektur',
            'Desain Produk',
            'Manajemen',
            'Akuntansi',
            'Biologi',
            'Kedokteran',
            'Teologi',
            'Pendidikan Bahasa Inggris'
        ];

        // Tambahkan Tim Khusus Battle Royale
        Team::firstOrCreate([
            'name' => 'Seluruh Prodi',
            'prodi' => 'Semua Prodi'
        ]);

        foreach ($prodis as $prodi) {
            // Membuat Tim A
            Team::firstOrCreate([
                'name' => $prodi . ' A',
                'prodi' => $prodi
            ]);

            // Membuat Tim B
            Team::firstOrCreate([
                'name' => $prodi . ' B',
                'prodi' => $prodi
            ]);
        }

        // Recap data turnamen historis
        $this->call([
            Mlbb2024Seeder::class,
        ]);
    }
}
