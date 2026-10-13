@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-users me-2 text-primary"></i>User Accounts & Access</h3>
            <p class="text-secondary small mb-0">Manage system users, administrators, support agents, and customers.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-file-import me-1"></i> Bulk Import Users
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-user-plus me-1"></i> Create User
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, email, phone..." value="{{ request()->query('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Roles --</option>
                        <option value="admin" {{ request()->query('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="agent" {{ request()->query('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                        <option value="customer" {{ request()->query('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Departments --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request()->query('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-25 text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                        <div class="small text-secondary">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $u->isAdmin() ? 'danger' : ($u->isAgent() ? 'success' : 'secondary') }} text-uppercase">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $u->department?->name ?? 'General' }}</span>
                            </td>
                            <td class="small text-secondary">{{ $u->phone ?? '&mdash;' }}</td>
                            <td>
                                <span class="badge bg-{{ $u->is_active ? 'success' : 'danger' }}">
                                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="small text-secondary">{{ $u->created_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle', $u) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $u->is_active ? 'warning' : 'success' }}" title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fa-solid fa-{{ $u->is_active ? 'user-slash' : 'user-check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
