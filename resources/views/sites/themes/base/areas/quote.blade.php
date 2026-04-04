@php
    $data = $area->data ?? [];
    $textAlign = $data['text_align'] ?? 'left';
    $sectionTone = $data['section_tone'] ?? 'accent';
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell site-section__shell site-section__shell--wide">
        <article class="site-quote{{ $textAlign === 'center' ? ' site-quote--centered' : '' }}{{ $sectionTone === 'accent' ? ' site-quote--accent' : '' }}">
            @if (! empty($data['eyebrow']))
                <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
            @endif

            <blockquote class="site-quote__text">
                {{ $data['quote_text'] ?? 'Tilføj et citat i designeren.' }}
            </blockquote>

            @if (! empty($data['quote_author']) || ! empty($data['quote_role']))
                <div class="site-quote__meta">
                    @if (! empty($data['quote_author']))
                        <strong>{{ $data['quote_author'] }}</strong>
                    @endif

                    @if (! empty($data['quote_role']))
                        <span>{{ $data['quote_role'] }}</span>
                    @endif
                </div>
            @endif
        </article>
    </div>
</section>
