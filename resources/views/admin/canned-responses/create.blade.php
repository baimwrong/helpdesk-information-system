@extends('layouts.app')

@section('title', 'Create Canned Response')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Create Canned Response</h3>
                <a href="{{ route('admin.canned.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Templates
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.canned.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label small fw-semibold text-secondary">Template Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="E.g. VPN Cache Clear Instructions">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="shortcut" class="form-label small fw-semibold text-secondary">Shortcut Code</label>
                                <input type="text" name="shortcut" id="shortcut" class="form-control" value="{{ old('shortcut') }}" placeholder="!vpn-reset">
                                <small class="text-muted" style="font-size: 0.75rem;">Prefix with exclamation mark, e.g. !ack</small>
                            </div>

                            <div class="col-md-6">
                                <label for="department_id" class="form-label small fw-semibold text-secondary">Target Department</label>
                                <select name="department_id" id="department_id" class="form-select">
                                    <option value="">-- Shared with All Staff --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label small fw-semibold text-secondary">Message Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="6" class="form-control @error('content') is-invalid @enderror" required placeholder="Type the standardized message template here...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.canned.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
