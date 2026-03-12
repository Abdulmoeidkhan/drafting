<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <span class="navbar-brand">Team Dashboard</span>
        <form method="POST" action="{{ route('logout') }}" class="ms-auto">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
        </form>
    </div>
</nav>

<div class="container py-4">
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-2">{{ $team->name }}</h4>
            <p class="mb-1"><strong>Email:</strong> {{ $team->email }}</p>
            <p class="mb-1"><strong>Captain:</strong> {{ $team->captain_name ?: 'Not set' }}</p>
            <p class="mb-0"><strong>Roster:</strong> {{ $participants->count() }} / {{ $team->max_players }}</p>
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
