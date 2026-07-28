<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Datos de la cuenta</h2>
        <p class="mt-1 text-sm text-gray-600">
            Actualiza tu nombre, correo y fotografía para que el equipo pueda identificarte.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form id="profileInformationForm" method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="Ej. Kevin Ávila" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="correo@empresa.com" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="avatar" value="Foto de perfil" />
            <div class="profile-avatar-row">
                <button
                    type="button"
                    class="profile-avatar-button"
                    data-workspace-lightbox
                    data-lightbox-title="Foto de perfil de {{ $user->name }}"
                    aria-label="Ver foto de perfil en grande"
                >
                    <img
                        src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=1769D2&background=EAF3FF' }}"
                        alt="Foto de perfil de {{ $user->name }}"
                    >
                </button>
                <div class="profile-file-field">
                    <input id="avatar" name="avatar" type="file" accept="image/*">
                    <small>JPG, PNG o WEBP. La imagen se reduce y optimiza automáticamente para ahorrar espacio.</small>
                    <small id="avatarProcessingStatus" role="status" aria-live="polite"></small>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                Este correo todavía no está verificado.
                <button form="send-verification" class="font-semibold underline hover:text-amber-950">
                    Enviar nuevamente el correo de verificación.
                </button>
            </div>

            @if (session('status') === 'verification-link-sent')
                <p class="text-sm font-semibold text-green-700">
                    Listo, enviamos un nuevo enlace de verificación a tu correo.
                </p>
            @endif
        @endif

        <div class="profile-actions">
            <x-primary-button>Guardar cambios</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-semibold text-green-700"
                >Datos actualizados correctamente.</p>
            @endif
        </div>
    </form>
</section>
