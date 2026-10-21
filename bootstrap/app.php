<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);

foreach ([
    $basePath.'/storage/framework/cache',
    $basePath.'/storage/framework/sessions',
    $basePath.'/storage/framework/views',
    $basePath.'/storage/logs',
    $basePath.'/bootstrap/cache',
] as $runtimeDirectory) {
    if (! is_dir($runtimeDirectory)) {
        mkdir($runtimeDirectory, 0755, true);
    }
}

/*
 * The installer must be reachable before a production .env exists. Laravel's
 * encrypted cookies are processed before the installer controller, so provide
 * a key early and persist it when the environment file is writable.
 */
$environmentPath = $basePath.'/.env';
$environment = is_file($environmentPath) ? file_get_contents($environmentPath) : false;
$environmentKey = is_string($environment)
    && preg_match('/^APP_KEY=(.*)$/m', $environment, $matches) === 1
    ? trim($matches[1], " \t\"'")
    : '';
$hasApplicationKey = $environmentKey !== '';

if (! $hasApplicationKey && ! getenv('APP_KEY')) {
    $applicationKey = 'base64:'.base64_encode(random_bytes(32));

    if (is_string($environment)) {
        $updatedEnvironment = preg_match('/^APP_KEY=.*$/m', $environment) === 1
            ? preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$applicationKey, $environment)
            : $environment.PHP_EOL.'APP_KEY='.$applicationKey.PHP_EOL;

        if (is_string($updatedEnvironment) && is_writable($environmentPath)) {
            file_put_contents($environmentPath, $updatedEnvironment, LOCK_EX);
        }
    }

    putenv('APP_KEY='.$applicationKey);
    $_ENV['APP_KEY'] = $applicationKey;
    $_SERVER['APP_KEY'] = $applicationKey;
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // No EnsureFrontendRequestsAreStateful here on purpose: that
        // middleware is for first-party SPA cookie-based auth sharing a
        // session with the API. This project is pure Bearer-token API
        // auth throughout (mobile app + admin panel) — nothing configures
        // SANCTUM_STATEFUL_DOMAINS or uses cookies for auth anywhere.
        // Including it made Sanctum's guard check session-based 'web'
        // auth before the bearer token on every request, which is both
        // unnecessary and was implicated in a cross-user auth resolution
        // bug caught in Phase 8 testing.
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Consistent API error envelope: {success, message, data, errors}
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            // Most Laravel exceptions do NOT implement getStatusCode() —
            // notably ValidationException (422), AuthenticationException
            // (401), and AuthorizationException (403) — so status has to
            // be resolved per exception type rather than assumed from a
            // method that most exceptions don't have. Getting this wrong
            // silently turns every validation/auth failure into a 500,
            // which is exactly the kind of bug that's invisible until
            // something actually runs the test suite.
            $status = match (true) {
                $e instanceof \Illuminate\Validation\ValidationException => $e->status,
                $e instanceof \Illuminate\Auth\AuthenticationException => 401,
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                $e instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException => 404,
                method_exists($e, 'getStatusCode') => $e->getStatusCode(),
                default => 500,
            };

            $isProduction = config('app.env') === 'production' || app()->isProduction();
            $message = match (true) {
                $e instanceof \Illuminate\Validation\ValidationException => 'The provided data is invalid.',
                $e instanceof \Illuminate\Auth\AuthenticationException => 'Unauthenticated.',
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'This action is unauthorized.',
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 'The requested resource was not found.',
                $e instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException => 'The requested route was not found.',
                $isProduction => 'Something went wrong.',
                default => $e->getMessage() ?: 'Something went wrong.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => method_exists($e, 'errors') ? $e->errors() : (object) [],
            ], $status ?: 500);
        });
    })->create();
