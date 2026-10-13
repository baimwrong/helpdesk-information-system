@extends('layouts.app')

@section('title', 'Edit Department')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Department: {{ $department->name }}</h3>
                <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Departments
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.departments.update', $department) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-secondary">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Slug Identifier</label>
                            <input type="text" class="form-control bg-light font-monospace" value="{{ $department->slug }}" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Department Contact Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $department->email) }}">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold text-secondary">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $department->description) }}</textarea>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="is_active">
                                Active status
                            </label>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Department</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
