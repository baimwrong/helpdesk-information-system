@extends('layouts.app')

@section('title', $article->title)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumbs -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="{{ route('kb.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to KB
                </a>
                <span class="text-muted">/</span>
                <span class="badge bg-light text-dark border">{{ $article->category?->name ?? 'General' }}</span>
            </div>

            <!-- Article Content Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="fw-bold text-dark mb-3">{{ $article->title }}</h2>

                    <div class="d-flex align-items-center gap-3 text-secondary small pb-3 mb-4 border-bottom">
                        <span><i class="fa-regular fa-user me-1"></i>{{ $article->author?->name ?? 'Help Desk Staff' }}</span>
                        <span><i class="fa-regular fa-calendar me-1"></i>{{ $article->updated_at->format('M d, Y') }}</span>
                        <span><i class="fa-regular fa-eye me-1"></i>{{ $article->views_count }} views</span>
                    </div>

                    <div class="article-body lh-lg text-dark mb-5" style="white-space: pre-line;">
                        {!! nl2br(e($article->content)) !!}
                    </div>

                    <!-- Helpfulness Voting Box -->
                    <div class="card bg-light border-0 p-4 rounded-3 text-center mb-4">
                        <h6 class="fw-bold mb-2">Was this guide helpful?</h6>
                        <p class="text-secondary small mb-3">{{ $article->helpful_count }} people found this helpful.</p>
                        <form action="{{ route('kb.vote', $article) }}" method="POST" class="d-inline-flex gap-2">
                            @csrf
                            <button type="submit" name="type" value="helpful" class="btn btn-sm btn-outline-success px-3">
                                <i class="fa-solid fa-thumbs-up me-1"></i> Yes, thank you
                            </button>
                            <button type="submit" name="type" value="not_helpful" class="btn btn-sm btn-outline-secondary px-3">
                                <i class="fa-solid fa-thumbs-down me-1"></i> No, I still need help
                            </button>
                        </form>
                    </div>

                    <!-- Still need help card -->
                    <div class="alert alert-info border-0 rounded-3 d-flex justify-content-between align-items-center p-3 mb-0">
                        <div>
                            <span class="fw-bold text-dark d-block">Still experiencing this issue?</span>
                            <small class="text-secondary">Open a ticket and an engineer will investigate your case directly.</small>
                        </div>
                        <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus me-1"></i> Submit Ticket
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Articles -->
            @if($relatedArticles->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3 text-dark">Related Guides in {{ $article->category?->name }}</h5>
                    <div class="list-group list-group-flush">
                        @foreach($relatedArticles as $rel)
                            <a href="{{ route('kb.show', $rel->slug) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-0 border-0">
                                <span class="text-primary fw-semibold"><i class="fa-regular fa-file-lines me-2"></i>{{ $rel->title }}</span>
                                <span class="text-muted small"><i class="fa-regular fa-eye me-1"></i>{{ $rel->views_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
