@php
    $data = $area->data ?? [];
    $textAlign = $data['text_align'] ?? 'left';
    $itemsStyle = $data['items_style'] ?? 'list';
    $sectionTone = $data['section_tone'] ?? 'default';
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell">
        <article class="midnight-panel midnight-panel--{{ $itemsStyle }}{{ $sectionTone === 'accent' ? ' midnight-panel--accent' : '' }}{{ $textAlign === 'center' ? ' midnight-panel--centered' : '' }}">
            <div class="midnight-panel__grid">
                <div class="midnight-panel__intro">
                    @if (! empty($data['eyebrow']))
                        <p class="midnight-kicker">{{ $data['eyebrow'] }}</p>
                    @endif

                    <h2 class="midnight-section-title">{{ $data['title'] ?? $page->title }}</h2>

                    @if (! empty($data['copy']))
                        <p class="ui-copy">{{ $data['copy'] }}</p>
                    @endif
                </div>

                @if (! empty($data['items']) && is_array($data['items']))
                    @if ($itemsStyle === 'cards')
                        <div class="midnight-panel__cards">
                            @foreach ($data['items'] as $item)
                                <article class="midnight-chip-card">
                                    <span class="midnight-chip-card__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <p>{{ $item }}</p>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="midnight-panel__timeline">
                            @foreach ($data['items'] as $item)
                                <article class="midnight-timeline-item">
                                    <span class="midnight-timeline-item__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <p>{{ $item }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </article>
    </div>
</section>
