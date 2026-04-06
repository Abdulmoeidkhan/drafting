<?php

namespace App\Events;

use App\Models\DraftRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoundCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public DraftRound $draftRound,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('draft.' . $this->draftRound->league_type . '.' . $this->draftRound->category_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'round.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'draft_round_id' => $this->draftRound->id,
            'league_type' => $this->draftRound->league_type,
            'category_id' => $this->draftRound->category_id,
            'status' => $this->draftRound->status,
            'completed_at' => $this->draftRound->updated_at,
        ];
    }
}
