<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearAllMatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menghapus semua data pertandingan (live & history)
     * ⚠️ AMAN: Tidak menghapus data Users/Admin, Teams, atau Sports
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('MENGHAPUS SEMUA DATA PERTANDINGAN');
        $this->command->info('========================================');
        $this->command->info('');
        
        // Cek data yang ada
        $gamesCount = DB::table('games')->count();
        $matchesCount = DB::table('pertandingans')->count();
        $tournamentCount = Schema::hasTable('tournaments') ? DB::table('tournaments')->count() : 0;
        
        $this->command->info("Data yang akan dihapus:");
        $this->command->info("- {$gamesCount} games");
        $this->command->info("- {$matchesCount} pertandingan");
        $this->command->info("- {$tournamentCount} tournament");
        $this->command->info('');
        
        // Info: Data yang TIDAK dihapus (AMAN)
        $this->command->info("✅ DATA YANG AMAN (tidak dihapus):");
        $this->command->info("- Users/Admin (termasuk admin/admin#1234)");
        $this->command->info("- Teams (Prodi)");
        $this->command->info("- Sports (Cabang Olahraga)");
        $this->command->info('');
        
        // Konfirmasi
        if ($matchesCount === 0 && $gamesCount === 0) {
            $this->command->warn('Tidak ada data pertandingan untuk dihapus.');
            return;
        }
        
        // Hapus data menggunakan delete() untuk menghindari masalah foreign key
        // Hapus games dulu (child table)
        if ($gamesCount > 0) {
            DB::table('games')->delete();
            $this->command->info("✓ {$gamesCount} data games dihapus");
        }
        
        // Hapus pertandingans
        if ($matchesCount > 0) {
            DB::table('pertandingans')->delete();
            $this->command->info("✓ {$matchesCount} data pertandingan dihapus");
        }
        
        // Hapus tournaments jika ada
        if ($tournamentCount > 0) {
            DB::table('tournaments')->delete();
            $this->command->info("✓ {$tournamentCount} data tournament dihapus");
        }
        
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('✅ BERHASIL! Semua data pertandingan dihapus.');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Dashboard sekarang kosong. Silakan tambahkan pertandingan baru.');
    }
}
