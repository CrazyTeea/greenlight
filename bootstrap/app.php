<?php

use App\Exceptions\Domain\DomainException;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        // доменные ошибки не должны превращаться в 500
        // на вебе показываем тостом, на api отдаём нормальный код
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->userMessage()], $e->status());
            }

            return back()->with('error', $e->userMessage());
        });

        // ларавел заворачивает AuthorizationException в AccessDeniedHttpException
        // ещё до наших рендереров, поэтому ловим уже http-исключение
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Недостаточно прав.'], 403);
            }

            // post возвращаем назад с ошибкой, get оставляем 403
            return $request->isMethod('GET')
                ? null
                : back()->with('error', 'Недостаточно прав для этого действия.');
        });
    })->create();
