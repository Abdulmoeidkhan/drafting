<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark sticky-top app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ auth()->user()->isAdmin() ? '/admin/dashboard' : (auth()->user()->hasRole('team') ? route('team.dashboard') : route('player.profile')) }}">
            <i class="bi bi-diagram-3"></i> Admin Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard"><i class="bi bi-house"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/participants"><i class="bi bi-people"></i> Participants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/teams"><i class="bi bi-shield"></i> Teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users"><i class="bi bi-person-gear"></i> Users</a>
                    </li>
                @elseif(auth()->user()->hasRole('team'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('team.dashboard') }}"><i class="bi bi-speedometer2"></i> Team Dashboard</a>
                    </li>
                @elseif(auth()->user()->hasRole('player'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('player.profile') }}"><i class="bi bi-person-circle"></i> Player Profile</a>
                    </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('activities.index') }}"><i class="bi bi-activity"></i> Activities</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
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

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Filters</h5>
            <form method="GET" action="{{ route('activities.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="team_id" class="form-label">Team</label>
                    <select name="team_id" id="team_id" class="form-select">
                        <option value="">All Teams</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ (string) $filters['team_id'] === (string) $team->id ? 'selected' : '' }}>
                                {{ $team->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="round_id" class="form-label">Round</label>
                    <select name="round_id" id="round_id" class="form-select">
                        <option value="">All Rounds</option>
                        @foreach($rounds as $round)
                            <option value="{{ $round->id }}" {{ (string) $filters['round_id'] === (string) $round->id ? 'selected' : '' }}>
                                #{{ $round->id }} - {{ $round->category?->name ?: 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) $filters['category_id'] === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="from" class="form-label">From Date</label>
                    <input type="date" name="from" id="from" value="{{ $filters['from'] }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label for="to" class="form-label">To Date</label>
                    <input type="date" name="to" id="to" value="{{ $filters['to'] }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Player/team/email">
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('activities.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-success">Export CSV</a>
                    <a href="{{ route('activities.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">All Draft Activities</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Round</th>
                            <th>Pick #</th>
                            <th>Team</th>
                            <th>Player</th>
                            <th>Photo</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td>{{ optional($activity->picked_at)->format('M d, Y H:i:s') ?: '-' }}</td>
                                <td>#{{ $activity->draft_round_id }}</td>
                                <td>{{ $activity->pick_number }}</td>
                                <td>{{ $activity->team?->name ?: 'N/A' }}</td>
                                <td>{{ $activity->participant?->full_name ?: 'N/A' }}</td>
                                <td>
                                    @if($activity->participant?->passport_picture)
                                        <img
                                            src="{{ asset('storage/' . ltrim($activity->participant->passport_picture, '/')) }}"
                                            alt="{{ $activity->participant?->full_name ?: 'Player' }}"
                                            class="rounded"
                                            style="width: 44px; height: 44px; object-fit: cover;"
                                        >
                                    @else
                                        <span class="badge text-bg-secondary">No Photo</span>
                                    @endif
                                </td>
                                <td>{{ $activity->round?->category?->name ?: ($activity->participant?->category?->name ?: 'Uncategorized') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No activities found for current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($activities->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} activities
                    </div>
                    <div class="d-flex gap-2">
                        @if($activities->onFirstPage())
                            <span class="btn btn-sm btn-outline-secondary disabled">Previous</span>
                        @else
                            <a href="{{ $activities->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">Previous</a>
                        @endif

                        @if($activities->hasMorePages())
                            <a href="{{ $activities->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">Next</a>
                        @else
                            <span class="btn btn-sm btn-outline-secondary disabled">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
