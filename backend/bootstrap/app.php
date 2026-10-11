<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $exception, \Illuminate\Http\Request $request) {
            if (! $request->is('api/*')) return null;
            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException || $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) return response()->json(['success' => false, 'message' => 'Resource not found.'], 404);
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
            if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $exception->getStatusCode() === 403) return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
            return null;
        });
    })->create();
