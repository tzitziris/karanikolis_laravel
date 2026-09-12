<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\AdminArticleService;
use Illuminate\Http\RedirectResponse;

class AdminArticlePublicationController extends Controller
{
    public function publish(Article $article, AdminArticleService $articles): RedirectResponse
    {
        $articles->publish($article);

        return redirect()->route('admin.home')->with('success', "Το άρθρο «{$article->title}» δημοσιεύτηκε.");
    }

    public function unpublish(Article $article, AdminArticleService $articles): RedirectResponse
    {
        $articles->unpublish($article);

        return redirect()->route('admin.home')->with('success', "Το άρθρο «{$article->title}» αποσύρθηκε από τη δημόσια σελίδα.");
    }
}
