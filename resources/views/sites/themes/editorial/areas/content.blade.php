@php
    $data = $area->data ?? [];
    $textAlign = $data['text_align'] ?? 'left';
    $itemsStyle = $data['items_style'] ?? 'cards';
    $sectionTone = $data['section_tone'] ?? 'default';
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell">
        <article class="ui-card editorial-story editorial-story--{{ $itemsStyle }}{{ $textAlign === 'center' ? ' editorial-story--centered' : '' }}{{ $sectionTone === 'accent' ? ' editorial-story--accent' : '' }}">
            <div class="editorial-story__intro">
                @if (! empty($data['eyebrow']))
                    <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="ui-title">{{ $data['title'] ?? $page->title }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            @if (! empty($data['items']) && is_array($data['items']))
                @if ($itemsStyle === 'list')
                    <ul class="editorial-story__list">
                        @foreach ($data['items'] as $item)
                            <li class="editorial-story__list-item">{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="editorial-story__cards">
                        @foreach ($data['items'] as $item)
                            <article class="editorial-note-card">
                                <span class="editorial-note-card__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <p>{{ $item }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endif
        </article>
    </div>
</section>
