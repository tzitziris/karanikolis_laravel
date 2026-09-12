<?php

namespace App\Http\Controllers;

use App\Exceptions\ArticleImageUploadException;
use App\Http\Requests\ArticleGalleryOrderRequest;
use App\Http\Requests\ArticleImageUploadRequest;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Services\AdminArticleService;
use Illuminate\Http\RedirectResponse;

class AdminArticleImageController extends Controller
{
    public function storeCover(ArticleImageUploadRequest $request, Article $article, AdminArticleService $articles): RedirectResponse
    {
        try {
            $articles->replaceCover($article, $request->file('photo'), $request->validated('alt_text'));
        } catch (ArticleImageUploadException $exception) {
            return back()->withErrors(['cover_photo' => $exception->getMessage()]);
        }

        return back()->with('success', 'Η φωτογραφία εξωφύλλου αποθηκεύτηκε.');
    }

    public function destroyCover(Article $article, AdminArticleService $articles): RedirectResponse
    {
        $articles->removeCover($article);

        return back()->with('success', 'Η φωτογραφία εξωφύλλου αφαιρέθηκε.');
    }

    public function storeGallery(ArticleImageUploadRequest $request, Article $article, AdminArticleService $articles): RedirectResponse
    {
        try {
            $articles->addGalleryImage($article, $request->file('photo'), $request->validated('alt_text'));
        } catch (ArticleImageUploadException $exception) {
            return back()->withErrors(['gallery_photo' => $exception->getMessage()]);
        }

        return back()->with('success', 'Η φωτογραφία προστέθηκε στη συλλογή.');
    }

    public function updateGallery(ArticleGalleryOrderRequest $request, Article $article, AdminArticleService $articles): RedirectResponse
    {
        $articles->saveGalleryOrder($article, $request->validated('images'));

        return back()->with('success', 'Η σειρά των φωτογραφιών αποθηκεύτηκε.');
    }

    public function destroyGallery(Article $article, ArticleImage $image, AdminArticleService $articles): RedirectResponse
    {
        abort_unless($image->article_id === $article->id, 404);

        $articles->deleteGalleryImage($image);

        return back()->with('success', 'Η φωτογραφία αφαιρέθηκε από τη συλλογή.');
    }
}
