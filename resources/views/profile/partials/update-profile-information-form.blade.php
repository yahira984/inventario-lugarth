<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Datos de la cuenta</h2>
        <p class="mt-1 text-sm text-gray-600">
            Actualiza tu nombre y correo. Usa un correo real para que el administrador pueda identificarte.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="Ej. Kevin Avila" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electronico" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="correo@empresa.com" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Este correo todavia no esta verificado.
                    <button form="send-verification" class="font-semibold underline hover:text-amber-950">
                        Enviar correo de verificacion otra vez.
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-semibold text-green-700">
                        Listo, enviamos un nuevo enlace de verificacion a tu correo.
                    </p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4">
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
