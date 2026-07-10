<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - AppLugarth</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gray-900 py-10">
    
    <!-- Fondo consistente con el login -->
    <div class="absolute inset-0 bg-cover bg-center z-0 fixed" style="background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070&auto=format&fit=crop');"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 to-orange-900/70 z-0 backdrop-blur-sm fixed"></div>

    <!-- Tarjeta de Registro -->
    <div class="relative z-10 w-full max-w-lg bg-white/95 p-8 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.5)] border-t-4 border-blue-500">
        
        <div class="text-center mb-6">
            <h2 class="text-3xl font-extrabold text-blue-900">Nuevo Operador</h2>
            <p class="text-gray-500 mt-1">Alta en el sistema de gestión de equipos</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Nombre -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo</label>
                <input type="text" name="name" required autofocus class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-gray-50">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Correo </label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-gray-50">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Contraseña -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-gray-50">
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Confirmar</label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all bg-gray-50">
                </div>
            </div>

            <!-- Botón -->
            <button type="submit" class="w-full py-3.5 bg-orange-500 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg transition-all transform hover:-translate-y-1 text-lg mt-4">
                Registrar Cuenta
            </button>
        </form>

        <p class="mt-6 text-center text-gray-600 text-sm">
            ¿Ya eres parte del equipo? <a href="{{ route('login') }}" class="font-bold text-orange-500 hover:text-blue-700">Inicia sesión</a>
        </p>
    </div>
</body>
</html>