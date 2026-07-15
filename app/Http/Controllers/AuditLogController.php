<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $logs = AuditLog::query()
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('admin.auditoria.index', compact('logs'));
    }

    public function destroy(Request $request, AuditLog $auditLog): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        $auditLog->delete();

        AuditLogger::registrar('Auditoria', 'Eliminacion de log', 'Elimino un registro de auditoria.', [
            'log_id' => $auditLog->id,
        ], $request);

        return back()->with('success', 'Registro de auditoria eliminado.');
    }

    public function clear(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403);

        AuditLog::query()->delete();

        AuditLogger::registrar('Auditoria', 'Limpieza de logs', 'Elimino todos los registros de auditoria.', [], $request);

        return back()->with('success', 'Auditoria limpiada correctamente.');
    }
}
