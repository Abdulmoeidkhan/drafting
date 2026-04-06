<?php

namespace App\Events;

use App\Models\DraftRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public DraftRound $draftRound,
        public string $previousTeamId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('draft.' . $this->draftRound->league_type . '.' . $this->draftRound->category_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'turn.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'draft_round_id' => $this->draftRound->id,
            'current_team_id' => $this->draftRound->current_team_id,
            'previous_team_id' => $this->previousTeamId,
            'current_turn_started_at' => $this->draftRound->current_turn_started_at,
            'turn_time_seconds' => $this->draftRound->turn_time_seconds,
            'league_type' => $this->draftRound->league_type,
            'category_id' => $this->draftRound->category_id,
            'status' => $this->draftRound->status,
        ];
    }
}
