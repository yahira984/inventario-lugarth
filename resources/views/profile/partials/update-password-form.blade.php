<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Cambiar contrasena</h2>
        <p class="mt-1 text-sm text-gray-600">
            La nueva contrasena debe tener minimo 8 caracteres. Escribela dos veces para confirmar.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Contrasena actual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" placeholder="Escribe tu contrasena actual" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva contrasena" />
            <x-text-input id="update_password_password" name="password" type="password" minlength="8" class="mt-1 block w-full" autocomplete="new-password" placeholder="Minimo 8 caracteres" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contrasena" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" minlength="8" class="mt-1 block w-full" autocomplete="new-password" placeholder="Repite la nueva contrasena" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Actualizar contrasena</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-semibold text-green-700"
                >Contrasena actualizada correctamente.</p>
            @endif
        </div>
    </form>
</section>
