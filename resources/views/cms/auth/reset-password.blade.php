<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="auth-password-field">
                <x-text-input
                    id="password"
                    class="block mt-1 w-full auth-password-field__input"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="new-password"
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

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <div class="auth-password-field">
                <x-text-input
                    id="password_confirmation"
                    class="block mt-1 w-full auth-password-field__input"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    class="auth-password-field__toggle"
                    x-on:click="showPassword = !showPassword"
                    x-bind:aria-label="showPassword ? 'Skjul adgangskode' : 'Vis adgangskode'"
                    x-text="showPassword ? 'Skjul' : 'Vis'"
                ></button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
