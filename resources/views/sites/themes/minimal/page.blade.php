@extends('sites.shared.layout')

@section('title', $page->title)

@section('content')
    @php
        $header = $site->headerSettings;
        $pageLayoutMode = \App\Support\Sites\SitePageLayoutModes::normalize($page->layout_mode ?? \App\Support\Sites\SitePageLayoutModes::STRUCTURED);
        $usesCustomMain = \App\Support\Sites\SitePageLayoutModes::usesCustomMain($pageLayoutMode) && filled($page->custom_html);
        $usesCustomFull = \App\Support\Sites\SitePageLayoutModes::usesCustomFull($pageLayoutMode) && filled($page->custom_html);
        $showBrandName = $header ? (bool) ($header->show_brand_name ?? true) : true;
        $showTagline = $header ? (bool) ($header->show_tagline ?? true) : true;
        $brandName = $showBrandName ? ($header?->brand_name ?: $site->name) : null;
        $tagline = $showTagline ? ($header?->tagline ?: 'Calm digital presence') : null;
        $logoUrl = $header?->logo_url;
        $logoAlt = $header?->logo_alt ?: ($brandName ?: $site->name);
        $showHeaderCta = (bool) ($header?->show_cta ?? false);
        $headerCtaLabel = $header?->cta_label;
        $headerCtaHref = \App\Support\Http\PublicSiteUrl::sanitize($header?->cta_href);
    @endphp

    @if ($usesCustomFull)
        @include('sites.shared.custom-page', ['page' => $page])
    @else
        <header class="site-theme-header">
            <div class="ui-shell minimal-header">
                <a href="{{ route('sites.show', $site) }}" class="minimal-brand">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}" class="minimal-brand__logo">
                    @else
                        <span class="minimal-brand__mark"></span>
                    @endif

                    @if ($brandName || $tagline)
                        <span class="minimal-brand__copy">
                            @if ($brandName)
                                <strong>{{ $brandName }}</strong>
                            @endif

                            @if ($tagline)
                                <small>{{ $tagline }}</small>
                            @endif
                        </span>
                    @endif
                </a>

                <div class="minimal-header__actions">
                    @if ($navigation->isNotEmpty())
                        <nav class="minimal-nav">
                            @foreach ($navigation as $navPage)
                                @php
                                    $isCurrentPage = $navPage->is($page);
                                    $href = $navPage->is_home
                                        ? route('sites.show', $site)
                                        : route('sites.page', [$site, $navPage->slug]);
                                @endphp

                                <a href="{{ $href }}" class="minimal-nav__link{{ $isCurrentPage ? ' minimal-nav__link--active' : '' }}">
                                    {{ $navPage->name }}
                                </a>
                            @endforeach
                        </nav>
                    @endif

                    @if ($showHeaderCta && $headerCtaLabel && $headerCtaHref)
                        <a href="{{ $headerCtaHref }}" class="ui-button ui-button--ink">
                            {{ $headerCtaLabel }}
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="site-theme-main">
            @if ($usesCustomMain)
                @include('sites.shared.custom-page', ['page' => $page])
            @else
                @forelse ($page->areas as $area)
                    @php
                        $areaView = view()->exists("sites.themes.{$theme}.areas.{$area->area_type}")
                            ? "sites.themes.{$theme}.areas.{$area->area_type}"
                            : (
                                view()->exists("sites.themes.base.areas.{$area->area_type}")
                                    ? "sites.themes.base.areas.{$area->area_type}"
                                    : 'sites.themes.base.areas.fallback'
                            );
                    @endphp

                    @include($areaView, ['area' => $area, 'site' => $site, 'page' => $page])
                @empty
                    @include('sites.themes.base.areas.fallback', ['area' => null, 'site' => $site, 'page' => $page])
                @endforelse
            @endif
        </main>

        @include('sites.shared.footer', ['site' => $site, 'navigation' => $navigation])
    @endif

@endsection
