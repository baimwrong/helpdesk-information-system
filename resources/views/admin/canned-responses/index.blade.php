@extends('layouts.app')

@section('title', 'Canned Responses Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-comment-dots me-2 text-primary"></i>Canned Responses</h3>
            <p class="text-secondary small mb-0">Pre-written response templates for swift, standardized agent communications.</p>
        </div>
        <a href="{{ route('admin.canned.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New Template
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Shortcut Code</th>
                        <th>Department</th>
                        <th>Created By</th>
                        <th>Content Preview</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $res)
                        <tr>
                            <td class="fw-bold text-dark">{{ $res->title }}</td>
                            <td>
                                @if($res->shortcut)
                                    <code class="text-primary">{{ $res->shortcut }}</code>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $res->department?->name ?? 'Shared (All)' }}</span>
                            </td>
                            <td class="small">{{ $res->user?->name }}</td>
                            <td class="small text-secondary text-truncate" style="max-width: 280px;">
                                {{ Str::limit($res->content, 80) }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.canned.edit', $res) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.canned.destroy', $res) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this canned response?')">
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
                            <td colspan="6" class="text-center py-4 text-secondary">No canned responses created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($responses->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $responses->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
