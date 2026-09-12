<?php

namespace App\Http\Controllers;

use App\Services\AdminArticleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, AdminArticleService $articles): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'articles' => $articles->allForDashboard(),
            'user' => $request->user()->only('id', 'email'),
        ]);
    }
}
