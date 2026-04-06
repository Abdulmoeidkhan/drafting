<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
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
            <p class="mb-1"><strong>League:</strong> {{ ucfirst((string) ($teamLeagueType ?? $team->league_type ?? 'male')) }}</p>
            <p class="mb-1"><strong>Captain:</strong> {{ $team->captain_name ?: 'Not set' }}</p>
            <p class="mb-0"><strong>Roster:</strong> {{ $participants->count() }} / {{ $team->max_players }}</p>
        </div>
    </div>

    <div class="card mb-4" data-broadcast-config="true" data-broadcast-league="{{ $activeRound->league_type ?? '' }}" data-broadcast-category="{{ $activeRound->category_id ?? '' }}" data-broadcast-round="{{ $activeRound->id ?? '' }}" data-broadcast-current-team="{{ $activeRound->current_team_id ?? '' }}" data-team-id="{{ $team->id }}" data-timer-seconds="{{ $remainingTurnSeconds }}" data-timer-start="{{ $activeRound->current_turn_started_at ?? '' }}" data-timer-duration="{{ $activeRound->turn_time_seconds ?? '' }}">
        <div class="card-body">
            <h5 class="mb-3">Active Draft Round</h5>

            @if($activeRound)
                <p class="mb-1"><strong>Category:</strong> {{ $activeRound->category?->name ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Current Team:</strong> {{ $activeRound->currentTeam?->name ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Your Picks This Round:</strong> {{ $teamRoundPicksCount }} / {{ $activeRound->picks_per_team }}</p>
                <p class="mb-3"><strong>Time Remaining:</strong> <span data-timer-display>{{ $remainingTurnSeconds }}</span>s</p>

                @if($isTeamTurn)
                    <div class="alert alert-info py-2" data-turn-status><i class="bi bi-clock"></i> <strong>🎯 It is YOUR turn to pick!</strong></div>
                @else
                    <div class="alert alert-secondary py-2" data-turn-status>Waiting for current team to finish their pick.</div>
                @endif

                <div class="mt-2">
                    <div id="broadcast-status" style="padding: 8px; border-radius: 4px; font-size: 0.85rem;">
                        <span style="color: #666;">Initializing real-time connection...</span>
                    </div>
                </div>

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
                                                    <button type="submit" class="btn btn-sm btn-primary" data-pick-button>Pick Player</button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled data-pick-button>Pick Player</button>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@vite('resources/js/app.js')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const configElement = document.querySelector('[data-broadcast-config="true"]');
    if (!configElement) {
        console.warn('⚠ Broadcast config element not found!');
        return;
    }

    const leagueType = configElement.dataset.broadcastLeague;
    const categoryId = configElement.dataset.broadcastCategory;
    const roundId = parseInt(configElement.dataset.broadcastRound, 10);
    const teamId = parseInt(configElement.dataset.teamId, 10);
    const timerSeconds = parseInt(configElement.dataset.timerSeconds, 10);
    const timerStartStr = configElement.dataset.timerStart;
    const timerDuration = parseInt(configElement.dataset.timerDuration, 10);
    const currentTeamId = parseInt(configElement.dataset.broadcastCurrentTeam, 10);

    const statusElement = document.getElementById('broadcast-status');
    let timerInterval = null;

    function updateConnectionStatus(message, color) {
        if (statusElement) {
            statusElement.innerHTML = '<span style="color:' + color + '">' + message + '</span>';
        }
    }

    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' :
                          'alert-info';

        const html = `
            <div class="alert ${alertClass} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        const container = document.querySelector('.container');
        if (container) container.insertAdjacentHTML('afterbegin', html);
    }

    function updateButtonStates(newCurrentTeamId) {
        const buttons = document.querySelectorAll('[data-pick-button]');
        const alertBox = document.querySelector('[data-turn-status]');
        const isTurn = (newCurrentTeamId === teamId);

        buttons.forEach(btn => {
            btn.disabled = !isTurn;
            btn.classList.toggle('btn-primary', isTurn);
            btn.classList.toggle('btn-outline-secondary', !isTurn);
        });

        if (alertBox) {
            alertBox.className = 'alert py-2 ' + (isTurn ? 'alert-info' : 'alert-secondary');
            alertBox.innerHTML = isTurn
                ? '<i class="bi bi-clock"></i> <strong>🎯 Your turn!</strong>'
                : 'Waiting for current team...';
        }
    }

    function updateTimer() {
        const el = document.querySelector('[data-timer-display]');
        if (!el || !timerStartStr) return;

        const start = new Date(timerStartStr).getTime();
        const now = Date.now();
        const remaining = Math.max(0, (timerDuration * 1000) - (now - start));
        const seconds = Math.ceil(remaining / 1000);

        el.textContent = seconds + 's';

        if (seconds === 0) {
            clearInterval(timerInterval);
            setTimeout(() => location.reload(), 1000);
        }
    }

    if (timerSeconds >= 0 && timerStartStr) {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    updateButtonStates(currentTeamId);

    function attachEchoListeners() {
        if (!window.Echo) {
            setTimeout(attachEchoListeners, 500);
            return;
        }

        const channelName = `draft.${leagueType}.${categoryId}`;
        updateConnectionStatus('✓ Connected', '#28a745');

        window.Echo.channel(channelName)
            .listen('.player.picked', data => {
                if (data.draft_round_id === roundId) {
                    showNotification(`<strong>${data.team_name}</strong> picked ${data.participant_name}`, 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .listen('.turn.changed', data => {
                if (data.draft_round_id === roundId) {
                    updateButtonStates(data.current_team_id);
                    showNotification(
                        data.current_team_id === teamId
                            ? '<strong>🎯 Your turn!</strong>'
                            : 'Another team is picking',
                        'info'
                    );
                }
            })
            .listen('.round.completed', data => {
                if (data.draft_round_id === roundId) {
                    showNotification('Round completed!', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            });
    }

    attachEchoListeners();

    window.addEventListener('beforeunload', () => {
        if (timerInterval) clearInterval(timerInterval);
    });
});
</script>
</body>
</html>
