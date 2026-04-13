@extends('sites.shared.layout')

@section('title', $page->title)

@section('content')
    @php
        $header = $site->headerSettings;
        $bookingInHeader = $site->usesBookingInHeader();
        $bookingUrl = $site->resolvedBookingUrl();
        $bookingTarget = $bookingInHeader && $site->bookingShouldOpenInNewTab() ? '_blank' : null;
        $pageLayoutMode = \App\Support\Sites\SitePageLayoutModes::normalize($page->layout_mode ?? \App\Support\Sites\SitePageLayoutModes::STRUCTURED);
        $usesCustomMain = \App\Support\Sites\SitePageLayoutModes::usesCustomMain($pageLayoutMode) && filled($page->custom_html);
        $usesCustomFull = \App\Support\Sites\SitePageLayoutModes::usesCustomFull($pageLayoutMode) && filled($page->custom_html);
        $showBrandName = $header ? (bool) ($header->show_brand_name ?? true) : true;
        $brandName = $showBrandName ? ($header?->brand_name ?: $site->name) : null;
        $logoUrl = $header?->logo_url;
        $logoAlt = $header?->logo_alt ?: ($brandName ?: $site->name);
        $headerBackgroundStyle = \App\Models\SiteHeaderSetting::normalizeBackgroundStyle($header?->background_style);
        $headerTextColorStyle = \App\Models\SiteHeaderSetting::normalizeTextColorStyle($header?->text_color_style);
        $headerShadowStyle = \App\Models\SiteHeaderSetting::normalizeShadowStyle($header?->shadow_style);
        $headerStickyMode = \App\Models\SiteHeaderSetting::normalizeStickyMode($header?->sticky_mode);
        $headerClasses = collect([
            'site-theme-header',
            $headerBackgroundStyle !== \App\Models\SiteHeaderSetting::BACKGROUND_AUTO ? "site-theme-header--bg-{$headerBackgroundStyle}" : null,
            $headerTextColorStyle !== \App\Models\SiteHeaderSetting::TEXT_AUTO ? "site-theme-header--text-{$headerTextColorStyle}" : null,
            $headerShadowStyle !== \App\Models\SiteHeaderSetting::SHADOW_AUTO ? "site-theme-header--shadow-{$headerShadowStyle}" : null,
            $headerStickyMode !== \App\Models\SiteHeaderSetting::STICKY_AUTO ? "site-theme-header--mode-{$headerStickyMode}" : null,
        ])->filter()->implode(' ');
        $showHeaderCta = $bookingInHeader || (bool) ($header?->show_cta ?? false);
        $headerCtaLabel = $bookingInHeader
            ? $site->resolvedBookingCtaLabel($header?->cta_label ?: 'Book tid')
            : $header?->cta_label;
        $headerCtaHref = $bookingInHeader
            ? $bookingUrl
            : \App\Support\Http\PublicSiteUrl::sanitize($header?->cta_href);
    @endphp

    @if ($usesCustomFull)
        @include('sites.shared.custom-page', ['page' => $page])
    @else
        <header class="{{ $headerClasses }}">
            <div class="ui-shell site-theme-header__inner">
                <a href="{{ route('sites.show', $site) }}" class="site-theme-brand">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}" class="site-theme-brand__logo">
                    @else
                        <span class="site-theme-brand__dot"></span>
                    @endif

                    @if ($brandName)
                        <span>{{ $brandName }}</span>
                    @endif
                </a>

                <div class="site-theme-header__actions">
                    @if ($navigation->isNotEmpty())
                        <nav class="site-theme-nav">
                            @foreach ($navigation as $navPage)
                                @php
                                    $isCurrentPage = $navPage->is($page);
                                    $href = $navPage->is_home
                                        ? route('sites.show', $site)
                                        : route('sites.page', [$site, $navPage->slug]);
                                @endphp

                                <a href="{{ $href }}" class="site-theme-nav__link{{ $isCurrentPage ? ' site-theme-nav__link--active' : '' }}">
                                    {{ $navPage->name }}
                                </a>
                            @endforeach
                        </nav>
                    @endif

                    @if ($showHeaderCta && $headerCtaLabel && $headerCtaHref)
                        <a href="{{ $headerCtaHref }}" class="ui-button ui-button--light" @if($bookingTarget) target="{{ $bookingTarget }}" rel="noreferrer" @endif>
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
