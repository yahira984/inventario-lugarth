<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Escribe el nombre de la persona que usará la cuenta.',
            'email.required' => 'Escribe un correo para la cuenta.',
            'email.email' => 'El correo no tiene formato válido. Ejemplo: usuario@empresa.com.',
            'email.unique' => 'Ese correo ya tiene una cuenta registrada. Inicia sesión o usa otro correo.',
            'password.required' => 'Crea una contraseña para la cuenta.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres. Faltan caracteres para que sea válida.',
            'password.confirmed' => 'Las contraseñas no coinciden. Escríbelas igual en ambos campos.',
        ]);

        $esPrimerUsuario = User::count() === 0;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $esPrimerUsuario ? 'administrador' : 'consultor',
            'approved_at' => $esPrimerUsuario ? now() : null,
        ]);

        event(new Registered($user));

        if ($esPrimerUsuario) {
            auth()->login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Primer usuario creado como administrador.');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Cuenta registrada. Un administrador debe aprobar tu correo y asignarte un rol antes de entrar.');
    }
}
