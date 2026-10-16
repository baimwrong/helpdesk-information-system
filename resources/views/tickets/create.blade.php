@extends('layouts.app')

@section('title', 'Submit New Ticket')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Create New Support Ticket</h3>
                    <p class="text-secondary small mb-0">Submit a technical incident, service request, or inquiry to the support desk.</p>
                </div>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Tickets
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-4">
                            <!-- Department & Category -->
                            <div class="col-md-6">
                                <label for="department_id" class="form-label small fw-semibold text-secondary">Target Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label small fw-semibold text-secondary">Topic / Category</label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">-- Optional / General Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-department-id="{{ $cat->department_id ?? '' }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }} {{ $cat->department ? '(' . $cat->department->name . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority Level with SLA Hints -->
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-secondary d-block">Urgency / Priority Level <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-md-3 col-6">
                                        <div class="form-check p-3 border rounded-3 text-center h-100">
                                            <input class="form-check-input float-none mb-2" type="radio" name="priority" id="p_low" value="low" {{ old('priority') === 'low' ? 'checked' : '' }}>
                                            <label class="form-check-label d-block fw-semibold text-secondary" for="p_low">
                                                Low
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">SLA: 4 Days</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check p-3 border rounded-3 text-center h-100 border-primary bg-primary bg-opacity-10">
                                            <input class="form-check-input float-none mb-2" type="radio" name="priority" id="p_medium" value="medium" {{ old('priority', 'medium') === 'medium' ? 'checked' : '' }}>
                                            <label class="form-check-label d-block fw-semibold text-primary" for="p_medium">
                                                Medium (Standard)
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">SLA: 48 Hours</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check p-3 border rounded-3 text-center h-100 border-warning bg-warning bg-opacity-10">
                                            <input class="form-check-input float-none mb-2" type="radio" name="priority" id="p_high" value="high" {{ old('priority') === 'high' ? 'checked' : '' }}>
                                            <label class="form-check-label d-block fw-semibold text-warning" for="p_high">
                                                High
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">SLA: 12 Hours</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="form-check p-3 border rounded-3 text-center h-100 border-danger bg-danger bg-opacity-10">
                                            <input class="form-check-input float-none mb-2" type="radio" name="priority" id="p_urgent" value="urgent" {{ old('priority') === 'urgent' ? 'checked' : '' }}>
                                            <label class="form-check-label d-block fw-semibold text-danger" for="p_urgent">
                                                Urgent / Critical
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">SLA: 4 Hours</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="col-12">
                                <label for="subject" class="form-label small fw-semibold text-secondary">Subject / Issue Summary <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="subject" class="form-control form-control-lg @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required placeholder="E.g. Unable to access financial ERP software from workstation">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label small fw-semibold text-secondary">Detailed Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required placeholder="Please provide exact steps to reproduce the issue, error messages or codes, workstation asset tag, and any troubleshooting already performed.">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- File Attachments Upload -->
                            <div class="col-12">
                                <label for="attachments" class="form-label small fw-semibold text-secondary">Attachments (Screenshots, logs, documents - max 10MB each)</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,.csv">
                                <div class="form-text small">Hold Ctrl or Shift to select multiple files. Allowed types: PNG, JPG, PDF, Word, Excel, ZIP, TXT.</div>
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Submit Ticket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deptSelect = document.getElementById('department_id');
    const catSelect = document.getElementById('category_id');
    if (!deptSelect || !catSelect) return;

    function filterCategories() {
        const selectedDept = deptSelect.value;
        const options = catSelect.querySelectorAll('option');

        let hasSelectedVisible = false;
        options.forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }

            const optDept = opt.getAttribute('data-department-id');
            const match = !selectedDept || !optDept || optDept === selectedDept;
            opt.hidden = !match;
            if (match && opt.selected) {
                hasSelectedVisible = true;
            }
        });

        if (!hasSelectedVisible && catSelect.value) {
            catSelect.value = '';
        }
    }

    deptSelect.addEventListener('change', filterCategories);
    filterCategories();
});
</script>
@endpush
