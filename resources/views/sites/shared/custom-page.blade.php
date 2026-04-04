@php($customLayoutMode = \App\Support\Sites\SitePageLayoutModes::normalize($page->layout_mode ?? \App\Support\Sites\SitePageLayoutModes::STRUCTURED))
@php($usesCustomLayout = \App\Support\Sites\SitePageLayoutModes::usesCustomCode($customLayoutMode) && filled($page->custom_html))

@if ($usesCustomLayout)
    @if (filled($page->custom_css))
        <style>
{!! $page->custom_css !!}
        </style>
    @endif

    <div class="site-custom-page{{ $customLayoutMode === \App\Support\Sites\SitePageLayoutModes::CUSTOM_FULL ? ' site-custom-page--full' : '' }}">
{!! $page->custom_html !!}
    </div>
@endif
