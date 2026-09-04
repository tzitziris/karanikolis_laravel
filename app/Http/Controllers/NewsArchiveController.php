<?php

namespace App\Http\Controllers;

use App\Services\ArticleFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsArchiveController extends Controller
{
    public function __invoke(Request $request, ArticleFeed $articles): Response|RedirectResponse
    {
        $requestedPage = max(1, (int) $request->query('page', 1));
        $archive = $articles->archivePage($requestedPage);

        if ($archive->total() === 0 && $requestedPage > 1) {
            return redirect()->route('news');
        }

        if ($archive->total() > 0 && $requestedPage > $archive->lastPage()) {
            return redirect()->route('news', ['page' => $archive->lastPage()]);
        }

        return Inertia::render('News', [
            'articles' => $archive->items(),
            'pagination' => [
                'currentPage' => $archive->currentPage(),
                'from' => $archive->firstItem(),
                'lastPage' => $archive->lastPage(),
                'links' => $archive->linkCollection()->toArray(),
                'nextPageUrl' => $archive->nextPageUrl(),
                'perPage' => $archive->perPage(),
                'previousPageUrl' => $archive->previousPageUrl(),
                'to' => $archive->lastItem(),
                'total' => $archive->total(),
            ],
        ]);
    }
}
