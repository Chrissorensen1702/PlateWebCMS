@extends('sales.layouts.default')

@section('title', 'Mobilapp')
@section('body-class', 'marketing-body marketing-body--mobile-app')

@section('header')
    @include('sales.layouts.header')
@endsection

@section('main-content')
    <section class="mobile-app-page" aria-label="Mobilapp introsektion">
        <img
            src="{{ asset('images/sales/mobilapp-intro.png') }}"
            alt=""
            aria-hidden="true"
            class="mobile-app-page__ambient-image"
        >

        <div class="ui-shell mobile-app-page__shell">
            <div class="mobile-app-page__content">
                <p class="mobile-app-page__eyebrow">PlateBook app</p>
                <h1 class="mobile-app-page__title">
                    <span class="mobile-app-page__title-line">
                        Hele dit
                        <span class="mobile-app-page__title-brand" aria-label="platebook">
                            <span class="mobile-app-page__title-brand-plate">plate</span><span class="mobile-app-page__title-brand-book">book</span>
                        </span>
                    </span>
                    <span class="mobile-app-page__title-line">lige ved hånden.</span>
                </h1>
                <p class="mobile-app-page__copy">
                    Vores PWA løsning giver dig muligheden for at din bookingkalender lige ved hånden, hvor end
                    du sidder derhjemme eller på arbejdet.
                </p>

                <div class="mobile-app-page__actions">
                    <a href="#iphone-guide" class="mobile-app-page__store-button mobile-app-page__store-button--light">
                        <span class="mobile-app-page__store-label">Guide til download</span>
                        <span class="mobile-app-page__store-platform">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="mobile-app-page__store-icon">
                                <path fill="currentColor" d="M16.37 12.18c.02 2.13 1.87 2.84 1.89 2.85-.02.05-.29 1-.95 1.98-.58.85-1.18 1.69-2.13 1.71-.94.02-1.24-.56-2.31-.56-1.08 0-1.42.54-2.29.58-.91.03-1.61-.92-2.19-1.76-1.19-1.73-2.09-4.88-.87-7 .61-1.05 1.71-1.72 2.9-1.74.9-.02 1.75.61 2.31.61.56 0 1.61-.75 2.71-.64.46.02 1.75.18 2.58 1.39-.07.04-1.54.9-1.55 2.58ZM14.87 6.5c.48-.58.81-1.39.72-2.2-.7.03-1.54.46-2.05 1.04-.45.51-.84 1.33-.73 2.12.78.06 1.57-.4 2.06-.96Z"/>
                            </svg>
                            <strong>iPhone</strong>
                        </span>
                    </a>
                    <a href="#android-guide" class="mobile-app-page__store-button mobile-app-page__store-button--dark">
                        <span class="mobile-app-page__store-label">Guide til download</span>
                        <span class="mobile-app-page__store-platform">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="mobile-app-page__store-icon">
                                <path fill="currentColor" d="M7.17 14.46c-.2.34-.31.72-.31 1.13v2.54c0 .77.62 1.4 1.4 1.4h.94v2.2c0 .33.27.6.6.6h.6c.33 0 .6-.27.6-.6v-2.2h2v2.2c0 .33.27.6.6.6h.6c.33 0 .6-.27.6-.6v-2.2h.94c.77 0 1.4-.62 1.4-1.4v-2.54c0-.41-.11-.79-.31-1.13H7.17Zm9.98-5.7.68-1.23a.33.33 0 1 0-.58-.32l-.69 1.25A6.7 6.7 0 0 0 12 7.06c-1.63 0-3.12.53-4.55 1.4l-.69-1.25a.33.33 0 1 0-.58.32l.68 1.23a5.23 5.23 0 0 0-2.42 4.42h15.12a5.23 5.23 0 0 0-2.41-4.42ZM9.5 10.98a.72.72 0 1 1 0-1.45.72.72 0 0 1 0 1.45Zm5 0a.72.72 0 1 1 0-1.45.72.72 0 0 1 0 1.45Z"/>
                            </svg>
                            <strong>Android</strong>
                        </span>
                    </a>
                </div>
            </div>

            <div class="mobile-app-page__visual" aria-hidden="true">
                <div class="mobile-app-page__phone-stage">
                    <div class="mobile-app-page__native-badge">
                        <span class="mobile-app-page__native-badge-kicker">På vej</span>
                        <strong class="mobile-app-page__native-badge-title">Native apps</strong>
                        <span class="mobile-app-page__native-badge-copy">Vi arbejder mod App Store og Google Play.</span>
                    </div>

                    <div class="marketing-hero__phone mobile-app-page__phone">
                        <div class="marketing-hero__phone-notch"></div>
                        <div class="marketing-hero__phone-screen">
                            <img
                                src="{{ asset('images/sales/platebook-phone-mockup.png') }}"
                                alt=""
                                class="marketing-hero__phone-media"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mobile-app-guides" aria-labelledby="mobile-app-guides-title">
        <div class="ui-shell mobile-app-guides__shell">
            <div class="mobile-app-guides__heading">
                <p class="mobile-app-guides__eyebrow">PWA guide</p>
                <h2 id="mobile-app-guides-title" class="mobile-app-guides__title">Sådan lægger du appen på hjemmeskærmen</h2>
                <p class="mobile-app-guides__copy">
                    Brug samme løsning på både iPhone og Android. Vælg din enhed herunder og følg de enkle trin.
                </p>
            </div>

            <div class="mobile-app-guides__grid">
                <article id="iphone-guide" class="mobile-app-guide-card mobile-app-guide-card--iphone">
                    <p class="mobile-app-guide-card__platform">iPhone</p>
                    <h3 class="mobile-app-guide-card__title">Download via Safari</h3>

                    <ol class="mobile-app-guide-card__steps">
                        <li>Åbn websitet i Safari.</li>
                        <li>Tryk på <strong>Del</strong>-ikonet nederst på skærmen.</li>
                        <li>Vælg <strong>Føj til hjemmeskærm</strong>.</li>
                        <li>Tryk derefter <strong>Tilføj</strong>, så bliver appen lagt på din hjemmeskærm.</li>
                    </ol>
                </article>

                <article id="android-guide" class="mobile-app-guide-card mobile-app-guide-card--android">
                    <p class="mobile-app-guide-card__platform">Android</p>
                    <h3 class="mobile-app-guide-card__title">Download via Chrome</h3>

                    <ol class="mobile-app-guide-card__steps">
                        <li>Åbn websitet i Chrome.</li>
                        <li>Tryk på menuen med de tre prikker øverst til højre.</li>
                        <li>Vælg <strong>Installer app</strong> eller <strong>Føj til hjemmeskærm</strong>.</li>
                        <li>Bekræft valget, så bliver appen installeret på din enhed.</li>
                    </ol>
                </article>
            </div>
        </div>
    </section>
@endsection
