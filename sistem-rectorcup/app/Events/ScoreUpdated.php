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
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('scores'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'     => $this->pertandingan->id,
            'score_a'=> $this->pertandingan->score_a,
            'score_b'=> $this->pertandingan->score_b,
            'status' => $this->pertandingan->status,
        ];
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }
}
