@php
    $data = $area->data ?? [];
    $textAlign = $data['text_align'] ?? 'left';
    $itemsStyle = $data['items_style'] ?? 'list';
    $sectionTone = $data['section_tone'] ?? 'default';
    $isServicesCatalog = ($page->template_key ?? null) === 'services'
        && ($area->area_key ?? null) === 'services-list';
    $products = is_array($data['items'] ?? null) ? $data['items'] : [];
    $prices = is_array($data['service_prices'] ?? null) && $data['service_prices'] !== []
        ? $data['service_prices']
        : array_fill(0, max(count($products), 1), 'Pris efter aftale');
@endphp

<section class="site-section">
    <div class="ui-shell site-section__shell site-section__shell--wide">
        <article class="ui-card site-panel{{ $isServicesCatalog ? ' site-panel--services-catalog' : ' site-panel--' . $itemsStyle }}{{ ! $isServicesCatalog && $textAlign === 'center' ? ' site-panel--centered' : '' }}{{ $sectionTone === 'accent' ? ' site-panel--accent' : '' }}">
            <div class="site-panel__content">
                @if (! empty($data['eyebrow']))
                    <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="ui-title">{{ $data['title'] ?? $page->title }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            @if ($isServicesCatalog)
                <section class="site-panel__catalog-column">
                    <h3 class="site-panel__catalog-title">Produkt</h3>

                    <ul class="ui-list site-panel__list site-panel__list--catalog">
                        @foreach ($products as $item)
                            <li class="ui-list__item">
                                <span class="ui-list__dot"></span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="site-panel__catalog-column">
                    <h3 class="site-panel__catalog-title">Pris</h3>

                    <ul class="ui-list site-panel__list site-panel__list--catalog">
                        @foreach ($prices as $price)
                            <li class="ui-list__item">
                                <span class="ui-list__dot"></span>
                                <span>{{ $price }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @elseif (! empty($data['items']) && is_array($data['items']))
                @if ($itemsStyle === 'cards')
                    <div class="site-panel__cards">
                        @foreach ($data['items'] as $item)
                            <article class="site-panel__card">
                                <span>{{ $item }}</span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <ul class="ui-list site-panel__list">
                        @foreach ($data['items'] as $item)
                            <li class="ui-list__item">
                                <span class="ui-list__dot"></span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </article>
    </div>
</section>
