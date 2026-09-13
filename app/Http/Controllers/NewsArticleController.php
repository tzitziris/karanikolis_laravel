<?php

namespace App\Http\Controllers;

use App\Services\ArticleBodyRenderer;
use App\Services\ArticleFeed;
use App\Services\PageMetadata;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class NewsArticleController extends Controller
{
    public function __invoke(
        string $slug,
        Request $request,
        ArticleFeed $articles,
        ArticleBodyRenderer $renderer,
        PageMetadata $metadata,
        NotFoundController $notFound,
    ): Response|SymfonyResponse {
        $article = $articles->articlePage($slug, $renderer);

        if ($article === null) {
            return $notFound();
        }

        return Inertia::render('Article', [
            'article' => $article,
            'meta' => $metadata->forArticle($article, $request),
        ]);
    }
}
