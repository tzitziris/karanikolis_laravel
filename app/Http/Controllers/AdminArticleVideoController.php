<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleVideoOrderRequest;
use App\Http\Requests\ArticleVideoRequest;
use App\Models\Article;
use App\Models\ArticleVideo;
use App\Services\AdminArticleService;
use Illuminate\Http\RedirectResponse;

class AdminArticleVideoController extends Controller
{
    public function store(ArticleVideoRequest $request, Article $article, AdminArticleService $articles): RedirectResponse
    {
        $articles->addVideo($article, (string) $request->validated('youtube_url'));

        return back()->with('success', 'Το βίντεο προστέθηκε στο άρθρο.');
    }

    public function update(ArticleVideoOrderRequest $request, Article $article, AdminArticleService $articles): RedirectResponse
    {
        $articles->saveVideoOrder($article, $request->validated('videos'));

        return back()->with('success', 'Η σειρά των βίντεο αποθηκεύτηκε.');
    }

    public function destroy(Article $article, ArticleVideo $video, AdminArticleService $articles): RedirectResponse
    {
        abort_unless($video->article_id === $article->id, 404);

        $articles->deleteVideo($video);

        return back()->with('success', 'Το βίντεο αφαιρέθηκε από το άρθρο.');
    }
}
