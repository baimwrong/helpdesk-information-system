@extends('layouts.app')

@section('title', 'Knowledge Base & Self-Service Portal')

@section('content')
<div class="container-fluid">
    <!-- Knowledge Base Hero Section -->
    <div class="kb-hero mb-5 text-center">
        <h2 class="fw-bold mb-2">How can we help you today?</h2>
        <p class="text-white-50 mb-4">Search our technical documentation, how-to guides, and troubleshooting manuals.</p>

        <form action="{{ route('kb.index') }}" method="GET" class="col-md-7 mx-auto">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white border-0 text-secondary ps-3"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="q" class="form-control border-0 py-3" placeholder="Search keywords (e.g. VPN, Outlook, printer, password reset)..." value="{{ $search }}">
                <button class="btn btn-warning px-4 fw-bold" type="submit">Search Guides</button>
            </div>
        </form>
    </div>

    <!-- Categories Grid -->
    <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-folder-tree me-2 text-primary"></i>Browse by Topic</h5>
    <div class="row g-3 mb-5">
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('kb.index') }}" class="text-decoration-none">
                <div class="kb-card p-3 {{ empty($categoryId) ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold text-dark d-block">All Topics</span>
                            <small class="text-secondary">Explore all articles</small>
                        </div>
                        <i class="fa-solid fa-layer-group fs-4 text-primary"></i>
                    </div>
                </div>
            </a>
        </div>
        @foreach($categories as $cat)
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('kb.index', ['category' => $cat->id]) }}" class="text-decoration-none">
                    <div class="kb-card p-3 {{ $categoryId == $cat->id ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-dark d-block">{{ $cat->name }}</span>
                                <small class="text-secondary">{{ $cat->articles_count }} {{ Str::plural('Article', $cat->articles_count) }}</small>
                            </div>
                            <div class="rounded p-2 text-white" style="background-color: {{ $cat->color }};">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Articles List Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">
            @if($search)
                Search Results for "<span class="text-primary">{{ $search }}</span>"
            @else
                Featured Troubleshooting Articles
            @endif
        </h5>
        @if(auth()->check() && auth()->user()->isStaff())
            <a href="{{ route('kb.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-plus me-1"></i> New Article
            </a>
        @endif
    </div>

    <div class="row g-4 mb-4">
        @forelse($articles as $art)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge" style="background-color: {{ $art->category?->color ?? '#3b82f6' }}15; color: {{ $art->category?->color ?? '#3b82f6' }};">
                                {{ $art->category?->name ?? 'General' }}
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">
                            <a href="{{ route('kb.show', $art->slug) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $art->title }}
                            </a>
                        </h5>
                        <p class="text-secondary small mb-4 flex-grow-1">
                            {{ Str::limit(strip_tags($art->content), 120) }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top text-secondary small">
                            <span><i class="fa-regular fa-eye me-1"></i>{{ $art->views_count }}</span>
                            <span><i class="fa-regular fa-thumbs-up me-1 text-success"></i>{{ $art->helpful_count }}</span>
                            <a href="{{ route('kb.show', $art->slug) }}" class="text-primary fw-semibold text-decoration-none">
                                Read Guide <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-secondary">
                    <i class="fa-solid fa-magnifying-glass fs-1 mb-3 text-muted"></i>
                    <h4>No articles found</h4>
                    <p class="small text-muted mb-3">Can't find what you are looking for? Open a ticket directly with our technical support team.</p>
                    <div>
                        <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm px-4">Submit a Ticket</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($articles->hasPages())
        <div class="d-flex justify-content-center">
            {{ $articles->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
