<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat/Re-create akun admin
     */
    public function run(): void
    {
        $this->command->info('Checking admin user...');
        
        // Cek apakah admin sudah ada
        $existingAdmin = User::where('username', 'admin')->first();
        
        if ($existingAdmin) {
            // Update password admin yang ada
            $existingAdmin->update([
                'password' => bcrypt('admin#1234'),
            ]);
            $this->command->info('✅ Password admin diperbarui: admin / admin#1234');
        } else {
            // Buat admin baru
            User::create([
                'name' => 'admin',
                'username' => 'admin',
                'email' => 'admin@ukdw.ac.id',
                'password' => bcrypt('admin#1234'),
            ]);
            $this->command->info('✅ Admin user dibuat: admin / admin#1234');
        }
        
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Username: admin');
        $this->command->info('Password: admin#1234');
    }
}
