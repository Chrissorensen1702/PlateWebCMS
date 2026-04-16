@extends('sales.layouts.default')

@section('title', 'Opret konto')
@section('body-class', 'marketing-body marketing-body--register')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="ui-section ui-section--tight register-page">
        <div class="ui-shell register-page__shell">
            <div class="register-page__hero">
                <div class="register-page__top">
                    <div class="register-page__content">
                        <p class="register-page__eyebrow">Kom igang med det samme</p>
                        <h1 class="register-page__title">Kom i gang på få minutter</h1>
                        <p class="register-page__copy">
                           Med vores dynamiske prisberegner finder vi en pris, der passer til netop jeres setup og størrelse. Brug prisberegneren, inden I opretter en konto, så vi sammen kan sikre en løsning og en pris, der er rentabel for begge parter.
                        </p>

                        @if (empty($pendingSolution))
                            <article class="ui-card register-page__solution-card register-page__solution-card--empty">
                                <p class="register-page__solution-eyebrow">Vil du starte med prisguiden?</p>
                                <h2 class="register-page__solution-title">Byg først din løsning og opret derefter konto.</h2>
                                <p class="register-page__solution-detail">
                                    Hvis du går gennem prisguiden først, gemmer vi dit valgte spor og dine vigtigste behov, så de er klar herinde bagefter.
                                </p>

                                <a href="{{ route('templates') }}#pricing-guide" class="ui-button register-page__solution-action">
                                    Se priser og pakker
                                </a>
                            </article>

                            <ul class="register-page__solution-steps" aria-label="Sådan fungerer prisguiden">
                                <li>Vælg den pakke og de tilvalg, der passer til jeres behov.</li>
                                <li>Udfyld jeres ønsker, så vi kan beregne en pris ud fra setup og størrelse.</li>
                                <li>Opret konto bagefter og fortsæt direkte med det gemte oplæg.</li>
                            </ul>
                        @endif
                    </div>

                    <div class="register-page__aside">
                        <div class="ui-card register-page__panel">
                            <div class="register-page__panel-header">
                                <p class="register-page__panel-kicker">Dine oplysninger</p>
                                <h2 class="register-page__panel-title">Opret din konto</h2>
                                <p class="register-page__panel-copy">Det tager kun et øjeblik at komme videre.</p>
                            </div>

                            @if (session('status'))
                                <div class="ui-status">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" class="register-page__form">
                                @csrf

                                <div class="register-page__field-grid">
                                    <div class="ui-field">
                                        <x-input-label for="name" value="Navn" />
                                        <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                                        <x-input-error :messages="$errors->get('name')" class="ui-field__error" />
                                    </div>

                                    <div class="ui-field">
                                        <x-input-label for="email" value="Email" />
                                        <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
                                        <x-input-error :messages="$errors->get('email')" class="ui-field__error" />
                                    </div>
                                </div>

                                <div class="register-page__field-grid">
                                    <div class="ui-field">
                                        <x-input-label for="phone" value="Telefon" />
                                        <x-text-input id="phone" type="text" name="phone" :value="old('phone')" required autocomplete="tel" />
                                        <x-input-error :messages="$errors->get('phone')" class="ui-field__error" />
                                    </div>

                                    <div class="ui-field">
                                        <x-input-label for="cvr_number" value="CVR (valgfrit)" />
                                        <x-text-input id="cvr_number" type="text" name="cvr_number" :value="old('cvr_number')" autocomplete="organization" />
                                        <x-input-error :messages="$errors->get('cvr_number')" class="ui-field__error" />
                                    </div>
                                </div>

                                <div class="ui-field" x-data="{ showPassword: false }">
                                    <x-input-label for="password" value="Adgangskode" />

                                    <div class="register-page__password-field">
                                        <x-text-input
                                            id="password"
                                            x-bind:type="showPassword ? 'text' : 'password'"
                                            name="password"
                                            required
                                            autocomplete="new-password"
                                            class="register-page__password-input"
                                        />

                                        <button
                                            type="button"
                                            class="register-page__password-toggle"
                                            x-on:click="showPassword = !showPassword"
                                            x-bind:aria-label="showPassword ? 'Skjul adgangskode' : 'Vis adgangskode'"
                                            x-text="showPassword ? 'Skjul' : 'Vis'"
                                        ></button>
                                    </div>

                                    <x-input-error :messages="$errors->get('password')" class="ui-field__error" />
                                </div>

                                <div class="ui-field">
                                    <x-input-label for="registration_note" value="Evt. note til os" />
                                    <textarea
                                        id="registration_note"
                                        name="registration_note"
                                        rows="4"
                                        class="ui-field__control ui-field__control--textarea"
                                    >{{ old('registration_note') }}</textarea>
                                    <x-input-error :messages="$errors->get('registration_note')" class="ui-field__error" />
                                </div>

                                <div class="register-page__options">
                                    <label class="register-page__option">
                                        <input
                                            type="hidden"
                                            name="wants_callback"
                                            value="0"
                                        >
                                        <input
                                            type="checkbox"
                                            name="wants_callback"
                                            value="1"
                                            class="register-page__checkbox"
                                            {{ old('wants_callback') ? 'checked' : '' }}
                                        >
                                        <span class="register-page__option-copy">
                                            <span class="register-page__option-title">Ønskes et opkald med gennemgang?</span>
                                            <span class="register-page__option-text">Sæt flueben hvis du gerne vil have, at vi ringer dig op og gennemgår løsningen sammen.</span>
                                        </span>
                                    </label>

                                    <label class="register-page__option register-page__option--required">
                                        <input
                                            type="checkbox"
                                            id="accept_terms"
                                            name="accept_terms"
                                            value="1"
                                            class="register-page__checkbox"
                                            {{ old('accept_terms') ? 'checked' : '' }}
                                            required
                                        >
                                        <span class="register-page__option-copy">
                                            <span class="register-page__option-title">Accepter vores betingelser og vilkår</span>
                                            <span class="register-page__option-text">Du skal acceptere betingelser og vilkår for at oprette din konto.</span>
                                        </span>
                                    </label>

                                    <x-input-error :messages="$errors->get('accept_terms')" class="ui-field__error" />
                                </div>

                                <div class="register-page__form-footer">
                                    <p class="register-page__form-note">
                                        Når kontoen er oprettet, kan du logge ind igen og fortsætte uden at starte forfra.
                                    </p>

                                    <div class="register-page__actions">
                                        <a class="register-page__login-link" href="{{ route('login') }}">
                                            Har du allerede en konto? Log ind
                                        </a>

                                        <x-primary-button class="register-page__submit">
                                            Opret konto
                                        </x-primary-button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="register-page__support">
                    @if (! empty($pendingSolution))
                        <article class="ui-card register-page__solution-card">
                            <div class="register-page__solution-top">
                                <div>
                                    <p class="register-page__solution-eyebrow">Din gemte løsning</p>
                                    <h2 class="register-page__solution-title">{{ $pendingSolution['title'] }}</h2>
                                </div>
                                <p class="register-page__solution-price">{{ $pendingSolution['price'] }}</p>
                            </div>

                            <p class="register-page__solution-note">{{ $pendingSolution['price_note'] }}</p>
                            <p class="register-page__solution-detail">{{ $pendingSolution['detail'] }}</p>
                        </article>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
