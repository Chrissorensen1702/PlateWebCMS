<x-app-layout>
    <x-slot name="header">
        <div class="section-heading">
            <div class="section-heading__content">
                <p class="section-heading__kicker">PLATFORM PAKKER</p>
                <h2 class="section-heading__title">Pakkesystemet er nulstillet</h2>
                <p class="section-heading__copy">
                    Den gamle dynamiske pakkeopsætning er fjernet fra overfladen, mens vi bygger en ny og mere enkel pakke-struktur.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="ui-shell">
        @if (session('status'))
            <div class="ui-status">
                {{ session('status') }}
            </div>
        @endif

        <section class="ui-card" style="max-width: 56rem; display: grid; gap: 1rem;">
            <p class="section-heading__kicker">Midlertidig pause</p>
            <h3 class="section-heading__title" style="font-size: clamp(1.55rem, 2.3vw, 2rem);">Vi starter forfra med faste pakker</h3>
            <p class="section-heading__copy" style="max-width: 42rem;">
                Det gamle pakke-admin er sat på pause, så vi ikke viderefører logik fra den dynamiske prisberegner.
                Datagrundlaget er bevaret i baggrunden indtil videre, men der kan ikke oprettes eller redigeres pakker herfra længere.
            </p>

            <div style="display: grid; gap: 0.6rem; color: var(--color-muted); line-height: 1.7;">
                <p style="margin: 0;">Næste skridt er at definere de nye faste pakker og bagefter bygge en ny, ren pris-side ovenpå.</p>
                <p style="margin: 0;">Indtil da er denne side kun en markering af, at det gamle system er lagt til side.</p>
            </div>
        </section>
    </div>
</x-app-layout>
