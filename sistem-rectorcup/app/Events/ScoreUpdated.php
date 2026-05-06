<?php

namespace App\Events;

use App\Models\Pertandingan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pertandingan;

    public function __construct(Pertandingan $pertandingan)
    {
        $this->pertandingan = $pertandingan;
        
        \Log::info('ScoreUpdated event created', [
            'match_id' => $pertandingan->id,
            'score_a' => $pertandingan->score_a,
            'score_b' => $pertandingan->score_b,
            'status' => $pertandingan->status,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('scores'),
        ];
    }

    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->pertandingan->id,
            'score_a' => $this->pertandingan->score_a,
            'score_b' => $this->pertandingan->score_b,
            'status' => $this->pertandingan->status,
        ];
        
        \Log::info('ScoreUpdated broadcasting with data', $data);
        
        return $data;
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }
}
