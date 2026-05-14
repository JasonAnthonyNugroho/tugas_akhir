<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['name', 'type', 'is_active', 'sport_id', 'year', 'start_date', 'end_date', 'external_score_url'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Boot method untuk handle cascade delete
     */
    protected static function boot()
    {
        parent::boot();

        // Ketika tournament dihapus, hapus juga semua pertandingan terkait
        static::deleting(function ($tournament) {
            $tournament->pertandingans()->delete();
            $tournament->teams()->detach(); // Detach pivot table relationships
        });
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'tournament_teams');
    }

    public function pertandingans()
    {
        return $this->hasMany(Pertandingan::class);
    }
}
