<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-users.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/navbar.css') }}">
</head>
<body>
    <!-- Navbar -->
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
                        <a class="nav-link" href="/admin/dashboard">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/participants">
                            <i class="bi bi-people"></i> Participants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/teams">
                            <i class="bi bi-shield"></i> Teams
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/users">
                            <i class="bi bi-person-gear"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('activities.index') }}">
                            <i class="bi bi-activity"></i> Activities
                        </a>
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

    <!-- Main Content -->
    <div class="container-fluid main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="bi bi-person-gear"></i> Users Management</h1>
                <p>Create and manage admin user accounts</p>
            </div>
            <button class="add-user-btn" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus"></i> Add New User
            </button>
        </div>

        <!-- Users List -->
        @if($users->count() > 0)
            <div class="row">
                @foreach($users as $user)
                    <div class="col-12">
                        <div class="user-card">
                            <div class="user-header">
                                <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                <div class="user-header-content">
                                    <h5>{{ $user->name }}</h5>
                                    <p>{{ $user->email }}</p>
                                    <div>
                                        @forelse($user->getRoleNames() as $roleName)
                                            <span class="badge text-bg-secondary me-1">{{ ucfirst($roleName) }}</span>
                                        @empty
                                            <span class="badge text-bg-light">No role</span>
                                        @endforelse
                                    </div>
                                </div>
                                @if($user->is_admin)
                                    <div class="admin-badge">
                                        <i class="bi bi-shield-check"></i> Administrator
                                    </div>
                                @endif
                            </div>

                            <div class="user-info">
                                <div class="info-item">
                                    <span class="info-label">Created</span>
                                    <span class="info-value">{{ $user->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status</span>
                                    <span class="info-value">
                                        <span style="color: var(--success-color); font-weight: 600;">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="user-actions">
                                <button
                                    class="action-btn-small edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editUserModal"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-roles='@json($user->getRoleNames()->values())'
                                    onclick="editUser(this)"
                                >
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                @if(auth()->user()->id !== $user->id)
                                    <form method="POST" action="/admin/users/{{ $user->id }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn-small delete" onclick="return confirm('Delete this user?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-people"></i>
                <h3>No users found</h3>
                <p>Create your first admin user to get started</p>
            </div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Create New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/admin/users">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign Roles</label>
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="create_role_{{ $role }}" name="roles[]" value="{{ $role }}">
                                    <label class="form-check-label" for="create_role_{{ $role }}">
                                        {{ ucfirst($role) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assign Roles</label>
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input edit-role-checkbox" type="checkbox" id="edit_role_{{ $role }}" name="roles[]" value="{{ $role }}">
                                    <label class="form-check-label" for="edit_role_{{ $role }}">
                                        {{ ucfirst($role) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(button) {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const email = button.dataset.email;
            const roles = JSON.parse(button.dataset.roles || '[]');

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('editForm').action = '/admin/users/' + id;

            document.querySelectorAll('.edit-role-checkbox').forEach((checkbox) => {
                checkbox.checked = roles.includes(checkbox.value);
            });
        }
    </script>
</body>
</html>
