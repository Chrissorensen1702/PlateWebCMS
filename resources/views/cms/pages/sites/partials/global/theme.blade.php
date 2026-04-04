@php($themeErrors = $errors->getBag('updateSiteTheme'))
<section class="ui-card site-dashboard-panel" x-data="{ previewTheme: '{{ old('theme', $site->theme) }}' }">
    <div class="site-dashboard-panel__header">
        <div>
            <p class="site-dashboard-panel__eyebrow">Themevalg</p>
            <h3 class="site-dashboard-panel__title">Website-theme</h3>
            <p class="site-dashboard-panel__copy">Vælg det samlede visuelle udtryk for hele websitet. Themes er bevidst ret forskellige, så det giver mening at skifte mellem dem.</p>
        </div>

        <div class="site-dashboard-panel__header-actions">
            <a href="{{ route('cms.sites.show', $site) }}" class="ui-button ui-button--outline">
                Tilbage til dashboard
            </a>
        </div>
    </div>

    @if ($themeErrors->any())
        <div class="site-page-form-card__errors site-page-form-card__errors--inline">
            <p class="ui-copy">Der er lige et par theme-felter vi skal have rettet:</p>
            <ul class="ui-list">
                @foreach ($themeErrors->all() as $error)
                    <li class="ui-list__item">
                        <span class="ui-list__dot"></span>
                        <span>{{ $error }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="site-theme-workspace">
        <aside class="site-theme-sidebar">
            <div class="site-theme-sidebar__header">
                <span class="site-dashboard-panel__detail-label">Themes</span>
                <h4 class="site-theme-sidebar__title">Vælg nyt theme</h4>
            </div>

            <form method="POST" action="{{ route('cms.sites.theme.update', $site) }}" class="site-global-form site-theme-sidebar__form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                <fieldset @disabled(! $canUpdateSite)>
                    <div class="site-theme-choice-grid">
                        @foreach (($availableThemes ?? []) as $themeKey => $definition)
                            <label class="site-theme-choice site-theme-choice--{{ $themeKey }}{{ $site->theme === $themeKey ? ' site-theme-choice--active' : '' }}">
                                <input type="radio" name="theme" value="{{ $themeKey }}" x-model="previewTheme" {{ old('theme', $site->theme) === $themeKey ? 'checked' : '' }}>

                                <span class="site-theme-choice__body">
                                    <strong>{{ $definition['label'] }}</strong>
                                    <span class="site-theme-choice__meta">{{ $definition['vibe'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="site-dashboard-panel__actions site-dashboard-panel__actions--compact">
                    @if ($canUpdateSite)
                        <button type="submit" class="ui-button ui-button--ink">Gem themevalg</button>
                    @else
                        <p class="ui-copy">Denne tenant-rolle giver kun læseadgang til themevalget.</p>
                    @endif
                </div>
            </form>
        </aside>

        <article class="site-theme-preview-card">
            <div class="site-theme-preview-card__frame">
                <div class="site-theme-preview-card__header">
                    <span class="site-dashboard-panel__detail-label">Live preview af theme</span>
                </div>

                <div class="site-theme-preview-card__viewport">
                    <iframe
                        title="Theme preview"
                        class="site-theme-preview-card__iframe"
                        loading="lazy"
                        x-bind:src="'{{ route('sites.show', $site) }}?preview_theme=' + encodeURIComponent(previewTheme)"
                    ></iframe>
                </div>
            </div>
        </article>
    </div>
</section>
