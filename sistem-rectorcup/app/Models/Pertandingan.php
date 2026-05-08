<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pertandingan extends Model
{
    protected $fillable = [
        'sport_id',
        'team_a_id',
        'team_b_id',
        'score_a',
        'score_b',
        'waktu_tanding',
        'lokasi',
        'keterangan',
        'status',
        'selesai_pada',
        'babak',
        'format_tanding',
        'screenshot',
        'tournament_id',
        'round',
        'match_number',
        'next_match_id',
        'winner_id',
        'match_date',
    ];

    protected $casts = [
        'waktu_tanding' => 'datetime',
        'selesai_pada' => 'datetime',
        'match_date' => 'datetime',
    ];

    /**
     * Update status scheduled ke live secara otomatis jika waktu sudah terlewati.
     */
    public static function autoUpdateLiveStatus()
    {
        return self::where('status', 'scheduled')
            ->where('waktu_tanding', '<=', now())
            ->update(['status' => 'live']);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function teamA(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(Pertandingan::class, 'next_match_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function games()
    {
        return $this->hasMany(MatchGame::class);
    }
}
