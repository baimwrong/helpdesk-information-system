@extends('layouts.app')

@section('title', 'Categories Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-tags me-2 text-primary"></i>Ticket Categories</h3>
            <p class="text-secondary small mb-0">Classify incoming incidents, inquiries, and request types.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-file-import me-1"></i> Bulk Import
            </a>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> New Category
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Color</th>
                        <th>Department</th>
                        <th>Tickets</th>
                        <th>KB Articles</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle" style="width: 12px; height: 12px; background-color: {{ $cat->color }};"></div>
                                    <span class="fw-bold text-dark">{{ $cat->name }}</span>
                                </div>
                                <small class="text-secondary font-monospace" style="font-size: 0.75rem;">{{ $cat->slug }}</small>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }};">
                                    {{ $cat->color }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $cat->department?->name ?? 'All Departments' }}</span>
                            </td>
                            <td class="small fw-semibold">{{ $cat->tickets_count }}</td>
                            <td class="small fw-semibold">{{ $cat->articles_count }}</td>
                            <td>
                                <span class="badge bg-{{ $cat->is_active ? 'success' : 'secondary' }}">
                                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
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
                            <td colspan="7" class="text-center py-4 text-secondary">No categories created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
