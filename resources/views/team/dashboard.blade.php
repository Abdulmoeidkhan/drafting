<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container-fluid">
        <span class="navbar-brand">Team Dashboard</span>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('activities.index') }}" class="btn btn-sm btn-outline-light">All Activities</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
            </form>
        </div>
    </div>
</nav>

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
                <p class="mb-3"><strong>Time Remaining:</strong> {{ $remainingTurnSeconds }}s</p>

                @if($isTeamTurn)
                    <div class="alert alert-info py-2">It is your turn to pick.</div>
                @else
                    <div class="alert alert-secondary py-2">Waiting for current team to finish their pick.</div>
                @endif

                @if($draftPoolParticipants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Player</th>
                                    <th>Email</th>
                                    <th>Category</th>
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
                                        <td>{{ $player->category?->name ?: 'Uncategorized' }}</td>
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
                            <th>Category</th>
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
                                <td>{{ $activity->round?->category?->name ?: ($activity->participant?->category?->name ?: 'Uncategorized') }}</td>
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
                            <th>Category</th>
                            <th>Drafted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            <tr>
                                <td>{{ $participant->full_name }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ $participant->category?->name ?: 'Uncategorized' }}</td>
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
</body>
</html>
