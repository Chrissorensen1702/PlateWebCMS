@php
    $data = $area->data ?? [];
    $itemsStyle = $data['items_style'] ?? 'list';
    $sectionTone = $data['section_tone'] ?? 'default';
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell">
        <article class="minimal-content{{ $sectionTone === 'accent' ? ' minimal-content--accent' : '' }}">
            <div class="minimal-content__lead">
                @if (! empty($data['eyebrow']))
                    <p class="minimal-kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="minimal-section-title">{{ $data['title'] ?? $page->title }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            @if (! empty($data['items']) && is_array($data['items']))
                @if ($itemsStyle === 'cards')
                    <div class="minimal-content__cards">
                        @foreach ($data['items'] as $item)
                            <article class="minimal-note-card">
                                <span>{{ $item }}</span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <ul class="minimal-content__list">
                        @foreach ($data['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </article>
    </div>
</section>
