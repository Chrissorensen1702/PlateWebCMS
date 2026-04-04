@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'ui-status']) }}>
        {{ $status }}
    </div>
@endif
