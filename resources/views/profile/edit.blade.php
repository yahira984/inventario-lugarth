<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi perfil - Inventario Lugarth</title>
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2875/2875878.png" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, sans-serif; background: #f6f8fb; color: #102033; }
        .app-shell { display: flex; min-height: 100vh; }
        .app-content { flex: 1; padding: 34px 18px; }
        .profile-page { max-width: 1060px; margin: 0 auto; }
        .profile-header { background: #fff; border: 1px solid #dbe5f0; border-radius: 16px; padding: 24px; box-shadow: 0 16px 40px rgba(15, 23, 42, .08); }
        .profile-header h1 { margin: 0 0 6px; font-size: clamp(26px, 4vw, 38px); }
        .profile-header p { margin: 0; color: #64748b; font-weight: 600; }
        .profile-grid { display: grid; gap: 18px; margin-top: 18px; }
        .profile-card { background: #fff; border: 1px solid #dbe5f0; border-radius: 16px; padding: 24px; box-shadow: 0 16px 40px rgba(15, 23, 42, .08); }
        .profile-card input { border-radius: 10px; border-color: #cbd5e1; min-height: 42px; }
        .profile-card button { transition: transform .16s ease, box-shadow .16s ease, background .16s ease; }
        .profile-card button:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(37, 99, 235, .18); }
        @media (max-width: 860px) {
            .app-content { padding: 78px 12px 24px; }
            .profile-card, .profile-header { padding: 18px; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    @include('materiales.partials.sidebar')

    <main class="app-content">
        <div class="profile-page">
            <header class="profile-header">
                <h1>Mi perfil</h1>
                <p>Cambia tus datos, actualiza tu contraseña y revisa la seguridad de tu cuenta.</p>
            </header>

            <div class="profile-grid">
                <section class="profile-card">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="profile-card">
                    @include('profile.partials.update-password-form')
                </section>

                <section class="profile-card">
                    <h2 style="margin:0 0 6px">Actividad reciente</h2>
                    <p style="margin:0 0 16px;color:#64748b">Últimas acciones registradas con tu cuenta.</p>
                    @forelse($recentActivity as $activity)
                        <div style="display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;padding:11px 0;border-bottom:1px solid #e2e8f0">
                            <div><strong>{{ $activity->accion }}</strong><div style="margin-top:3px;color:#64748b;font-size:12px">{{ $activity->descripcion }}</div></div>
                            <small style="white-space:nowrap">{{ $activity->created_at?->format('d/m/Y H:i') }}</small>
                        </div>
                    @empty
                        <div class="workspace-empty-panel"><strong>Aún no hay actividad</strong><span>Tus próximas acciones aparecerán aquí.</span></div>
                    @endforelse
                </section>

                <section class="profile-card">
                    @include('profile.partials.delete-user-form')
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>
