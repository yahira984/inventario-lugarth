<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function registrar(string $modulo, string $accion, string $descripcion, array $datos = [], ?Request $request = null): void
    {
        $request ??= request();

        AuditLog::create([
            'user_id' => Auth::id(),
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'ruta' => $request?->path(),
            'ip' => $request?->ip(),
            'datos' => $datos ?: null,
        ]);
    }
}
