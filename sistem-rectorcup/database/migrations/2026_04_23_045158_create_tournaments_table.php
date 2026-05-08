<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Rector Cup Futsal 2026"
            $table->foreignId('sport_id')->nullable()->constrained('sports')->onDelete('cascade');
            $table->enum('type', ['single_elimination', 'group_stage'])->default('single_elimination');
            $table->integer('year')->default(date('Y'));
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('external_score_url', 500)->nullable(); // Google Sheet skor manual (mis. PUBG, Catur)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot: tim peserta tiap tournament
        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_teams');
        Schema::dropIfExists('tournaments');
    }
};
