<?php

namespace App\Http\Controllers;

use App\Services\ArticleFeed;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(ArticleFeed $articles): Response
    {
        return Inertia::render('Home', [
            'articles' => $articles->latestForHome(),
        ]);
    }
}
