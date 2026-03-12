<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Participant - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-participant-edit.css') }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
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
                        <a class="nav-link active" href="/admin/participants">
                            <i class="bi bi-people"></i> Participants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/teams">
                            <i class="bi bi-shield"></i> Teams
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users">
                            <i class="bi bi-person-gear"></i> Users
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
    <div class="container main-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="text-white mb-2">Edit Participant</h1>
                <p class="text-white-50">
                    <a href="{{ route('admin.participant.view', $participant->id) }}" class="text-white-50">← Back to Details</a>
                </p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Validation errors:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-pencil-square"></i> Participant Details</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.participant.update', $participant->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="{{ $participant->first_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="{{ $participant->last_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nick Name</label>
                            <input type="text" class="form-control" name="nick_name" value="{{ $participant->nick_name }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ $participant->email }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" value="{{ $participant->dob->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nationality</label>
                            <input type="text" class="form-control" name="nationality" value="{{ $participant->nationality }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="{{ $participant->city }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" value="{{ $participant->address }}" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kit Size</label>
                            <select class="form-select" name="kit_size" required>
                                <option value="small" {{ $participant->kit_size === 'small' ? 'selected' : '' }}>Small</option>
                                <option value="medium" {{ $participant->kit_size === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="large" {{ $participant->kit_size === 'large' ? 'selected' : '' }}>Large</option>
                                <option value="xl" {{ $participant->kit_size === 'xl' ? 'selected' : '' }}>XL</option>
                                <option value="xxl" {{ $participant->kit_size === 'xxl' ? 'selected' : '' }}>XXL</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Shirt Number</label>
                            <input type="text" class="form-control" name="shirt_number" value="{{ $participant->shirt_number }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Performance</label>
                        <textarea class="form-control" name="performance" rows="3">{{ $participant->performance }}</textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Airline</label>
                            <input type="text" class="form-control" name="airline" value="{{ $participant->airline }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Arrival Date</label>
                            <input type="date" class="form-control" name="arrival_date" value="{{ $participant->arrival_date ? $participant->arrival_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Arrival Time</label>
                            <input type="time" class="form-control" name="arrival_time" value="{{ $participant->arrival_time }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hotel Name</label>
                            <input type="text" class="form-control" name="hotel_name" value="{{ $participant->hotel_name }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Checkin Date</label>
                            <input type="date" class="form-control" name="checkin" value="{{ $participant->checkin ? $participant->checkin->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Checkout Date</label>
                            <input type="date" class="form-control" name="checkout" value="{{ $participant->checkout ? $participant->checkout->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">-- No Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $participant->category_id === $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.participant.view', $participant->id) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
