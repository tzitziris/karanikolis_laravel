<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleContentRequest;
use App\Models\Article;
use App\Support\ArticleBodyContract;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminArticleContentController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Admin/ArticleForm', [
            'article' => null,
            'bodyContract' => $this->bodyContract(),
            'mode' => 'create',
        ]);
    }

    public function store(ArticleContentRequest $request): RedirectResponse
    {
        $article = Article::create([
            'body' => $request->validated('body'),
            'excerpt' => trim((string) $request->validated('excerpt')),
            'is_visible' => false,
            'published_at' => $request->date('published_at'),
            'title' => trim((string) $request->validated('title')),
        ]);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Το άρθρο δημιουργήθηκε ως προσχέδιο.');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('Admin/ArticleForm', [
            'article' => $this->articleData($article),
            'bodyContract' => $this->bodyContract(),
            'mode' => 'edit',
        ]);
    }

    public function update(ArticleContentRequest $request, Article $article): RedirectResponse
    {
        $article->update([
            'body' => $request->validated('body'),
            'excerpt' => trim((string) $request->validated('excerpt')),
            'published_at' => $request->date('published_at'),
            'title' => trim((string) $request->validated('title')),
        ]);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('success', 'Το άρθρο αποθηκεύτηκε.');
    }

    /**
     * @return array<string, mixed>
     */
    private function articleData(Article $article): array
    {
        return [
            'body' => $article->body,
            'excerpt' => $article->excerpt,
            'id' => $article->id,
            'isVisible' => $article->is_visible,
            'publishedAt' => $article->published_at?->format('Y-m-d\TH:i'),
            'slug' => $article->slug,
            'title' => $article->title,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bodyContract(): array
    {
        return [
            'alignments' => ArticleBodyContract::alignments(),
            'headingLevels' => ArticleBodyContract::headingLevels(),
            'marks' => ArticleBodyContract::editableMarks(),
            'nodes' => ArticleBodyContract::editableNodes(),
        ];
    }
}
