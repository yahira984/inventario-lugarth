<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para administrar usuarios.');

        return view('usuarios.index', [
            'usuarios' => User::query()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->puedeAdministrarCatalogo(), 403, 'No tienes permiso para administrar usuarios.');

        $datos = $request->validate([
            'role' => ['required', Rule::in(['administrador', 'almacenista', 'consultor'])],
            'approved' => ['nullable', 'boolean'],
        ], [
            'role.required' => 'Selecciona un rol para el usuario.',
            'role.in' => 'El rol seleccionado no es valido.',
        ]);

        $aprobado = $request->boolean('approved');

        if ($request->user()->is($user) && ($datos['role'] !== 'administrador' || ! $aprobado)) {
            return back()->withErrors([
                'role' => 'No puedes quitarte el rol de administrador ni desaprobar tu propia cuenta.',
            ]);
        }

        $user->update([
            'role' => $datos['role'],
            'approved_at' => $aprobado ? ($user->approved_at ?? now()) : null,
        ]);

        return back()->with('success', 'Permisos y aprobacion actualizados correctamente.');
    }
}
