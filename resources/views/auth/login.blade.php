<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AppLugarth</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gray-900 px-4">
    <div class="absolute inset-0 bg-cover bg-center z-0" style="background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070&auto=format&fit=crop');"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 to-orange-900/70 z-0 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-md bg-white/95 p-8 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] border-t-4 border-orange-500">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">InventarioLugarth</h1>
            <p class="text-orange-600 font-semibold mt-2">Control de inventario y manufactura</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <strong class="block mb-1">No se pudo entrar</strong>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
            @csrf

            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="usuario@empresa.com" class="w-full px-4 py-3 rounded-lg border @error('email') border-red-500 bg-red-50 @else border-gray-300 bg-gray-50 @enderror focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all text-gray-800">
                @error('email')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Contraseña</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="current-password" placeholder="Mínimo 8 caracteres" class="w-full px-4 py-3 rounded-lg border @error('password') border-red-500 bg-red-50 @else border-gray-300 bg-gray-50 @enderror focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all text-gray-800">
                <p id="passwordHelp" class="mt-2 text-sm font-semibold text-gray-500">Debe tener al menos 8 caracteres.</p>
                @error('password')
                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm gap-3">
                <label class="flex items-center text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500 w-4 h-4 mr-2">
                    Recordar sesión
                </label>
                <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-orange-500 transition-colors">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="w-full py-3.5 bg-blue-700 hover:bg-orange-500 text-white font-bold rounded-lg shadow-lg transition-all transform hover:-translate-y-1 text-lg">
                Ingresar al almacén
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600 text-sm">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-orange-500">Regístrate aquí</a>
        </p>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordHelp = document.getElementById('passwordHelp');

        passwordInput.addEventListener('input', () => {
            const faltan = Math.max(0, 8 - passwordInput.value.length);

            if (faltan > 0) {
                passwordHelp.textContent = `Faltan ${faltan} caracter${faltan === 1 ? '' : 'es'} para completar los 8 mínimos.`;
                passwordHelp.className = 'mt-2 text-sm font-semibold text-orange-600';
                return;
            }

            passwordHelp.textContent = 'La contraseña ya cumple con el mínimo de 8 caracteres.';
            passwordHelp.className = 'mt-2 text-sm font-semibold text-green-600';
        });
    </script>
</body>
</html>
