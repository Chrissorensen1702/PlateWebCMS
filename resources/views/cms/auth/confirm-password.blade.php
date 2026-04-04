<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="auth-password-field">
                <x-text-input
                    id="password"
                    class="block mt-1 w-full auth-password-field__input"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                />

                <button
                    type="button"
                    class="auth-password-field__toggle"
                    x-on:click="showPassword = !showPassword"
                    x-bind:aria-label="showPassword ? 'Skjul adgangskode' : 'Vis adgangskode'"
                    x-text="showPassword ? 'Skjul' : 'Vis'"
                ></button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
