<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - AppLugarth</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gray-900 py-10 px-4">
    <div class="absolute inset-0 bg-cover bg-center z-0 fixed" style="background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070&auto=format&fit=crop');"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 to-orange-900/70 z-0 backdrop-blur-sm fixed"></div>

    <div class="relative z-10 w-full max-w-lg bg-white/95 p-8 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] border-t-4 border-blue-500">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-extrabold text-blue-900">Nuevo operador</h2>
            <p class="text-gray-500 mt-1">Alta en el sistema de inventario</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <strong class="block mb-1">Revisa estos datos</strong>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate>
            @csrf

            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nombre completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nombre de la persona" class="w-full px-4 py-3 rounded-lg border @error('name') border-red-500 bg-red-50 @else border-gray-300 bg-gray-50 @enderror focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                @error('name')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="usuario@empresa.com" class="w-full px-4 py-3 rounded-lg border @error('email') border-red-500 bg-red-50 @else border-gray-300 bg-gray-50 @enderror focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Contraseña</label>
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="w-full px-4 py-3 rounded-lg border @error('password') border-red-500 bg-red-50 @else border-gray-300 bg-gray-50 @enderror focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    <p id="passwordHelp" class="mt-2 text-xs font-semibold text-gray-500">Debe tener al menos 8 caracteres.</p>
                    @error('password')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">Confirmar</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Repite la contraseña" class="w-full px-4 py-3 rounded-lg border @error('password_confirmation') border-red-500 bg-red-50 @else border-gray-300 bg-gray-50 @enderror focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    <p id="confirmHelp" class="mt-2 text-xs font-semibold text-gray-500">Debe ser igual a la contraseña.</p>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-orange-500 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition-all transform hover:-translate-y-1 text-lg mt-4">
                Registrar cuenta
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600 text-sm">
            ¿Ya eres parte del equipo? <a href="{{ route('login') }}" class="font-bold text-orange-500 hover:text-blue-700">Inicia sesión</a>
        </p>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const passwordHelp = document.getElementById('passwordHelp');
        const confirmHelp = document.getElementById('confirmHelp');

        function revisarPassword() {
            const faltan = Math.max(0, 8 - passwordInput.value.length);

            if (faltan > 0) {
                passwordHelp.textContent = `Faltan ${faltan} caracter${faltan === 1 ? '' : 'es'} para completar los 8 mínimos.`;
                passwordHelp.className = 'mt-2 text-xs font-semibold text-orange-600';
            } else {
                passwordHelp.textContent = 'La contraseña ya cumple con el mínimo.';
                passwordHelp.className = 'mt-2 text-xs font-semibold text-green-600';
            }

            revisarConfirmacion();
        }

        function revisarConfirmacion() {
            if (!confirmInput.value) {
                confirmHelp.textContent = 'Debe ser igual a la contraseña.';
                confirmHelp.className = 'mt-2 text-xs font-semibold text-gray-500';
                return;
            }

            if (confirmInput.value !== passwordInput.value) {
                confirmHelp.textContent = 'No coincide todavía. Escríbela exactamente igual.';
                confirmHelp.className = 'mt-2 text-xs font-semibold text-red-600';
                return;
            }

            confirmHelp.textContent = 'Las contraseñas coinciden.';
            confirmHelp.className = 'mt-2 text-xs font-semibold text-green-600';
        }

        passwordInput.addEventListener('input', revisarPassword);
        confirmInput.addEventListener('input', revisarConfirmacion);
    </script>
</body>
</html>
