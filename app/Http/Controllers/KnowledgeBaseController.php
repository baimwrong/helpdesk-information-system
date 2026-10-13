<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    /**
     * Display the Knowledge Base portal.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $categoryId = $request->query('category');

        $query = KnowledgeBaseArticle::with(['category', 'author'])
            ->where('is_published', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $articles = $query->latest()->paginate(9)->withQueryString();
        $categories = Category::withCount(['articles' => fn ($q) => $q->where('is_published', true)])
            ->where('is_active', true)
            ->get();

        $popularArticles = KnowledgeBaseArticle::where('is_published', true)
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        return view('knowledge-base.index', compact('articles', 'categories', 'popularArticles', 'search', 'categoryId'));
    }

    /**
     * Display a specific knowledge base article.
     */
    public function show(string $slug): View
    {
        $article = KnowledgeBaseArticle::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->increment('views_count');

        $relatedArticles = KnowledgeBaseArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->take(4)
            ->get();

        return view('knowledge-base.show', compact('article', 'relatedArticles'));
    }

    /**
     * Cast vote on article helpfulness.
     */
    public function vote(Request $request, KnowledgeBaseArticle $article): RedirectResponse
    {
        $type = $request->input('type');

        if ($type === 'helpful') {
            $article->increment('helpful_count');

            return back()->with('success', 'Thank you! Glad to hear this article helped you.');
        } elseif ($type === 'not_helpful') {
            $article->increment('not_helpful_count');

            return back()->with('info', 'Thank you for your feedback. We will work on improving this guide.');
        }

        return back();
    }

    /**
     * Management index for staff.
     */
    public function manage(): View
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $articles = KnowledgeBaseArticle::with(['category', 'author'])
            ->latest()
            ->paginate(15);

        return view('knowledge-base.manage', compact('articles'));
    }

    /**
     * Show create article form.
     */
    public function create(): View
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('knowledge-base.create', compact('categories'));
    }

    /**
     * Store new knowledge base article.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
        ]);

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (KnowledgeBaseArticle::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        KnowledgeBaseArticle::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category_id' => $validated['category_id'] ?? null,
            'author_id' => $user->id,
            'content' => $validated['content'],
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('kb.manage')->with('success', 'Article published successfully.');
    }

    /**
     * Show edit article form.
     */
    public function edit(KnowledgeBaseArticle $article): View
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('knowledge-base.edit', compact('article', 'categories'));
    }

    /**
     * Update existing article.
     */
    public function update(Request $request, KnowledgeBaseArticle $article): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
        ]);

        $article->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'] ?? null,
            'content' => $validated['content'],
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('kb.manage')->with('success', 'Article updated successfully.');
    }

    /**
     * Delete an article.
     */
    public function destroy(KnowledgeBaseArticle $article): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isStaff()) {
            abort(403);
        }

        $article->delete();

        return redirect()->route('kb.manage')->with('success', 'Article deleted.');
    }
}
