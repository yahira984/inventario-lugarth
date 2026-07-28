<?php

use App\Http\Middleware\UpdateLastSeen;
use Illuminate\Database\QueryException;
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
    ->withMiddleware(function (Middleware $middleware) {

        // Agrega el middleware a la lista web 👇
        $middleware->web(append: [
            UpdateLastSeen::class,
        ]);

        $middleware->redirectUsersTo(
            fn (Request $request): string => route(
                $request->user()?->rutaInicio() ?? 'login',
            ),
        );

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (QueryException $exception, Request $request) {
            if (! $request->is('equipo/*')) {
                return null;
            }

            return response()->json([
                'message' => 'El chat todavía no termina de actualizarse en esta computadora. Aplica las migraciones pendientes y vuelve a intentarlo.',
            ], 503);
        });

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if ($request->is('profile')) {
                return redirect()
                    ->route('profile.edit')
                    ->withErrors([
                        'avatar' => 'La foto original es demasiado pesada para esta computadora. Vuelve a seleccionarla para que el sistema la optimice antes de subirla.',
                    ]);
            }

            if (! $request->is('admin/respaldos/restaurar')) {
                return null;
            }

            $message = 'El respaldo supera el límite de carga del servidor. '
                .'Reinicia Herd para aplicar la configuración del proyecto y vuelve a intentarlo.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return redirect()
                ->route('admin.backups.index')
                ->with('error', $message);
        });
    })->create();
