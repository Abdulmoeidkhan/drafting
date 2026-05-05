<div
    wire:poll.1s="pollActiveRound"
    x-data="{
        broadcastActive: false,
        echoSubscribed: false,
        channelName: 'draft.league.{{ $leagueType }}',
        subscribe() {
            if (this.echoSubscribed || typeof window.Echo === 'undefined') return;
            try {
                const pusher = window.Echo.connector?.pusher;
                if (pusher) {
                    if (pusher.connection.state === 'connected') {
                        this.broadcastActive = true;
                    } else {
                        pusher.connection.bind('connected', () => {
                            this.broadcastActive = true;
                        });
                    }
                }
                window.Echo.channel(this.channelName)
                    .listen('.draft.turn.changed', () => {
                        this.broadcastActive = true;
                        $wire.$refresh();
                    });
                this.echoSubscribed = true;
            } catch (e) {
                this.echoSubscribed = false;
            }
        }
    }"
    x-init="subscribe(); setInterval(() => { if (!echoSubscribed) subscribe(); }, 5000)"
>
    @if($errors->has('pick'))
        <div class="alert alert-danger py-2 mb-3">{{ $errors->first('pick') }}</div>
    @endif

    <h5 class="mb-3">Active Draft Round</h5>

    @if($activeRound)
        <p class="mb-1"><strong>Category:</strong> {{ $activeRound->category?->name ?: 'N/A' }}</p>
        <p class="mb-1"><strong>Current Team:</strong> {{ $activeRound->currentTeam?->name ?: 'N/A' }}</p>
        <p class="mb-1"><strong>Your Picks This Round:</strong> {{ $teamRoundPicksCount }} / {{ $activeRound->picks_per_team }}</p>
        <p class="mb-3">
            <strong>Time Remaining:</strong>
            <span>{{ sprintf('%02d:%02d / %02d:%02d', intdiv($activeRoundRemainingSeconds, 60), $activeRoundRemainingSeconds % 60, intdiv((int) $activeRound->turn_time_seconds, 60), ((int) $activeRound->turn_time_seconds) % 60) }}</span>
        </p>

        @if($isTeamTurn)
            <div class="alert alert-info py-2 mb-3">It is your turn to pick.</div>
        @else
            <div class="alert alert-secondary py-2 mb-3">Waiting for current team to finish their pick.</div>
        @endif

        @if($draftPoolParticipants->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Player</th>
                            <th>Email</th>
                            <th>Skill Category</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($draftPoolParticipants as $player)
                            <tr>
                                <td>
                                    @if($player->passport_picture)
                                        <img
                                            src="{{ asset('storage/' . ltrim($player->passport_picture, '/')) }}"
                                            alt="{{ $player->full_name }}"
                                            class="rounded"
                                            style="width: 48px; height: 48px; object-fit: cover;"
                                        >
                                    @else
                                        <span class="badge text-bg-secondary">No Photo</span>
                                    @endif
                                </td>
                                <td>{{ $player->full_name }}</td>
                                <td>{{ $player->email }}</td>
                                <td>
                                    @php $playerSkills = collect($player->skill_categories ?? [])->filter()->values()->all(); @endphp
                                    <div><strong>{{ $player->category?->name ?: 'Uncategorized' }}</strong></div>
                                    @if(count($playerSkills))
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($playerSkills as $skill)
                                                <span class="badge text-bg-light border">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-muted">No specific skills</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($canPick)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary"
                                            wire:click="pickPlayer({{ $player->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="pickPlayer({{ $player->id }})"
                                        >
                                            <span wire:loading.remove wire:target="pickPlayer({{ $player->id }})">Pick Player</span>
                                            <span wire:loading wire:target="pickPlayer({{ $player->id }})">Picking...</span>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Pick Player</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No eligible players are currently available in this active round.</p>
        @endif
    @else
        <p class="text-muted mb-0">No active draft round right now.</p>
    @endif

    @if($draftActivity->count() > 0)
        <hr class="my-4">
        <h5 class="mb-3">Recent Draft Activity</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Round</th>
                        <th>Pick #</th>
                        <th>Team</th>
                        <th>Player</th>
                        <th>Skill Category</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($draftActivity as $activity)
                        <tr>
                            <td>{{ optional($activity->picked_at)->format('M d, Y H:i') ?: '-' }}</td>
                            <td>#{{ $activity->draft_round_id }}</td>
                            <td>{{ $activity->pick_number }}</td>
                            <td>{{ $activity->team?->name ?: 'N/A' }}</td>
                            <td>{{ $activity->participant?->full_name ?: 'N/A' }}</td>
                            <td>
                                @php
                                    $activityMainCategory = $activity->round?->category?->name ?: ($activity->participant?->category?->name ?: 'Uncategorized');
                                    $activitySkills = collect($activity->participant?->skill_categories ?? [])->filter()->values()->all();
                                @endphp
                                <div><strong>{{ $activityMainCategory }}</strong></div>
                                @if(count($activitySkills))
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach($activitySkills as $skill)
                                            <span class="badge text-bg-light border">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted">No specific skills</small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
