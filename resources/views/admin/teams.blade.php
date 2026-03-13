<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teams & Draft - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-teams.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top app-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard">
                <i class="bi bi-diagram-3"></i> Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard"><i class="bi bi-house"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/participants"><i class="bi bi-people"></i> Participants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/teams"><i class="bi bi-shield"></i> Teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users"><i class="bi bi-person-gear"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('activities.index') }}"><i class="bi bi-activity"></i> Activities</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="/logout" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link" style="border: none; background: none; cursor: pointer;">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-container" id="teamsPage" data-active-tab="{{ $activeTab }}" data-active-draft-category="{{ $activeDraftCategory }}">
        <div class="page-header">
            <h1><i class="bi bi-trophy"></i> Team Module</h1>
            <p>Teams are managed here and players can only be assigned through drafting.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('account_credentials'))
            @php
                $credentials = session('account_credentials');
            @endphp
            <div class="alert alert-warning">
                <strong>{{ $credentials['label'] ?? 'New Account' }} Credentials:</strong>
                <div>Email: <code>{{ $credentials['email'] ?? '' }}</code></div>
                <div>Password: <code>{{ $credentials['password'] ?? '' }}</code></div>
                <small class="text-muted">Share these once and ask user to change password after first login.</small>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card section-card mb-4">
            <div class="card-body">
                <ul class="nav nav-tabs" id="teamTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="teams-tab" data-bs-toggle="tab" data-bs-target="#teams-tab-pane" type="button" role="tab">
                            <i class="bi bi-shield"></i> Teams
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-tab-pane" type="button" role="tab">
                            <i class="bi bi-list-check"></i> Draft Pool
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="drafted-tab" data-bs-toggle="tab" data-bs-target="#drafted-tab-pane" type="button" role="tab">
                            <i class="bi bi-collection"></i> Drafted Players
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories-tab-pane" type="button" role="tab">
                            <i class="bi bi-tags"></i> Categories
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4" id="teamTabsContent">
                    <div class="tab-pane fade show active" id="teams-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="sub-card">
                                    <h4><i class="bi bi-plus-circle"></i> Create Team</h4>
                                    <form method="POST" action="{{ route('admin.team.create') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Team Name</label>
                                            <input class="form-control" name="name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Short Code</label>
                                            <input class="form-control" name="short_code" maxlength="10" placeholder="Optional">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Captain Name</label>
                                            <input class="form-control" name="captain_name" placeholder="Optional">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Team Login Email</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Team Logo</label>
                                            <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                                            <div class="form-text">Optional. PNG, JPG, or WEBP up to 2MB.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Max Players</label>
                                            <input type="number" class="form-control" name="max_players" min="1" max="30" value="11" required>
                                        </div>
                                        <button class="btn btn-primary w-100" type="submit">Create Team</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="sub-card">
                                    <h4><i class="bi bi-list"></i> Existing Teams</h4>
                                    <p class="text-muted small mb-3">Players cannot be selected here directly. Use the Draft Pool tab.</p>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Logo</th>
                                                    <th>Team</th>
                                                    <th>Captain</th>
                                                    <th>Roster</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($teams as $team)
                                                    <tr>
                                                        <td>
                                                            @if($team->logo)
                                                                <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }} logo" class="team-logo-thumb">
                                                            @else
                                                                <div class="team-logo-placeholder"><i class="bi bi-image"></i></div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <strong>{{ $team->name }}</strong>
                                                            @if($team->short_code)
                                                                <div class="small text-muted">{{ $team->short_code }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $team->captain_name ?: 'Not set' }}</td>
                                                        <td>{{ $team->participants_count }} / {{ $team->max_players }}</td>
                                                        <td class="actions-cell">
                                                            <button
                                                                class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editTeamModal"
                                                                data-id="{{ $team->id }}"
                                                                data-name="{{ $team->name }}"
                                                                data-short="{{ $team->short_code }}"
                                                                data-captain="{{ $team->captain_name }}"
                                                                data-email="{{ $team->email }}"
                                                                data-max="{{ $team->max_players }}"
                                                                data-logo="{{ $team->logo ? asset('storage/' . $team->logo) : '' }}"
                                                                onclick="setEditTeam(this)"
                                                            >
                                                                Edit
                                                            </button>
                                                            <form method="POST" action="{{ route('admin.team.delete', $team->id) }}" onsubmit="return confirm('Delete this team? Drafted players will return to draft pool.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">No teams found. Create your first team.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="draft-tab-pane" role="tabpanel" tabindex="0">
                        <div class="sub-card">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                                <div>
                                    <h4 class="mb-1"><i class="bi bi-list-check"></i> Category-Wise Draft Rounds</h4>
                                    <p class="text-muted small mb-0">Start a round by selecting category, start team, pick count, and turn timer. Picks then rotate one-by-one.</p>
                                </div>
                                <a href="{{ route('admin.teams', ['tab' => 'categories']) }}" class="btn btn-outline-secondary btn-sm">
                                    Manage Categories
                                </a>
                            </div>

                            <div class="round-setup-card mb-4">
                                <h5 class="mb-3"><i class="bi bi-gear"></i> Draft Round Setup</h5>
                                @if($activeRound)
                                    <div class="alert alert-info mb-0">
                                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                                            <div>
                                                <strong>Active Round:</strong>
                                                {{ $activeRound->category?->name }}
                                                <span class="mx-1">|</span>
                                                Current Turn: <strong>{{ $activeRound->currentTeam?->name }}</strong>
                                                <span class="mx-1">|</span>
                                                Pick {{ $activeRound->current_pick_number }} / {{ $activeRound->total_picks_planned }}
                                                <span class="mx-1">|</span>
                                                Timer: <strong id="turnTimer" data-seconds="{{ $activeRoundRemainingSeconds }}" data-tick-url="{{ route('admin.draft.round.tick', $activeRound->id) }}">--:--</strong>
                                            </div>
                                            <form method="POST" action="{{ route('admin.draft.round.close', $activeRound->id) }}" onsubmit="return confirm('Close the active round now?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Close Round</button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('admin.draft.round.start') }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-lg-3">
                                                <label class="form-label">Primary Category</label>
                                                <select name="category_id" class="form-select" required>
                                                    <option value="">Select category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <label class="form-label">Allow Also From (Higher)</label>
                                                <select name="higher_category_ids[]" class="form-select" multiple>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text">Optional. Example: In Gold round, also allow Platinum.</div>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Starting Team</label>
                                                <select name="starting_team_id" class="form-select" required>
                                                    <option value="">Select team</option>
                                                    @foreach($teams as $team)
                                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Picks / Team</label>
                                                <input type="number" class="form-control" name="picks_per_team" value="1" min="1" max="10" required>
                                                <div class="form-text">Set `1` for round-1 style flow.</div>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Turn Time</label>
                                                <select name="turn_time_seconds" class="form-select" required>
                                                    <option value="120">2 Minutes</option>
                                                    <option value="150" selected>2.5 Minutes</option>
                                                    <option value="180">3 Minutes</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-primary" type="submit">Start Draft Round</button>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            @if($activeRound)
                                <div class="mb-4">
                                    <h6 class="mb-2">Round Progress By Team</h6>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Team</th>
                                                    <th>Picks This Round</th>
                                                    <th>Target</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($teams as $team)
                                                    @php
                                                        $teamPicks = $activeRoundTeamPickCounts[$team->id] ?? 0;
                                                        $isTurn = (int) $activeRound->current_team_id === (int) $team->id;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $team->name }}</td>
                                                        <td>{{ $teamPicks }}</td>
                                                        <td>{{ $activeRound->picks_per_team }}</td>
                                                        <td>
                                                            @if($isTurn)
                                                                <span class="badge text-bg-primary">Current Turn</span>
                                                            @elseif($teamPicks >= $activeRound->picks_per_team)
                                                                <span class="badge text-bg-success">Completed</span>
                                                            @else
                                                                <span class="badge text-bg-secondary">Waiting</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <ul class="nav nav-pills draft-category-tabs mb-3" id="draftCategoryTabs" role="tablist">
                                @foreach($categories as $category)
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="draft-category-{{ $category->id }}-tab"
                                            data-bs-toggle="pill"
                                            data-bs-target="#draft-category-{{ $category->id }}"
                                            type="button"
                                            role="tab"
                                            data-category-key="{{ $category->id }}"
                                        >
                                            {{ $category->name }}
                                            <span class="category-count-badge">{{ $category->draftable_participants_count }}</span>
                                        </button>
                                    </li>
                                @endforeach
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link"
                                        id="draft-category-uncategorized-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#draft-category-uncategorized"
                                        type="button"
                                        role="tab"
                                        data-category-key="uncategorized"
                                    >
                                        Uncategorized
                                        <span class="category-count-badge">{{ $uncategorizedDraftableParticipants->count() }}</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="draftCategoryTabContent">
                                @foreach($categories as $category)
                                    <div class="tab-pane fade" id="draft-category-{{ $category->id }}" role="tabpanel" tabindex="0">
                                        <div class="draft-round-header">
                                            <div>
                                                <h5 class="mb-1">{{ $category->name }} Round</h5>
                                                <p class="text-muted small mb-0">Only approved undrafted players from this category are available.</p>
                                            </div>
                                            <span class="badge text-bg-light">{{ $category->draftable_participants_count }} available</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Photo</th>
                                                        <th>Player</th>
                                                        <th>Email</th>
                                                        <th>City</th>
                                                        <th>Draft To Team</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($category->participants as $participant)
                                                        <tr>
                                                            <td>
                                                                @if($participant->passport_picture)
                                                                    <img
                                                                        src="{{ asset('storage/' . ltrim($participant->passport_picture, '/')) }}"
                                                                        alt="{{ $participant->full_name }}"
                                                                        class="rounded"
                                                                        style="width: 44px; height: 44px; object-fit: cover;"
                                                                    >
                                                                @else
                                                                    <span class="badge text-bg-secondary">No Photo</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $participant->full_name }}</td>
                                                            <td>{{ $participant->email }}</td>
                                                            <td>{{ $participant->city }}</td>
                                                            <td>
                                                                @if($activeRound)
                                                                    @php
                                                                        $isEligible = in_array((int) $category->id, array_map('intval', $activeRoundEligibleCategoryIds), true);
                                                                        $currentTeam = $teams->firstWhere('id', $activeRound->current_team_id);
                                                                    @endphp

                                                                    @if($isEligible && $currentTeam)
                                                                        <form method="POST" action="{{ route('admin.draft.round.pick', [$activeRound->id, $participant->id]) }}">
                                                                            @csrf
                                                                            <button class="btn btn-sm btn-primary" type="submit">
                                                                                Pick For {{ $currentTeam->name }}
                                                                            </button>
                                                                        </form>
                                                                    @elseif(!$isEligible)
                                                                        <span class="badge text-bg-secondary">Not in round eligibility</span>
                                                                    @else
                                                                        <span class="badge text-bg-secondary">No current team</span>
                                                                    @endif
                                                                @elseif($teams->count() > 0)
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                            Draft (Legacy)
                                                                        </button>
                                                                        <ul class="dropdown-menu">
                                                                            @foreach($teams as $team)
                                                                                <li>
                                                                                    <form method="POST" action="{{ route('admin.team.draft', [$team->id, $participant->id]) }}">
                                                                                        @csrf
                                                                                        <button class="dropdown-item" type="submit">
                                                                                            {{ $team->name }} ({{ $team->participants_count }}/{{ $team->max_players }})
                                                                                        </button>
                                                                                    </form>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @else
                                                                    <span class="badge text-bg-secondary">Create team first</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">No available players in this category round.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="tab-pane fade" id="draft-category-uncategorized" role="tabpanel" tabindex="0">
                                    <div class="draft-round-header">
                                        <div>
                                            <h5 class="mb-1">Uncategorized Round</h5>
                                            <p class="text-muted small mb-0">Players here need no category or can be drafted from this fallback pool.</p>
                                        </div>
                                        <span class="badge text-bg-light">{{ $uncategorizedDraftableParticipants->count() }} available</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Photo</th>
                                                    <th>Player</th>
                                                    <th>Email</th>
                                                    <th>City</th>
                                                    <th>Draft To Team</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($uncategorizedDraftableParticipants as $participant)
                                                    <tr>
                                                        <td>
                                                            @if($participant->passport_picture)
                                                                <img
                                                                    src="{{ asset('storage/' . ltrim($participant->passport_picture, '/')) }}"
                                                                    alt="{{ $participant->full_name }}"
                                                                    class="rounded"
                                                                    style="width: 44px; height: 44px; object-fit: cover;"
                                                                >
                                                            @else
                                                                <span class="badge text-bg-secondary">No Photo</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $participant->full_name }}</td>
                                                        <td>{{ $participant->email }}</td>
                                                        <td>{{ $participant->city }}</td>
                                                        <td>
                                                            @if($activeRound)
                                                                <span class="badge text-bg-secondary">Uncategorized is not part of active round rules</span>
                                                            @elseif($teams->count() > 0)
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                        Draft
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        @foreach($teams as $team)
                                                                            <li>
                                                                                <form method="POST" action="{{ route('admin.team.draft', [$team->id, $participant->id]) }}">
                                                                                    @csrf
                                                                                    <button class="dropdown-item" type="submit">
                                                                                        {{ $team->name }} ({{ $team->participants_count }}/{{ $team->max_players }})
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @else
                                                                <span class="badge text-bg-secondary">Create team first</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">No uncategorized players available.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="drafted-tab-pane" role="tabpanel" tabindex="0">
                        <div class="sub-card">
                            <h4><i class="bi bi-collection"></i> Drafted Players</h4>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Category</th>
                                            <th>Drafted At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($draftedParticipants as $participant)
                                            <tr>
                                                <td>{{ $participant->full_name }}</td>
                                                <td>{{ $participant->team?->name }}</td>
                                                <td>{{ $participant->category?->name ?: 'Uncategorized' }}</td>
                                                <td>{{ optional($participant->drafted_at)->format('M d, Y H:i') }}</td>
                                                <td>
                                                    @if($participant->team)
                                                        <form method="POST" action="{{ route('admin.team.release', [$participant->team->id, $participant->id]) }}" onsubmit="return confirm('Release this player back to draft pool?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-warning" type="submit">Release</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No drafted players yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="categories-tab-pane" role="tabpanel" tabindex="0">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="sub-card">
                                    <h4><i class="bi bi-tag"></i> Create Category</h4>
                                    <form method="POST" action="{{ route('admin.category.create') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Category Name</label>
                                            <input class="form-control" name="name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="description" rows="3" placeholder="Optional"></textarea>
                                        </div>
                                        <button class="btn btn-primary w-100" type="submit">Create Category</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="sub-card">
                                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                                        <div>
                                            <h4 class="mb-1"><i class="bi bi-tags"></i> Player Categories</h4>
                                            <p class="text-muted small mb-0">These categories are linked to players and used to start category-wise draft rounds.</p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Description</th>
                                                    <th>Players</th>
                                                    <th>Round</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($categories as $category)
                                                    <tr>
                                                        <td><strong>{{ $category->name }}</strong></td>
                                                        <td>{{ $category->description ?: 'No description' }}</td>
                                                        <td>{{ $category->participants_count }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.teams', ['tab' => 'draft', 'category' => $category->id]) }}" class="btn btn-sm btn-outline-secondary">
                                                                Start Draft Round
                                                            </a>
                                                        </td>
                                                        <td class="actions-cell">
                                                            <button
                                                                class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editCategoryModal"
                                                                data-id="{{ $category->id }}"
                                                                data-name="{{ $category->name }}"
                                                                data-description="{{ $category->description }}"
                                                                onclick="setEditCategory(this)"
                                                            >
                                                                Edit
                                                            </button>
                                                            <form method="POST" action="{{ route('admin.category.delete', $category->id) }}" onsubmit="return confirm('Delete this category? Linked players will become uncategorized.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">No categories found. Create a category to start category-wise draft rounds.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTeamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editTeamForm" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Logo</label>
                            <div id="edit_team_logo_preview" class="team-logo-preview-empty">
                                <i class="bi bi-image"></i>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Team Name</label>
                            <input class="form-control" id="edit_team_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Short Code</label>
                            <input class="form-control" id="edit_team_short_code" name="short_code" maxlength="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Captain Name</label>
                            <input class="form-control" id="edit_team_captain_name" name="captain_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Team Login Email</label>
                            <input type="email" class="form-control" id="edit_team_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Replace Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <div class="form-text">Leave empty to keep the current logo.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Players</label>
                            <input type="number" class="form-control" id="edit_team_max_players" name="max_players" min="1" max="30" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editCategoryForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input class="form-control" id="edit_category_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="edit_category_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setEditTeam(button) {
            const id = button.dataset.id;
            const logo = button.dataset.logo;
            document.getElementById('editTeamForm').action = '/admin/teams/' + id;
            document.getElementById('edit_team_name').value = button.dataset.name || '';
            document.getElementById('edit_team_short_code').value = button.dataset.short || '';
            document.getElementById('edit_team_captain_name').value = button.dataset.captain || '';
            document.getElementById('edit_team_email').value = button.dataset.email || '';
            document.getElementById('edit_team_max_players').value = button.dataset.max || 11;

            const preview = document.getElementById('edit_team_logo_preview');

            if (logo) {
                preview.className = 'team-logo-preview';
                preview.innerHTML = '<img src="' + logo + '" alt="Team logo preview">';
            } else {
                preview.className = 'team-logo-preview-empty';
                preview.innerHTML = '<i class="bi bi-image"></i>';
            }
        }

        function setEditCategory(button) {
            document.getElementById('editCategoryForm').action = '/admin/categories/' + button.dataset.id;
            document.getElementById('edit_category_name').value = button.dataset.name || '';
            document.getElementById('edit_category_description').value = button.dataset.description || '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const page = document.getElementById('teamsPage');
            const activeTopTab = page?.dataset.activeTab || 'teams';
            const activeDraftCategory = page?.dataset.activeDraftCategory || 'uncategorized';
            const topTabButton = document.getElementById(activeTopTab + '-tab');
            const timerElement = document.getElementById('turnTimer');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let skipRequestInFlight = false;

            function updateTurnTimer(secondsLeft) {
                if (!timerElement) {
                    return;
                }

                const mins = Math.floor(secondsLeft / 60);
                const secs = secondsLeft % 60;
                timerElement.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            }

            async function maybeSkipTurn(forceCheck) {
                if (!timerElement || skipRequestInFlight) {
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

                skipRequestInFlight = true;

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

                    const data = await response.json();

                    if (data.advanced || data.round_closed) {
                        window.location.reload();
                    }
                } finally {
                    skipRequestInFlight = false;
                }
            }

            if (timerElement) {
                let secondsLeft = parseInt(timerElement.dataset.seconds || '0', 10);
                secondsLeft = Number.isNaN(secondsLeft) ? 0 : Math.max(0, secondsLeft);
                updateTurnTimer(secondsLeft);

                setInterval(function () {
                    if (secondsLeft > 0) {
                        secondsLeft = Math.max(0, secondsLeft - 1);
                        timerElement.dataset.seconds = String(secondsLeft);
                    }

                    updateTurnTimer(secondsLeft);

                    if (secondsLeft === 0) {
                        maybeSkipTurn(false);
                    }
                }, 1000);

                // Periodic server sync in case multiple admins are viewing the draft board.
                setInterval(function () {
                    maybeSkipTurn(true);
                }, 15000);
            }

            if (topTabButton) {
                bootstrap.Tab.getOrCreateInstance(topTabButton).show();
            }

            const draftCategoryButton = document.querySelector('[data-category-key="' + activeDraftCategory + '"]');
            const fallbackDraftCategoryButton = document.querySelector('#draftCategoryTabs .nav-link');

            if (draftCategoryButton) {
                bootstrap.Tab.getOrCreateInstance(draftCategoryButton).show();
            } else if (fallbackDraftCategoryButton) {
                bootstrap.Tab.getOrCreateInstance(fallbackDraftCategoryButton).show();
            }
        });
    </script>
</body>
</html>
