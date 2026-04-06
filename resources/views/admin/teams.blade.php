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
    @include('partials.portal-navbar')

    <div class="container-fluid main-container" id="teamsPage" data-active-tab="{{ $activeTab }}" data-active-draft-category="{{ $activeDraftCategory }}" data-active-league="{{ $activeLeagueType }}">
        <div class="page-header">
            <h1><i class="bi bi-trophy"></i> Team Module</h1>
            <p>Teams are managed here and players can only be assigned through drafting.</p>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <span class="badge text-bg-dark">Active League: {{ $activeLeagueLabel }}</span>
                <a href="{{ route('admin.teams', ['tab' => $activeTab, 'category' => $activeDraftCategory, 'league' => 'male']) }}" class="btn btn-sm {{ $activeLeagueType === 'male' ? 'btn-primary' : 'btn-outline-primary' }}">Male League</a>
                <a href="{{ route('admin.teams', ['tab' => $activeTab, 'category' => $activeDraftCategory, 'league' => 'female']) }}" class="btn btn-sm {{ $activeLeagueType === 'female' ? 'btn-primary' : 'btn-outline-primary' }}">Female League</a>
            </div>
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
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="league-setup-tab" data-bs-toggle="tab" data-bs-target="#league-setup-tab-pane" type="button" role="tab">
                            <i class="bi bi-calendar3"></i> League Setup
                            @if($leagueRoundConfigs->isNotEmpty())
                                <span class="badge text-bg-primary ms-1">{{ $leagueRoundConfigs->count() }}</span>
                            @endif
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
                                        <input type="hidden" name="league_type" value="{{ $activeLeagueType }}">
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
                                            <label class="form-label">Team Color</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color" name="color" value="#6c757d" title="Pick team color">
                                                <span class="form-text mb-0">Used in pick-order badges and round tables.</span>
                                            </div>
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
                                                        <td>
                                                            <span style="display:inline-block;width:28px;height:28px;border-radius:50%;background:{{ $team->color ?? '#6c757d' }};border:2px solid #dee2e6;" title="{{ $team->color ?? '#6c757d' }}"></span>
                                                        </td>
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
                                                                data-league="{{ $team->league_type ?? 'male' }}"
                                                                data-max="{{ $team->max_players }}"
                                                                data-logo="{{ $team->logo ? asset('storage/' . $team->logo) : '' }}"
                                                                data-color="{{ $team->color ?? '#6c757d' }}"
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
                                    <h4 class="mb-1"><i class="bi bi-list-check"></i> Draft Rounds</h4>
                                    <p class="text-muted small mb-0">Each round every team picks one player in the order defined by the League Setup. Rounds auto-advance when all teams have picked.</p>
                                </div>
                                <a href="{{ route('admin.teams', ['tab' => 'categories', 'league' => $activeLeagueType]) }}" class="btn btn-outline-secondary btn-sm">
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
                                        <input type="hidden" name="league_type" value="{{ $activeLeagueType }}">
                                        @php
                                            $completedRoundsForDraft = \App\Models\DraftRound::query()->where('status','completed')->where('league_type', $activeLeagueType)->count();
                                            $upcomingRoundNum        = $completedRoundsForDraft + 1;
                                            $upcomingLeagueConfig    = $leagueRoundConfigs->firstWhere('round_number', $upcomingRoundNum);
                                            $totalLeagueRounds       = $leagueRoundConfigs->count();
                                        @endphp
                                        @if(!$leagueRoundConfigs->count())
                                            <div class="alert alert-warning mb-0">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                Configure the <a href="{{ route('admin.teams', ['tab' => 'league-setup', 'league' => $activeLeagueType]) }}">League Setup</a> first &mdash; it defines max players, number of rounds, and the pick order.
                                            </div>
                                        @elseif($upcomingRoundNum > $totalLeagueRounds)
                                            <div class="alert alert-success mb-0">
                                                <i class="bi bi-trophy"></i>
                                                All <strong>{{ $totalLeagueRounds }}</strong> league rounds have been completed. The draft is finished.
                                            </div>
                                        @else
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="alert alert-info mb-0 py-2">
                                                    <strong>Round {{ $upcomingRoundNum }} of {{ $totalLeagueRounds }}</strong> &mdash; Pick order:
                                                    @if($upcomingLeagueConfig)
                                                        <span class="ms-1 d-inline-flex flex-wrap gap-1">
                                                        @foreach($upcomingLeagueConfig->team_pick_order as $pos => $tId)
                                                            @php $pt = $teamsById->get((int)$tId); $ptc = $pt?->color ?? '#6c757d'; @endphp
                                                            <span class="badge" style="background:{{ $ptc }};color:#fff;">{{ $pos+1 }}. {{ $pt?->name ?? 'Unknown' }}</span>
                                                        @endforeach
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <label class="form-label">Primary Category</label>
                                                <select name="category_id" class="form-select" required>
                                                    <option value="">Select category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label class="form-label">Allow Also From (Higher)</label>
                                                <select name="higher_category_ids[]" class="form-select" multiple>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text">Optional. Hold Ctrl/Cmd to select multiple.</div>
                                            </div>
                                            <div class="col-lg-2">
                                                <label class="form-label">Turn Time</label>
                                                <select name="turn_time_seconds" class="form-select" required>
                                                    <option value="120">2 Minutes</option>
                                                    <option value="150" selected>2.5 Minutes</option>
                                                    <option value="180">3 Minutes</option>
                                                    <option value="240">4 Minutes</option>
                                                    <option value="300">5 Minutes</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 d-flex align-items-end">
                                                <button class="btn btn-primary w-100" type="submit">Start Round {{ $upcomingRoundNum }}</button>
                                            </div>
                                        </div>
                                        @endif
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
                                                        <th>Skill Category</th>
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
                                                            <td colspan="6" class="text-center text-muted py-4">No available players in this category round.</td>
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
                                                    <th>Skill Category</th>
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
                                                        <td>
                                                            @php $uncatSkills = collect($participant->skill_categories ?? [])->filter()->values()->all(); @endphp
                                                            <div><strong>{{ $participant->category?->name ?: 'Uncategorized' }}</strong></div>
                                                            @if(count($uncatSkills))
                                                                <div class="d-flex flex-wrap gap-1 mt-1">
                                                                    @foreach($uncatSkills as $skill)
                                                                        <span class="badge text-bg-light border">{{ $skill }}</span>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <small class="text-muted">No specific skills</small>
                                                            @endif
                                                        </td>
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
                                                        <td colspan="6" class="text-center text-muted py-4">No uncategorized players available.</td>
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
                                            <th>Skill Category</th>
                                            <th>Drafted At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($draftedParticipants as $participant)
                                            <tr>
                                                <td>{{ $participant->full_name }}</td>
                                                <td>{{ $participant->team?->name }}</td>
                                                <td>
                                                    @php $draftedSkills = collect($participant->skill_categories ?? [])->filter()->values()->all(); @endphp
                                                    <div><strong>{{ $participant->category?->name ?: 'Uncategorized' }}</strong></div>
                                                    @if(count($draftedSkills))
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            @foreach($draftedSkills as $skill)
                                                                <span class="badge text-bg-light border">{{ $skill }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <small class="text-muted">No specific skills</small>
                                                    @endif
                                                </td>
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
                                                            <a href="{{ route('admin.teams', ['tab' => 'draft', 'category' => $category->id, 'league' => $activeLeagueType]) }}" class="btn btn-sm btn-outline-secondary">
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

                    {{-- League Setup Tab --}}
                    <div class="tab-pane fade" id="league-setup-tab-pane" role="tabpanel" tabindex="0">
                        <div class="sub-card">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
                                <div>
                                    <h4 class="mb-1"><i class="bi bi-calendar3"></i> League Round Setup</h4>
                                    <p class="text-muted small mb-0">Pre-plan all draft rounds. Set the full pick order for Round 1 by dragging teams; subsequent rounds are auto-generated &mdash; first-pick team moves to last each round, 2nd becomes new first. Total rounds = max players per team.</p>
                                </div>
                                @if($leagueRoundConfigs->isNotEmpty())
                                    <form method="POST" action="{{ route('admin.league.setup.clear') }}" onsubmit="return confirm('Clear the entire league round plan?');">
                                        @csrf
                                        <input type="hidden" name="league_type" value="{{ $activeLeagueType }}">
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash"></i> Clear All Rounds</button>
                                    </form>
                                @endif
                            </div>

                            @if($teams->count() === 0)
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle"></i> Create at least one team before setting up league rounds.
                                </div>
                            @else
                                <div class="round-setup-card mb-4">
                                    <h5 class="mb-3">
                                        <i class="bi bi-gear"></i>
                                        {{ $leagueRoundConfigs->isEmpty() ? 'Configure League Rounds' : 'Reconfigure League (replaces current plan)' }}
                                    </h5>
                                    <form method="POST" action="{{ route('admin.league.setup.save') }}" id="leagueSetupForm">
                                        @csrf
                                        <input type="hidden" name="league_type" value="{{ $activeLeagueType }}">
                                        @php
                                            $r1Config     = $leagueRoundConfigs->firstWhere('round_number', 1);
                                            $r1OrderIds   = $r1Config ? collect($r1Config->team_pick_order)->map(fn($id) => (int)$id)->all() : [];
                                            // Merge: saved order first, then any new teams not yet in saved order
                                            $savedTeamIds = collect($r1OrderIds);
                                            $allTeamIds   = $teams->pluck('id')->map(fn($id) => (int)$id);
                                            $newTeamIds   = $allTeamIds->diff($savedTeamIds);
                                            $orderedTeams = $savedTeamIds->merge($newTeamIds)
                                                ->map(fn($id) => $teams->firstWhere('id', $id))
                                                ->filter()
                                                ->values();
                                        @endphp
                                        <div class="row g-3">
                                            <div class="col-lg-3">
                                                <label class="form-label fw-semibold">Max Players per Team</label>
                                                <input type="number" class="form-control" name="max_players" min="1" max="99"
                                                    value="{{ $leagueRoundConfigs->count() ?: 16 }}" required>
                                                <div class="form-text">Each team gets this many players. One round per player &mdash; total rounds = this number.</div>
                                            </div>
                                            <div class="col-lg-9">
                                                <label class="form-label fw-semibold">Round 1 &mdash; Draft Pick Order</label>
                                                <p class="form-text mb-2">Drag teams to set who picks first, second, etc. Rounds 2&ndash;{{ $leagueRoundConfigs->count() ?: 16 }} are auto-generated: first-pick team moves to last each round.</p>
                                                <ul id="round1SortableList" class="list-group" style="cursor:grab; user-select:none;">
                                                    @foreach($orderedTeams as $idx => $t)
                                                        @php $teamColor = $t->color ?? '#6c757d'; @endphp
                                                        <li class="list-group-item d-flex align-items-center gap-2 py-2"
                                                            data-team-id="{{ $t->id }}"
                                                            style="border-left: 4px solid {{ $teamColor }};">
                                                            <span class="drag-handle text-muted me-1" style="cursor:grab;"><i class="bi bi-grip-vertical"></i></span>
                                                            <span class="pick-position-badge badge" style="background:{{ $teamColor }};color:#fff;">{{ $idx + 1 }}</span>
                                                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:{{ $teamColor }};flex-shrink:0;"></span>
                                                            <span class="fw-semibold">{{ $t->name }}</span>
                                                            @if($t->short_code)
                                                                <small class="text-muted">({{ $t->short_code }})</small>
                                                            @endif
                                                            <input type="hidden" name="round1_order[]" value="{{ $t->id }}">
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                <div class="form-text mt-1"><i class="bi bi-info-circle"></i> Position 1 = first pick, last position = last pick in Round 1.</div>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="bi bi-check2-circle"></i>
                                                    {{ $leagueRoundConfigs->isEmpty() ? 'Generate All Rounds' : 'Regenerate Rounds' }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                @if($leagueRoundConfigs->isEmpty())
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0">No rounds planned yet. Fill out the form above to generate the league schedule.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="width:90px;">Round</th>
                                                    <th>Pick Order (First &rarr; Last)</th>
                                                    <th style="width:130px;">Type</th>
                                                    <th style="width:120px;">Status</th>
                                                    <th style="width:110px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($leagueRoundConfigs as $config)
                                                    @php
                                                        $roundStatus = 'planned';
                                                        if ($config->round_number < $nextLeagueRoundNumber) {
                                                            $roundStatus = 'completed';
                                                        } elseif ($config->round_number === $nextLeagueRoundNumber && $activeRound) {
                                                            $roundStatus = 'active';
                                                        }
                                                    @endphp
                                                    <tr class="{{ $roundStatus === 'active' ? 'table-primary' : ($roundStatus === 'completed' ? 'opacity-50' : '') }}">
                                                        <td class="fw-bold">Round {{ $config->round_number }}</td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($config->team_pick_order as $pos => $teamId)
                                                                    @php
                                                                        $pickTeam  = $teamsById->get((int)$teamId);
                                                                        $pickColor = $pickTeam?->color ?? '#6c757d';
                                                                        $isFirst   = $pos === 0;
                                                                        $isLast    = $pos === count($config->team_pick_order) - 1;
                                                                    @endphp
                                                                    <span class="badge" style="background:{{ $pickColor }};color:#fff;{{ $isFirst ? 'outline:2px solid #0d6efd;' : '' }}">
                                                                        {{ $pos + 1 }}. {{ $pickTeam?->name ?? 'Unknown' }}
                                                                        @if($isFirst)<small class="ms-1">(first)</small>@endif
                                                                        @if($isLast)<small class="ms-1">(last)</small>@endif
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($config->is_manually_set)
                                                                <span class="badge text-bg-warning"><i class="bi bi-pencil"></i> Manual</span>
                                                            @else
                                                                <span class="badge text-bg-light text-dark"><i class="bi bi-arrow-repeat"></i> Auto</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($roundStatus === 'completed')
                                                                <span class="badge text-bg-success">Completed</span>
                                                            @elseif($roundStatus === 'active')
                                                                <span class="badge text-bg-primary">Active</span>
                                                            @else
                                                                <span class="badge text-bg-secondary">Planned</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($roundStatus !== 'completed')
                                                                <button
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editLeagueRoundModal"
                                                                    data-round="{{ $config->round_number }}"
                                                                    data-first-team="{{ $config->team_pick_order[0] ?? '' }}"
                                                                    data-action="{{ route('admin.league.round.update', $config->round_number) }}"
                                                                >
                                                                    <i class="bi bi-pencil"></i> Override
                                                                </button>
                                                            @else
                                                                <span class="text-muted small">&mdash;</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Rotation rule:</strong> The first-pick team from round N moves to last in round N+1; the 2nd team becomes the new first pick.
                                            Use <em>Override</em> to manually reorder any future round.
                                            The planned first-pick is automatically pre-selected when starting a draft round from the <em>Draft Pool</em> tab.
                                        </small>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editLeagueRoundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Override First Pick &mdash; Round <span id="editLeagueRoundNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editLeagueRoundForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <input type="hidden" name="league_type" id="editLeagueRoundLeagueType" value="{{ $activeLeagueType }}">
                        <p class="text-muted small mb-3">The remaining teams keep their relative order from the existing plan, rotated so the chosen team goes first.</p>
                        <div class="mb-3">
                            <label class="form-label">First Pick Team</label>
                            <select class="form-select" name="first_team_id" id="editLeagueRoundFirstTeam" required>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Override</button>
                    </div>
                </form>
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
                            <label class="form-label">League</label>
                            <select class="form-select" id="edit_team_league_type" name="league_type" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Replace Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <div class="form-text">Leave empty to keep the current logo.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Team Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color" id="edit_team_color" name="color" value="#6c757d" title="Pick team color">
                                <span class="form-text mb-0">Shown in pick-order badges and round tables.</span>
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    @vite('resources/js/app.js')
    
    <script>
        // Real-time broadcast listening for draft updates
        document.addEventListener('DOMContentLoaded', function() {
            const teamsPage = document.getElementById('teamsPage');
            if (!teamsPage) return;
            
            const activeLeagueType = teamsPage.dataset.activeLeague || 'male';
            const activeDraftCategory = teamsPage.dataset.activeDraftCategory || '1';
            
            const channelName = 'draft.' + activeLeagueType + '.' + activeDraftCategory;
            let listenerAttached = false;
            
            // Show toast notification
            function showNotification(message, type = 'info') {
                const alertClass = type === 'success' ? 'alert-success' : 
                                  type === 'error' ? 'alert-danger' : 
                                  'alert-info';
                const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px;">' +
                                 message +
                                 '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                 '</div>';
                document.body.insertAdjacentHTML('afterbegin', alertHtml);
            }
            
            // Setup real-time listeners when Echo is ready
            function attachEchoListeners() {
                if (listenerAttached || !window.Echo || !window.Echo.channel) {
                    return;
                }
                
                listenerAttached = true;
                
                window.Echo.channel(channelName)
                    .listen('.player.picked', function(data) {
                        console.log('✓ Admin broadcast: Player picked -', data.participant_name, 'by', data.team_name);
                        showNotification(
                            '<strong>' + data.team_name + '</strong> picked <strong>' + data.participant_name + '</strong>',
                            'success'
                        );
                        // Refresh the page to show updated state
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    })
                    .listen('.turn.changed', function(data) {
                        console.log('✓ Admin broadcast: Turn changed');
                        showNotification(
                            'Turn advanced to the next team.',
                            'info'
                        );
                        // Refresh to show new current team and reset timer
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    })
                    .listen('.round.completed', function(data) {
                        console.log('✓ Admin broadcast: Round completed');
                        showNotification(
                            '<strong>Round completed!</strong> Next round starting...',
                            'success'
                        );
                        // Refresh to show round completion and auto-started next round
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    });
            }
            
            // Try to attach listeners immediately if Echo ready
            if (window.Echo && window.Echo.channel) {
                attachEchoListeners();
                console.log('✓ Admin Echo connected - real-time updates active');
            } else {
                // Wait for Echo to be ready (up to 5 seconds)
                let attempts = 0;
                const echoCheckInterval = setInterval(function() {
                    attempts++;
                    if (window.Echo && window.Echo.channel) {
                        clearInterval(echoCheckInterval);
                        attachEchoListeners();
                        console.log('✓ Admin Echo connected - real-time updates active');
                    } else if (attempts > 50) {
                        clearInterval(echoCheckInterval);
                        console.warn('⚠ Admin Echo not available - real-time updates disabled');
                        console.warn('  Hint: npm packages not installed. Run: npm install');
                    }
                }, 100);
            }
        });
    </script>
    
    <script>
        // ── Round 1 sortable pick-order list ────────────────────────────────────
        (function () {
            const list = document.getElementById('round1SortableList');
            if (!list) return;

            function refreshPositionBadges() {
                list.querySelectorAll('li').forEach(function (li, idx) {
                    const badge = li.querySelector('.pick-position-badge');
                    if (badge) badge.textContent = idx + 1;
                    // update the hidden input value to current team id (already set, just ensure order)
                });
            }

            Sortable.create(list, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'list-group-item-primary',
                onEnd: refreshPositionBadges,
            });
        }());
    </script>
    <script>
        function setEditTeam(button) {
            const id = button.dataset.id;
            const logo = button.dataset.logo;
            document.getElementById('editTeamForm').action = '/admin/teams/' + id;
            document.getElementById('edit_team_name').value = button.dataset.name || '';
            document.getElementById('edit_team_short_code').value = button.dataset.short || '';
            document.getElementById('edit_team_captain_name').value = button.dataset.captain || '';
            document.getElementById('edit_team_email').value = button.dataset.email || '';
            document.getElementById('edit_team_league_type').value = button.dataset.league || 'male';
            document.getElementById('edit_team_color').value = button.dataset.color || '#6c757d';

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

        const leagueRoundModal = document.getElementById('editLeagueRoundModal');
        if (leagueRoundModal) {
            leagueRoundModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                const roundNumber = button.getAttribute('data-round');
                const firstTeamId = button.getAttribute('data-first-team');
                const actionUrl   = button.getAttribute('data-action');
                document.getElementById('editLeagueRoundNumber').textContent = roundNumber || '';
                const form = document.getElementById('editLeagueRoundForm');
                if (form && actionUrl) form.action = actionUrl;
                const select = document.getElementById('editLeagueRoundFirstTeam');
                if (select && firstTeamId) select.value = firstTeamId;
            });
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
