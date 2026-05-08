<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pertandingans', function (Blueprint $table) {
            $table->id();

            // Relasi utama
            $table->foreignId('sport_id')->constrained('sports')->onDelete('cascade');
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->onDelete('set null');
            $table->foreignId('team_a_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('team_b_id')->nullable()->constrained('teams')->onDelete('cascade');

            // Skor & status
            $table->integer('score_a')->default(0);
            $table->integer('score_b')->default(0);
            $table->enum('status', ['scheduled', 'live', 'finished'])->default('scheduled');
            $table->string('screenshot')->nullable();

            // Waktu & lokasi
            $table->dateTime('waktu_tanding');
            $table->dateTime('match_date')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->string('lokasi');

            // Info pertandingan
            $table->string('babak')->nullable();              // Contoh: Quarter Final, Final
            $table->string('format_tanding')->default('Knockout'); // BO1/BO3/BO5
            $table->string('keterangan')->nullable();

            // Bracket support
            $table->integer('round')->nullable();             // 1, 2, 3 (Final)
            $table->integer('match_number')->nullable();      // Urutan dalam babak
            $table->foreignId('next_match_id')->nullable()->constrained('pertandingans')->onDelete('set null');
            $table->foreignId('winner_id')->nullable()->constrained('teams')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertandingans');
    }
};
