<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RememberVisitedCampaign;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'active' => EnsureAccountIsActive::class,
            'remember.campaign' => RememberVisitedCampaign::class,
        ]);

        // Keep the intended-URL redirect working for guests hitting a
        // protected page: they land back where they were going after login.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Render the branded error pages for the status codes we designed,
         * while keeping Laravel's debug screen available in local development.
         */
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();

            if (! in_array($status, [403, 404, 419, 429, 500, 503], true)) {
                return null;
            }

            if (! view()->exists("errors.{$status}")) {
                return null;
            }

            return response()->view("errors.{$status}", [
                'exception' => $e,
            ], $status);
        });
    })->create();
