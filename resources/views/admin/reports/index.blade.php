@extends('layouts.app')

@section('title', 'Analytics & SLA Performance Reports')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Service Performance & SLA Analytics</h3>
            <p class="text-secondary small mb-0">Monitor SLA compliance, ticket turnaround times, and agent customer satisfaction ratings.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <!-- Time Filter Form -->
            <form action="{{ route('admin.reports.index') }}" method="GET" class="d-flex align-items-center gap-1">
                <select name="range" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7" {{ $range === '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $range === '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $range === '90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="all" {{ $range === 'all' ? 'selected' : '' }}>All Time</option>
                </select>
            </form>

            <a href="{{ route('admin.reports.export') }}" class="btn btn-outline-success btn-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Export Tickets CSV
            </a>
        </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Total In Period</span>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($totalTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Resolved / Closed</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($resolvedTickets + $closedTickets) }}</h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">SLA Compliance Rate</span>
                        <h3 class="fw-bold mb-0 mt-1 {{ $slaComplianceRate >= 90 ? 'text-success' : 'text-danger' }}">
                            {{ $slaComplianceRate }}%
                        </h3>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary small fw-semibold d-block text-uppercase">Average CSAT Rating</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning">
                            {{ number_format($avgCsat, 1) }} / 5.0
                        </h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Performance Scorecard -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-user-check me-2 text-primary"></i>Staff & Agent Performance Scorecard</h5>
            <span class="badge bg-light text-secondary border">Active Engineers</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th class="text-center">Assigned Tickets</th>
                        <th class="text-center">Resolved Tickets</th>
                        <th class="text-center">Resolution Rate</th>
                        <th class="text-center">Average CSAT Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $agent)
                        @php
                            $rate = $agent['assigned_count'] > 0 ? round(($agent['resolved_count'] / $agent['assigned_count']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">
                                <i class="fa-solid fa-circle-user text-primary me-2"></i>{{ $agent['name'] }}
                            </td>
                            <td class="small text-secondary">{{ $agent['email'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $agent['department'] }}</span></td>
                            <td class="text-center fw-semibold">{{ $agent['assigned_count'] }}</td>
                            <td class="text-center fw-semibold text-success">{{ $agent['resolved_count'] }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 80px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($rate, 100) }}%"></div>
                                    </div>
                                    <small class="fw-bold">{{ $rate }}%</small>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($agent['avg_rating'] > 0)
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">
                                        <i class="fa-solid fa-star text-warning me-1"></i>{{ $agent['avg_rating'] }} / 5
                                    </span>
                                @else
                                    <span class="text-muted small">No ratings yet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-secondary">No agents recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Department Distribution Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-building-user me-2 text-primary"></i>Department Workload Breakdown</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Slug</th>
                        <th class="text-center">Tickets in Period</th>
                        <th class="text-center">Workload Share</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departmentStats as $ds)
                        @php
                            $share = $totalTickets > 0 ? round(($ds->tickets_count / $totalTickets) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark">{{ $ds->name }}</td>
                            <td class="small font-monospace text-secondary">{{ $ds->slug }}</td>
                            <td class="text-center fw-bold">{{ $ds->tickets_count }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 120px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $share }}%"></div>
                                    </div>
                                    <small class="fw-bold">{{ $share }}%</small>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
