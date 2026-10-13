@extends('layouts.app')

@section('title', 'Edit SLA Policy')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit SLA Policy: {{ $sla->name }}</h3>
                <a href="{{ route('admin.sla.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to SLA
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.sla.update', $sla) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-secondary">Policy Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $sla->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label small fw-semibold text-secondary">Priority Tier <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="urgent" {{ old('priority', $sla->priority) === 'urgent' ? 'selected' : '' }}>Urgent / Critical</option>
                                <option value="high" {{ old('priority', $sla->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ old('priority', $sla->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ old('priority', $sla->priority) === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="response_time_hours" class="form-label small fw-semibold text-secondary">First Response Hours <span class="text-danger">*</span></label>
                                <input type="number" name="response_time_hours" id="response_time_hours" class="form-control @error('response_time_hours') is-invalid @enderror" value="{{ old('response_time_hours', $sla->response_time_hours) }}" min="1" required>
                                @error('response_time_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-6">
                                <label for="resolution_time_hours" class="form-label small fw-semibold text-secondary">Resolution Due Hours <span class="text-danger">*</span></label>
                                <input type="number" name="resolution_time_hours" id="resolution_time_hours" class="form-control @error('resolution_time_hours') is-invalid @enderror" value="{{ old('resolution_time_hours', $sla->resolution_time_hours) }}" min="1" required>
                                @error('resolution_time_hours')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold text-secondary">Policy Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $sla->description) }}</textarea>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $sla->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="is_active">
                                Active status
                            </label>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.sla.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update SLA Policy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
