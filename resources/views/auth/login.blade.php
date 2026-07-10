<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AppLugarth</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gray-900">
    
    <!-- Fondo de inventario colorido -->
    <div class="absolute inset-0 bg-cover bg-center z-0" style="background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070&auto=format&fit=crop');"></div>
    <!-- Capa de color vibrante (Azul/Naranja) -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 to-orange-900/70 z-0 backdrop-blur-sm"></div>

    <!-- Tarjeta de Login -->
    <div class="relative z-10 w-full max-w-md bg-white/95 p-8 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] border-t-4 border-orange-500">
        
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-blue-900 tracking-tight">InventarioLugarth</h1>
            <p class="text-orange-600 font-semibold mt-2">Control de Inventario y Manufactura</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" name="email" required autofocus class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all text-gray-800 bg-gray-50">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Contraseña</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all text-gray-800 bg-gray-50">
            </div>

            <!-- Recordar y Olvidé mi contraseña -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500 w-4 h-4 mr-2">
                    Recordar sesión
                </label>
                <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-orange-500 transition-colors">¿Olvidaste tu contraseña?</a>
            </div>

            <!-- Botón -->
            <button type="submit" class="w-full py-3.5 bg-blue-700 hover:bg-orange-500 text-white font-bold rounded-lg shadow-lg transition-all transform hover:-translate-y-1 text-lg">
                Ingresar al Almacén
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600 text-sm">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-orange-500">Regístrate aquí</a>
        </p>
    </div>
</body>
</html>