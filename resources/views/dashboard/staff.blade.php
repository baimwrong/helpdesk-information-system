@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Help Desk Overview</h3>
            <p class="text-secondary small mb-0">Live operations, SLA tracking, and ticket queue health.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-file-import me-1"></i> Bulk Import
            </a>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i> New Ticket
            </a>
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Total Tickets</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($totalTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Open</span>
                        <h3 class="fw-bold mb-0 mt-1 text-primary">{{ number_format($openTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-envelope-open"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">In Progress</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($inProgressTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Pending</span>
                        <h3 class="fw-bold mb-0 mt-1 text-secondary">{{ number_format($pendingTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fa-solid fa-pause"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Resolved</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($resolvedTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="stat-card border-danger border-opacity-25 bg-danger bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-danger small fw-semibold d-block text-uppercase">SLA Breached</span>
                        <h3 class="fw-bold mb-0 mt-1 text-danger">{{ number_format($breachedTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-25 text-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Ticket Priority Distribution -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Tickets by Priority</h5>
                    <span class="badge bg-light text-secondary border">Active</span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 260px;">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Workload -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Department Workload</h5>
                    <span class="badge bg-light text-secondary border">Top 5</span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 260px;">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets and Activity -->
    <div class="row g-4">
        <!-- Recent Tickets Table -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i>Recent Tickets</h5>
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Due Date</th>
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
                                        <div class="fw-semibold text-truncate" style="max-width: 200px;">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-decoration-none text-dark">
                                                {{ $ticket->subject }}
                                            </a>
                                        </div>
                                        <small class="text-secondary">{{ $ticket->user?->name }}</small>
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
                                        @if($ticket->isBreached())
                                            <span class="text-danger small fw-semibold" title="SLA Breached">
                                                <i class="fa-solid fa-circle-exclamation me-1"></i>Breached
                                            </span>
                                        @elseif($ticket->resolution_due_at)
                                            <span class="text-secondary small">
                                                {{ $ticket->resolution_due_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="text-secondary small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-secondary">
                                        <i class="fa-solid fa-inbox fs-2 mb-2 d-block text-muted"></i>
                                        No tickets found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Audit Activity -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Activity Log</h5>
                </div>
                <div class="card-body p-3">
                    <div class="timeline">
                        @forelse($recentActivities as $act)
                            <div class="d-flex align-items-start gap-2 mb-3 pb-3 border-bottom border-light">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-bolt" style="font-size: 0.75rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-dark">{{ $act->description }}</div>
                                    <div class="d-flex justify-content-between text-secondary" style="font-size: 0.75rem;">
                                        <span>Ticket #{{ $act->ticket?->ticket_number }}</span>
                                        <span>{{ $act->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary text-center small py-3">No recent activities recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Priority Chart
        const ctxPriority = document.getElementById('priorityChart').getContext('2d');
        new Chart(ctxPriority, {
            type: 'bar',
            data: {
                labels: ['Urgent', 'High', 'Medium', 'Low'],
                datasets: [{
                    label: 'Tickets',
                    data: [
                        {{ $priorityCounts['urgent'] ?? 0 }},
                        {{ $priorityCounts['high'] ?? 0 }},
                        {{ $priorityCounts['medium'] ?? 0 }},
                        {{ $priorityCounts['low'] ?? 0 }}
                    ],
                    backgroundColor: ['#ef4444', '#f97316', '#3b82f6', '#64748b'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // Department Chart
        const ctxDept = document.getElementById('departmentChart').getContext('2d');
        new Chart(ctxDept, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($departments->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($departments->pluck('tickets_count')) !!},
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endpush
@endsection
