@extends('layouts.app')

@section('title', 'Departments Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-building-user me-2 text-primary"></i>Departments</h3>
            <p class="text-secondary small mb-0">Organize business units and ticket assignment routing groups.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-file-import me-1"></i> Bulk Import
            </a>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> New Department
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Slug</th>
                        <th>Email</th>
                        <th>Users</th>
                        <th>Tickets</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td class="fw-bold text-dark">{{ $dept->name }}</td>
                            <td class="font-monospace small text-secondary">{{ $dept->slug }}</td>
                            <td class="small">{{ $dept->email ?? '&mdash;' }}</td>
                            <td class="small fw-semibold">{{ $dept->users_count }}</td>
                            <td class="small fw-semibold">{{ $dept->tickets_count }}</td>
                            <td>
                                <span class="badge bg-{{ $dept->is_active ? 'success' : 'secondary' }}">
                                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">No departments created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $departments->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
