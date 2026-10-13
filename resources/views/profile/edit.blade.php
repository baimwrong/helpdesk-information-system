@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-user-gear me-2 text-primary"></i>My Profile Settings</h4>
                <span class="badge bg-{{ $user->isAdmin() ? 'danger' : ($user->isAgent() ? 'success' : 'secondary') }} text-uppercase px-3 py-2">
                    Role: {{ $user->role }}
                </span>
            </div>

            <!-- Profile Info Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0">Personal Information</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label small fw-semibold text-secondary">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label small fw-semibold text-secondary">Phone / Extension</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+1-555-0199">
                            </div>

                            <div class="col-md-6">
                                <label for="department_id" class="form-label small fw-semibold text-secondary">Department</label>
                                <select name="department_id" id="department_id" class="form-select">
                                    <option value="">-- None / General --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0">Change Password</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="current_password" class="form-label small fw-semibold text-secondary">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-semibold text-secondary">New Password</label>
                                <input type="password" name="password" id="password" class="form-control" required placeholder="Minimum 8 characters">
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-warning px-4">
                                    <i class="fa-solid fa-key me-2"></i>Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
