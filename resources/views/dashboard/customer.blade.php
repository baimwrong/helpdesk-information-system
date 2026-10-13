@extends('layouts.app')

@section('title', 'Customer Portal')

@section('content')
<div class="container-fluid">
    <!-- Welcome Hero -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white p-4" style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-2">Hello, {{ auth()->user()->name }}!</h3>
                <p class="mb-0 text-white-50">Welcome to your Internal Help Desk Portal. How can our technical support team help you today?</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('tickets.create') }}" class="btn btn-light text-primary fw-bold shadow-sm px-4 py-2">
                    <i class="fa-solid fa-plus-circle me-2"></i>Create New Ticket
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">My Total Tickets</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($totalMyTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Active / In Progress</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($openMyTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Resolved / Closed</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($resolvedMyTickets + $closedMyTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My Recent Tickets -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>My Recent Tickets</h5>
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTickets as $ticket)
                                <tr>
                                    <td class="fw-bold font-monospace small">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none text-primary">
                                            {{ $ticket->ticket_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-truncate" style="max-width: 250px;">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none text-dark">
                                                {{ $ticket->subject }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $ticket->department?->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-{{ $ticket->status }} text-uppercase">
                                            {{ str_replace('_', ' ', $ticket->status) }}
                                        </span>
                                    </td>
                                    <td class="text-secondary small">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-secondary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">
                                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block text-muted"></i>
                                        You haven't submitted any tickets yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Self Service Guides -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-lightbulb me-2 text-warning"></i>Popular Guides</h5>
                    <a href="{{ route('kb.index') }}" class="btn btn-sm btn-link text-decoration-none">Search KB</a>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        @forelse($popularArticles as $article)
                            <a href="{{ route('kb.show', $article->slug) }}" class="list-group-item list-group-item-action border-0 px-2 py-3 rounded-2 mb-1">
                                <div class="fw-semibold text-primary mb-1">{{ $article->title }}</div>
                                <div class="d-flex justify-content-between text-secondary" style="font-size: 0.75rem;">
                                    <span><i class="fa-solid fa-tag me-1"></i>{{ $article->category?->name ?? 'General' }}</span>
                                    <span><i class="fa-regular fa-eye me-1"></i>{{ $article->views_count }} views</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-secondary small py-2 mb-0">No articles available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
