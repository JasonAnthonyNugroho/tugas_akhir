<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['name', 'type', 'is_active', 'sport_id', 'year', 'start_date', 'end_date', 'external_score_url'];

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
