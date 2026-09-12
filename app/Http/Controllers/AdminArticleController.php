<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\AdminArticleService;
use Illuminate\Http\RedirectResponse;

class AdminArticleController extends Controller
{
    public function destroy(Article $article, AdminArticleService $articles): RedirectResponse
    {
        $title = $article->title;

        $articles->delete($article);

        return redirect()->route('admin.home')->with('success', "Το άρθρο «{$title}» διαγράφηκε οριστικά.");
    }
}
