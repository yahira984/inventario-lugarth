<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Cambiar contraseña</h2>
        <p class="mt-1 text-sm text-gray-600">
            La nueva contraseña debe tener mínimo 8 caracteres. Escríbela dos veces para confirmar.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" placeholder="Escribe tu contraseña actual" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva contraseña" />
            <x-text-input id="update_password_password" name="password" type="password" minlength="8" class="mt-1 block w-full" autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contraseña" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" minlength="8" class="mt-1 block w-full" autocomplete="new-password" placeholder="Repite la nueva contraseña" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="profile-actions">
            <x-primary-button>Actualizar contraseña</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-semibold text-green-700"
                >Contraseña actualizada correctamente.</p>
            @endif
        </div>
    </form>
</section>
