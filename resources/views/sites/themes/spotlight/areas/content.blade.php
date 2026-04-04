@php
    $data = $area->data ?? [];
    $itemsStyle = $data['items_style'] ?? 'cards';
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell">
        <article class="spotlight-story">
            <div class="spotlight-story__lead">
                @if (! empty($data['eyebrow']))
                    <p class="spotlight-kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="spotlight-section-title">{{ $data['title'] ?? $page->title }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            @if (! empty($data['items']) && is_array($data['items']))
                @if ($itemsStyle === 'list')
                    <ul class="spotlight-story__list">
                        @foreach ($data['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="spotlight-story__cards">
                        @foreach ($data['items'] as $item)
                            <article class="spotlight-card">
                                <span class="spotlight-card__count">{{ $loop->iteration }}</span>
                                <p>{{ $item }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endif
        </article>
    </div>
</section>
