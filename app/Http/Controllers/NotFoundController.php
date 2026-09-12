<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class NotFoundController extends Controller
{
    public function __invoke(): SymfonyResponse
    {
        return Inertia::render('NotFound')
            ->toResponse(request())
            ->setStatusCode(404);
    }
}
