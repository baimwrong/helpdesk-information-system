@extends('layouts.app')

@section('title', 'Edit Article')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Knowledge Base Article</h3>
                <a href="{{ route('kb.manage') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Management
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('kb.update', $article) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label small fw-semibold text-secondary">Article Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control form-control-lg @error('title') is-invalid @enderror" value="{{ old('title', $article->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label small fw-semibold text-secondary">Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">-- General / No Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label small fw-semibold text-secondary">Article Body & Guide Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" rows="12" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $article->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" id="is_published" class="form-check-input" value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="is_published">
                                Published & visible to users
                            </label>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('kb.manage') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Update Article
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
