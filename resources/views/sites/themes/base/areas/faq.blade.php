@php
    $data = $area->data ?? [];
    $layoutStyle = $data['layout_style'] ?? 'stacked';
    $sectionTone = $data['section_tone'] ?? 'default';
    $items = collect($data['items'] ?? [])
        ->filter(fn (mixed $item): bool => is_string($item) && trim($item) !== '')
        ->map(function (string $item): array {
            [$question, $answer] = array_pad(array_map('trim', explode('|', $item, 2)), 2, '');

            return [
                'question' => $question !== '' ? $question : $item,
                'answer' => $answer,
            ];
        })
        ->values();
@endphp

<section id="{{ $area->area_key }}" class="site-section">
    <div class="ui-shell site-section__shell site-section__shell--wide">
        <article class="site-faq{{ $sectionTone === 'accent' ? ' site-faq--accent' : '' }}">
            <div class="site-faq__intro">
                @if (! empty($data['eyebrow']))
                    <p class="section-heading__kicker">{{ $data['eyebrow'] }}</p>
                @endif

                <h2 class="ui-title">{{ $data['title'] ?? $page->title }}</h2>

                @if (! empty($data['copy']))
                    <p class="ui-copy">{{ $data['copy'] }}</p>
                @endif
            </div>

            @if ($items->isNotEmpty())
                <div class="site-faq__items site-faq__items--{{ $layoutStyle }}">
                    @foreach ($items as $item)
                        <article class="site-faq__item">
                            <h3>{{ $item['question'] }}</h3>
                            @if ($item['answer'] !== '')
                                <p>{{ $item['answer'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </div>
</section>
