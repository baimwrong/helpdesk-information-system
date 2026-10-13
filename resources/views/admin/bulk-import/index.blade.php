@extends('layouts.app')

@section('title', 'Admin Bulk Data Import Center')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-file-import me-2 text-primary"></i>Bulk Data Import Center</h3>
            <p class="text-secondary small mb-0">Rapidly onboard users, seed incident tickets, categories, and knowledge base articles via CSV.</p>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 border border-primary border-opacity-25">
            <i class="fa-solid fa-shield-halved me-1"></i> Admin Tool
        </span>
    </div>

    <!-- Import Operation Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title fw-bold mb-0">Select Data Entity to Import</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.bulk-import.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                <!-- Entity Selection Pills -->
                <div class="row g-3 mb-4">
                    <div class="col-md-2 col-6">
                        <input type="radio" class="btn-check" name="entity_type" id="ent_users" value="users" checked onchange="updateTemplateInfo('users')">
                        <label class="btn btn-outline-primary w-100 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center" for="ent_users">
                            <i class="fa-solid fa-users fs-3 mb-2"></i>
                            <span class="fw-bold d-block">Users</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Staff & Clients</small>
                        </label>
                    </div>

                    <div class="col-md-2 col-6">
                        <input type="radio" class="btn-check" name="entity_type" id="ent_tickets" value="tickets" onchange="updateTemplateInfo('tickets')">
                        <label class="btn btn-outline-primary w-100 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center" for="ent_tickets">
                            <i class="fa-solid fa-ticket-simple fs-3 mb-2"></i>
                            <span class="fw-bold d-block">Tickets</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Help Desk Records</small>
                        </label>
                    </div>

                    <div class="col-md-2 col-6">
                        <input type="radio" class="btn-check" name="entity_type" id="ent_departments" value="departments" onchange="updateTemplateInfo('departments')">
                        <label class="btn btn-outline-primary w-100 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center" for="ent_departments">
                            <i class="fa-solid fa-building-user fs-3 mb-2"></i>
                            <span class="fw-bold d-block">Departments</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Org Business Units</small>
                        </label>
                    </div>

                    <div class="col-md-2 col-6">
                        <input type="radio" class="btn-check" name="entity_type" id="ent_categories" value="categories" onchange="updateTemplateInfo('categories')">
                        <label class="btn btn-outline-primary w-100 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center" for="ent_categories">
                            <i class="fa-solid fa-tags fs-3 mb-2"></i>
                            <span class="fw-bold d-block">Categories</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Ticket Classifications</small>
                        </label>
                    </div>

                    <div class="col-md-2 col-6">
                        <input type="radio" class="btn-check" name="entity_type" id="ent_articles" value="articles" onchange="updateTemplateInfo('articles')">
                        <label class="btn btn-outline-primary w-100 p-3 text-center h-100 d-flex flex-column align-items-center justify-content-center" for="ent_articles">
                            <i class="fa-solid fa-book fs-3 mb-2"></i>
                            <span class="fw-bold d-block">Knowledge Base</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Articles & FAQs</small>
                        </label>
                    </div>
                </div>

                <!-- Template Download Card / Instructions -->
                <div class="alert alert-light border p-3 rounded-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="fw-bold text-dark d-block" id="templateTitle">Users CSV Structure</span>
                        <small class="text-secondary" id="templateColumns">Required columns: <code>name, email, role, department, phone, password</code></small>
                    </div>
                    <a href="{{ route('admin.bulk-import.template', 'users') }}" class="btn btn-outline-success btn-sm px-3" id="btnDownloadTemplate">
                        <i class="fa-solid fa-file-csv me-1"></i> Download Sample CSV Template
                    </a>
                </div>

                <!-- CSV File Upload -->
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="csv_file" class="form-label small fw-semibold text-secondary">Choose CSV File (Max 20MB)</label>
                        <input type="file" name="csv_file" id="csv_file" class="form-control form-control-lg @error('csv_file') is-invalid @enderror" accept=".csv,.txt" required>
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            <i class="fa-solid fa-upload me-2"></i>Start Bulk Import
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Audit Trail History -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Bulk Import Execution History</h5>
            <span class="badge bg-light text-secondary border">Total {{ $logs->total() }} Jobs</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Admin User</th>
                        <th>Entity</th>
                        <th>File Name</th>
                        <th>Total Rows</th>
                        <th>Success</th>
                        <th>Failed</th>
                        <th>Status</th>
                        <th>Error Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="small text-secondary font-monospace">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <span class="fw-semibold small text-dark">{{ $log->user?->name ?? 'System' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $log->entity_type }}</span>
                            </td>
                            <td class="small font-monospace text-truncate" style="max-width: 150px;">{{ $log->file_name }}</td>
                            <td class="fw-bold text-dark">{{ number_format($log->total_rows) }}</td>
                            <td class="fw-bold text-success">{{ number_format($log->successful_rows) }}</td>
                            <td class="fw-bold text-{{ $log->failed_rows > 0 ? 'danger' : 'secondary' }}">
                                {{ number_format($log->failed_rows) }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->status === 'completed' ? 'success' : ($log->status === 'partial' ? 'warning' : 'danger') }} text-uppercase">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($log->errors))
                                    <button class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#errorModal{{ $log->id }}">
                                        View {{ count($log->errors) }} Errors
                                    </button>

                                    <!-- Error Modal -->
                                    <div class="modal fade" id="errorModal{{ $log->id }}" tabindex="-1" aria-labelledby="errorModalLabel{{ $log->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h6 class="modal-title" id="errorModalLabel{{ $log->id }}">
                                                        Import Errors - {{ $log->file_name }}
                                                    </h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <ul class="list-group list-group-flush small">
                                                        @foreach($log->errors as $err)
                                                            <li class="list-group-item text-danger py-2">
                                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $err }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-secondary">
                                <i class="fa-solid fa-file-excel fs-2 mb-2 d-block text-muted"></i>
                                No bulk import jobs executed yet. Select an entity above and upload a CSV to get started!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<script>
    const templates = {
        users: {
            title: "Users CSV Structure",
            columns: "Required columns: <code>name, email, role, department, phone, password</code>",
            url: "{{ route('admin.bulk-import.template', 'users') }}"
        },
        tickets: {
            title: "Tickets CSV Structure",
            columns: "Required columns: <code>customer_email, subject, description, department, category, priority, status, assigned_to_email</code>",
            url: "{{ route('admin.bulk-import.template', 'tickets') }}"
        },
        departments: {
            title: "Departments CSV Structure",
            columns: "Required columns: <code>name, slug, email, description</code>",
            url: "{{ route('admin.bulk-import.template', 'departments') }}"
        },
        categories: {
            title: "Categories CSV Structure",
            columns: "Required columns: <code>name, slug, department, color, description</code>",
            url: "{{ route('admin.bulk-import.template', 'categories') }}"
        },
        articles: {
            title: "Knowledge Base Articles CSV Structure",
            columns: "Required columns: <code>title, slug, category, author_email, content</code>",
            url: "{{ route('admin.bulk-import.template', 'articles') }}"
        }
    };

    function updateTemplateInfo(type) {
        const info = templates[type];
        document.getElementById('templateTitle').innerText = info.title;
        document.getElementById('templateColumns').innerHTML = info.columns;
        document.getElementById('btnDownloadTemplate').setAttribute('href', info.url);
    }
</script>
@endsection
