@extends('layouts.app')

@section('title', 'SLA Policies Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-clock me-2 text-primary"></i>Service Level Agreements (SLA)</h3>
            <p class="text-secondary small mb-0">Define maximum response and resolution targets per incident priority tier.</p>
        </div>
        <a href="{{ route('admin.sla.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> New SLA Policy
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Policy Name</th>
                        <th>Priority Tier</th>
                        <th>First Response Target</th>
                        <th>Resolution Target</th>
                        <th>Assigned Tickets</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td class="fw-bold text-dark">{{ $plan->name }}</td>
                            <td>
                                <span class="badge badge-priority-{{ $plan->priority }} text-uppercase">
                                    {{ $plan->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border fw-semibold">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $plan->response_time_hours }} Hours
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-danger border fw-semibold">
                                    <i class="fa-solid fa-stopwatch me-1"></i>{{ $plan->resolution_time_hours }} Hours
                                </span>
                            </td>
                            <td class="small fw-semibold">{{ $plan->tickets_count }}</td>
                            <td>
                                <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.sla.edit', $plan) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('admin.sla.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this SLA policy?')">
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
                            <td colspan="7" class="text-center py-4 text-secondary">No SLA policies created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($plans->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $plans->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
