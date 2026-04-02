<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Team Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
    @vite(['resources/js/app.js'])
</head>
<body class="bg-light">
@include('partials.portal-navbar')

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-2">{{ $team->name }}</h4>
            <p class="mb-1"><strong>Email:</strong> {{ $team->email }}</p>
            <p class="mb-1"><strong>League:</strong> {{ $teamLeagueLabel ?? ucfirst((string) ($teamLeagueType ?? $team->league_type ?? 'male')) }}</p>
            <p class="mb-1"><strong>Captain:</strong> {{ $team->captain_name ?: 'Not set' }}</p>
            <p class="mb-0"><strong>Roster:</strong> {{ $participants->count() }} / {{ $team->max_players }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Active Draft Round</h5>

            @if($activeRound)
                <p class="mb-1"><strong>Category:</strong> {{ $activeRound->category?->name ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Current Team:</strong> {{ $activeRound->currentTeam?->name ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Your Picks This Round:</strong> {{ $teamRoundPicksCount }} / {{ $activeRound->picks_per_team }}</p>
                <p class="mb-3">
                    <strong>Time Remaining:</strong>
                    <span id="teamTurnTimer"
                          data-seconds="{{ $remainingTurnSeconds }}"
                          data-tick-url="{{ $activeRound ? route('team.draft.round.tick', $activeRound->id) : '' }}">
                        {{ $remainingTurnSeconds }}s
                    </span>
                </p>

                @if($isTeamTurn)
                    <div class="alert alert-info py-2" id="teamTurnNotice">It is your turn to pick.</div>
                @else
                    <div class="alert alert-secondary py-2" id="teamTurnNotice">Waiting for current team to finish their pick.</div>
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
                                                <form method="POST" action="{{ route('team.draft.round.pick', ['round' => $activeRound->id, 'participant' => $player->id]) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary">Pick Player</button>
                                                </form>
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
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Draft Activity</h5>
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
                        @forelse($draftActivity as $activity)
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
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No draft activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Drafted Players</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Skill Category</th>
                            <th>Drafted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            <tr>
                                <td>{{ $participant->full_name }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>
                                    @php $participantSkills = collect($participant->skill_categories ?? [])->filter()->values()->all(); @endphp
                                    <div><strong>{{ $participant->category?->name ?: 'Uncategorized' }}</strong></div>
                                    @if(count($participantSkills))
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($participantSkills as $skill)
                                                <span class="badge text-bg-light border">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <small class="text-muted">No specific skills</small>
                                    @endif
                                </td>
                                <td>{{ optional($participant->drafted_at)->format('M d, Y H:i') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No players drafted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@php
    $teamBroadcastDriver = config('broadcasting.default');
    $teamBroadcastConnection = in_array($teamBroadcastDriver, ['reverb', 'pusher'], true)
        ? (array) config('broadcasting.connections.' . $teamBroadcastDriver, [])
        : [];
    $teamBroadcastConfig = [
        'enabled' => !empty($teamBroadcastConnection['key']),
        'channel' => 'draft.league.' . ($teamLeagueType ?? 'male'),
        'teamId' => (int) $team->id,
    ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timerElement = document.getElementById('teamTurnTimer');
        const noticeElement = document.getElementById('teamTurnNotice');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const broadcastConfig = @json($teamBroadcastConfig);
        let tickRequestInFlight = false;

        function renderTimer(secondsLeft) {
            if (!timerElement) {
                return;
            }

            const mins = Math.floor(secondsLeft / 60);
            const secs = secondsLeft % 60;
            timerElement.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }

        async function maybeAdvanceTurn(forceCheck) {
            if (!timerElement || tickRequestInFlight) {
                return;
            }

            const tickUrl = timerElement.dataset.tickUrl;

            if (!tickUrl) {
                return;
            }

            if (!forceCheck) {
                const secondsLeft = parseInt(timerElement.dataset.seconds || '0', 10);

                if (!Number.isNaN(secondsLeft) && secondsLeft > 0) {
                    return;
                }
            }

            tickRequestInFlight = true;

            try {
                const response = await fetch(tickUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();

                if (payload.advanced || payload.round_closed) {
                    window.location.reload();
                }
            } finally {
                tickRequestInFlight = false;
            }
        }

        if (timerElement) {
            let secondsLeft = parseInt(timerElement.dataset.seconds || '0', 10);
            secondsLeft = Number.isNaN(secondsLeft) ? 0 : Math.max(0, secondsLeft);
            renderTimer(secondsLeft);

            setInterval(function () {
                if (secondsLeft > 0) {
                    secondsLeft = Math.max(0, secondsLeft - 1);
                    timerElement.dataset.seconds = String(secondsLeft);
                }

                renderTimer(secondsLeft);

                if (secondsLeft === 0) {
                    maybeAdvanceTurn(false);
                }
            }, 1000);

            setInterval(function () {
                maybeAdvanceTurn(true);
            }, 15000);
        }

        if (timerElement && !broadcastConfig.enabled) {
            setInterval(function () {
                if (document.visibilityState === 'visible') {
                    window.location.reload();
                }
            }, 15000);
        }

        if (broadcastConfig.enabled && window.Echo) {
            window.Echo.channel(broadcastConfig.channel)
                .listen('.draft.turn.changed', function (payload) {
                    if (noticeElement && payload?.message) {
                        noticeElement.textContent = payload.message;
                        noticeElement.className = (payload.currentTeamId === broadcastConfig.teamId)
                            ? 'alert alert-success py-2'
                            : 'alert alert-secondary py-2';
                    }

                    window.setTimeout(function () {
                        window.location.reload();
                    }, payload?.currentTeamId === broadcastConfig.teamId ? 700 : 300);
                });
        }
    });
</script>
</body>
</html>
