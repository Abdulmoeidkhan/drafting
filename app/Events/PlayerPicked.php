<?php

namespace App\Events;

use App\Models\DraftPick;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerPicked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public function __construct(
        public DraftPick $draftPick,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('draft.' . $this->draftPick->draftRound->league_type . '.' . $this->draftPick->draftRound->category_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'player.picked';
    }

    public function broadcastWith(): array
    {
        return [
            'draft_pick_id' => $this->draftPick->id,
            'draft_round_id' => $this->draftPick->round_id,
            'team_id' => $this->draftPick->team_id,
            'team_name' => $this->draftPick->team->name,
            'participant_id' => $this->draftPick->participant_id,
            'participant_name' => $this->draftPick->participant->first_name . ' ' . $this->draftPick->participant->last_name,
            'pick_number' => $this->draftPick->pick_number,
            'picked_at' => $this->draftPick->picked_at,
            'league_type' => $this->draftPick->draftRound->league_type,
            'category_id' => $this->draftPick->draftRound->category_id,
        ];
    }
}
