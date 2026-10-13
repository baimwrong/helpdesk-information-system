@extends('layouts.app')

@section('title', 'Manage Knowledge Base Articles')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-file-pen me-2 text-primary"></i>Knowledge Base Management</h3>
            <p class="text-secondary small mb-0">Create, edit, and publish technical documentation guides.</p>
        </div>
        <a href="{{ route('kb.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> New Article
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Views</th>
                        <th>Helpful</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $art)
                        <tr>
                            <td>
                                <a href="{{ route('kb.show', $art->slug) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $art->title }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $art->category?->name ?? 'General' }}</span>
                            </td>
                            <td class="small">{{ $art->author?->name }}</td>
                            <td class="small">{{ number_format($art->views_count) }}</td>
                            <td class="small text-success">{{ number_format($art->helpful_count) }}</td>
                            <td>
                                <span class="badge bg-{{ $art->is_published ? 'success' : 'secondary' }}">
                                    {{ $art->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="small text-secondary">{{ $art->updated_at->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ route('kb.edit', $art) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('kb.destroy', $art) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this article?')">
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
                            <td colspan="8" class="text-center py-4 text-secondary">
                                No articles created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $articles->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
