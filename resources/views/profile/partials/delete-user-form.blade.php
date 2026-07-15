<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-gray-900">Eliminar cuenta</h2>
        <p class="mt-1 text-sm text-gray-600">
            Esta accion borra tu cuenta de acceso. Solo usala si realmente ya no necesitas entrar al sistema.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar mi cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900">
                Confirmar eliminacion de cuenta
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Para evitar accidentes, escribe tu contrasena. Despues de confirmar, esta cuenta ya no podra iniciar sesion.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contrasena" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Contrasena"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
