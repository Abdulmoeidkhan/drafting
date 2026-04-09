<?php

namespace App\Events;

use App\Models\DraftRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DraftTurnChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public ?string $connection;

    public string $queue;

    public int $roundId;

    public string $leagueType;

    public ?int $currentTeamId;

    public ?string $currentTeamName;

    public int $currentPickNumber;

    public int $totalPicksPlanned;

    public string $status;

    public ?string $message;

    public ?string $turnStartedAt;

    public int $turnTimeSeconds;

    public function __construct(DraftRound $round, ?string $message = null)
    {
        $round->loadMissing('currentTeam');

        $this->connection = config('broadcasting.queue_connection');
        $this->queue = (string) config('broadcasting.queue', 'broadcasts');

        $this->roundId = (int) $round->id;
        $this->leagueType = (string) $round->league_type;
        $this->currentTeamId = $round->current_team_id ? (int) $round->current_team_id : null;
        $this->currentTeamName = $round->currentTeam?->name;
        $this->currentPickNumber = (int) $round->current_pick_number;
        $this->totalPicksPlanned = (int) $round->total_picks_planned;
        $this->status = (string) $round->status;
        $this->message = $message;
        $this->turnStartedAt = $round->current_turn_started_at?->toIso8601String();
        $this->turnTimeSeconds = (int) $round->turn_time_seconds;
    }

    public function broadcastOn(): array
    {
        return [new Channel('draft.league.'.$this->leagueType)];
    }

    public function broadcastAs(): string
    {
        return 'draft.turn.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'roundId' => $this->roundId,
            'leagueType' => $this->leagueType,
            'currentTeamId' => $this->currentTeamId,
            'currentTeamName' => $this->currentTeamName,
            'currentPickNumber' => $this->currentPickNumber,
            'totalPicksPlanned' => $this->totalPicksPlanned,
            'status' => $this->status,
            'message' => $this->message,
            'turnStartedAt' => $this->turnStartedAt,
            'turnTimeSeconds' => $this->turnTimeSeconds,
        ];
    }
}
