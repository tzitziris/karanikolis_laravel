<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * The pages that always exist. They carry no lastmod: their content changes
     * when the code changes, and inventing a date would be a claim we cannot
     * back with anything in the database.
     */
    private const STATIC_ROUTES = ['home', 'coaches', 'schedule', 'news', 'about'];

    public function __invoke(): Response
    {
        // scopeReadyForPublic is the one definition of "published" on this site;
        // the archive, the home page and the article page all read it. A sitemap
        // with a rule of its own would eventually advertise a hidden article.
        $articles = Article::query()
            ->readyForPublic()
            ->publicationOrder()
            ->get(['slug', 'updated_at']);

        $entries = collect(self::STATIC_ROUTES)
            ->map(fn (string $name): array => [
                'lastmod' => $name === 'news' ? $articles->max('updated_at') : null,
                'loc' => route($name),
            ])
            ->concat($articles->map(fn (Article $article): array => [
                'lastmod' => $article->updated_at,
                'loc' => route('news.show', $article->slug),
            ]))
            ->all();

        return response()
            ->view('sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
