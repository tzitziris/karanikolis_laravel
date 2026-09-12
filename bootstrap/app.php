<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if ($request->is('admin/articles/*/cover') || $request->is('admin/articles/*/gallery')) {
                return response(
                    '<!doctype html><html lang="el"><meta charset="utf-8"><title>Η φωτογραφία είναι πολύ μεγάλη</title><body>Η φωτογραφία είναι μεγαλύτερη από όσο δέχεται ο server. Ανεβάστε μικρότερη φωτογραφία.</body></html>',
                    413,
                );
            }

            return null;
        });
    })->create();
