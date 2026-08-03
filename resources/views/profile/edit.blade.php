<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mi perfil - Inventario Lugarth</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, sans-serif; background: #f6f8fb; color: #102033; }
        .profile-page { width: min(1060px, 100%); margin: 0 auto; }
        .profile-header { background: #fff; border: 1px solid #dbe5f0; border-radius: 8px; padding: 24px; box-shadow: 0 12px 32px rgba(15, 23, 42, .07); }
        .profile-header h1 { margin: 0 0 6px; font-size: 32px; line-height: 1.15; }
        .profile-header p { margin: 0; color: #64748b; font-weight: 600; line-height: 1.5; }
        .profile-grid { display: grid; gap: 18px; margin-top: 18px; }
        .profile-card { min-width: 0; background: #fff; border: 1px solid #dbe5f0; border-radius: 8px; padding: 24px; box-shadow: 0 12px 32px rgba(15, 23, 42, .07); }
        .profile-card form, .profile-card section { min-width: 0; }
        .profile-card input { width: 100%; max-width: 100%; border-radius: 7px; border-color: #cbd5e1; min-height: 44px; }
        .profile-card button { transition: transform .16s ease, box-shadow .16s ease, background .16s ease; }
        .profile-card button:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(37, 99, 235, .18); }
        .profile-avatar-row { min-width: 0; display: grid; grid-template-columns: 88px minmax(0, 1fr); align-items: center; gap: 16px; margin-top: 10px; padding: 14px; background: #f8fafc; border: 1px solid #dbe5f0; border-radius: 8px; }
        .profile-avatar-button { width: 88px; height: 88px; padding: 0; overflow: hidden; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; cursor: zoom-in; }
        .profile-avatar-button img { width: 100%; height: 100%; object-fit: cover; }
        .profile-file-field { min-width: 0; }
        .profile-file-field input[type="file"] { padding: 7px; background: #fff; }
        .profile-file-field small { display: block; margin-top: 6px; color: #64748b; font-size: 11px; line-height: 1.45; }
        .profile-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
        .profile-activity-row { min-width: 0; display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e2e8f0; }
        .profile-activity-row > div { min-width: 0; }
        .profile-activity-row p { margin: 3px 0 0; overflow-wrap: anywhere; color: #64748b; font-size: 12px; }
        .profile-activity-row time { color: #64748b; font-size: 11px; white-space: nowrap; }
        .profile-danger-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
        @media (max-width: 640px) {
            .profile-header h1 { font-size: 26px !important; }
            .profile-card, .profile-header { padding: 18px; }
            .profile-avatar-row { grid-template-columns: 1fr; justify-items: center; }
            .profile-file-field { width: 100%; }
            .profile-actions { align-items: stretch; }
            .profile-actions > * { width: 100%; }
            .profile-actions button { width: 100%; justify-content: center; }
            .profile-activity-row { grid-template-columns: 1fr; gap: 5px; }
            .profile-danger-actions { display: grid; grid-template-columns: 1fr; }
            .profile-danger-actions button { width: 100%; justify-content: center; margin: 0 !important; }
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
                        <div class="profile-activity-row">
                            <div><strong>{{ $activity->accion }}</strong><p>{{ $activity->descripcion }}</p></div>
                            <time datetime="{{ $activity->created_at?->toIso8601String() }}">{{ $activity->created_at?->format('d/m/Y H:i') }}</time>
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
<script src="{{ asset('js/profile-avatar.js') }}" defer></script>
</body>
</html>
