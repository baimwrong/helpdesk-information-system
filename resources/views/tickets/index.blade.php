@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-ticket-simple me-2 text-primary"></i>Tickets Directory</h3>
            <p class="text-secondary small mb-0">Browse, filter, and track technical support requests.</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> New Ticket
        </a>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('tickets.index') }}" method="GET" class="row g-2 align-items-center">
                @if(request()->query('tab'))
                    <input type="hidden" name="tab" value="{{ request()->query('tab') }}">
                @endif

                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search ticket #, subject..." value="{{ request()->query('search') }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="open" {{ request()->query('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request()->query('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="pending" {{ request()->query('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="resolved" {{ request()->query('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request()->query('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Priorities --</option>
                        <option value="urgent" {{ request()->query('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request()->query('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request()->query('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request()->query('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Departments --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request()->query('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request()->query('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-1 col-md-4 col-sm-12 text-end">
                    <a href="{{ route('tickets.index', ['tab' => request()->query('tab')]) }}" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs border-bottom-0 mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'all' ? 'active fw-bold' : '' }}" href="{{ route('tickets.index', array_merge(request()->except('page'), ['tab' => 'all'])) }}">
                <i class="fa-solid fa-list me-1"></i> All Tickets
            </a>
        </li>
        @if(auth()->user()->isStaff())
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'my_assigned' ? 'active fw-bold' : '' }}" href="{{ route('tickets.index', array_merge(request()->except('page'), ['tab' => 'my_assigned'])) }}">
                    <i class="fa-solid fa-user-check me-1"></i> My Assigned
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'unassigned' ? 'active fw-bold' : '' }}" href="{{ route('tickets.index', array_merge(request()->except('page'), ['tab' => 'unassigned'])) }}">
                    <i class="fa-solid fa-inbox me-1"></i> Queue / Unassigned
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger {{ $tab === 'breached' ? 'active fw-bold' : '' }}" href="{{ route('tickets.index', array_merge(request()->except('page'), ['tab' => 'breached'])) }}">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> SLA Breached
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'resolved' ? 'active fw-bold' : '' }}" href="{{ route('tickets.index', array_merge(request()->except('page'), ['tab' => 'resolved'])) }}">
                <i class="fa-solid fa-check-double me-1"></i> Resolved
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'closed' ? 'active fw-bold' : '' }}" href="{{ route('tickets.index', array_merge(request()->except('page'), ['tab' => 'closed'])) }}">
                <i class="fa-solid fa-archive me-1"></i> Closed
            </a>
        </li>
    </ul>

    <!-- Tickets Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 130px;">Ticket #</th>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>SLA Due</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="fw-bold font-monospace small">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none text-primary">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none text-dark">
                                        {{ $ticket->subject }}
                                    </a>
                                </div>
                                @if($ticket->category)
                                    <span class="badge" style="background-color: {{ $ticket->category->color }}15; color: {{ $ticket->category->color }}; font-size: 0.7rem;">
                                        {{ $ticket->category->name }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $ticket->user?->name }}</div>
                                <div class="text-secondary" style="font-size: 0.75rem;">{{ $ticket->user?->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $ticket->department?->name ?? 'General' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-priority-{{ $ticket->priority }} text-uppercase">
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-status-{{ $ticket->status }} text-uppercase">
                                    {{ str_replace('_', ' ', $ticket->status) }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->assignedTo)
                                    <span class="small text-dark fw-semibold">
                                        <i class="fa-regular fa-circle-user me-1 text-primary"></i>{{ $ticket->assignedTo->name }}
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($ticket->isBreached())
                                    <span class="badge bg-danger text-white">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Breached
                                    </span>
                                @elseif($ticket->resolution_due_at)
                                    <span class="text-secondary small">
                                        {{ $ticket->resolution_due_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-secondary">
                                <i class="fa-solid fa-inbox fs-1 mb-2 d-block text-muted"></i>
                                <h5>No tickets found</h5>
                                <p class="small text-muted mb-3">Try adjusting your filters or search keywords.</p>
                                <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-primary">Create New Ticket</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $tickets->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
