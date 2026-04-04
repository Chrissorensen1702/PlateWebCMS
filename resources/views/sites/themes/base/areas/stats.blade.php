@php
    $data = $area->data ?? [];
    $displayStyle = $data['display_style'] ?? 'cards';
    $sectionTone = $data['section_tone'] ?? 'default';
    $items = collect($data['items'] ?? [])
        ->filter(fn (mixed $item): bool => is_string($item) && trim($item) !== '')
        ->map(function (string $item): array {
            [$value, $label] = array_pad(array_map('trim', explode('|', $item, 2)), 2, '');

            return [
                'value' => $value !== '' ? $value : $item,
                'label' => $label,
            ];
        })
        ->values();
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell site-section__shell site-section__shell--wide">
        <article class="site-stats{{ $sectionTone === 'accent' ? ' site-stats--accent' : '' }}">
            <div class="site-stats__intro">
                @if (! empty($data['eyebrow']))
                    <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="ui-title">{{ $data['title'] ?? $page->title }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            @if ($items->isNotEmpty())
                <div class="site-stats__items site-stats__items--{{ $displayStyle }}">
                    @foreach ($items as $item)
                        <article class="site-stats__item">
                            <strong>{{ $item['value'] }}</strong>
                            @if ($item['label'] !== '')
                                <span>{{ $item['label'] }}</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </div>
</section>
