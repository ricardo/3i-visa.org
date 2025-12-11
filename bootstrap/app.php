<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        // Exclude Stripe webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Update exchange rates daily at midnight
        $schedule->command('currency:update')->dailyAt('00:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
