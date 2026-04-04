@php
    $tenant = $site->tenant;
    $primaryContact = $tenant?->primary_contact;
    $tenantMeta = collect([
        $tenant?->display_email,
        $tenant?->phone,
        $tenant?->cvr_number ? 'CVR ' . $tenant->cvr_number : null,
    ])->filter()->implode(' · ');
    $summaryStats = collect([
        ['label' => 'Theme', 'value' => $site->theme],
        ['label' => 'Plan', 'value' => $site->plan?->name ?? 'Ingen plan'],
        ['label' => 'Online', 'value' => $site->is_online ? 'Ja' : 'Nej', 'tone' => $site->is_online ? 'online' : 'offline'],
    ]);
    $summaryDetails = collect([
        $tenant?->name ? [
            'label' => 'Kunde',
            'title' => $tenant->name,
            'copy' => 'Dette website er knyttet til kunden i tenant-strukturen.',
        ] : null,
        $primaryContact?->name ? [
            'label' => 'Kontaktperson',
            'title' => $primaryContact->name,
            'copy' => $primaryContact->email ?: 'Ingen email tilfoejet endnu.',
        ] : null,
        $tenantMeta !== '' ? [
            'label' => 'Virksomhedsinfo',
            'title' => 'Kundeoplysninger',
            'copy' => $tenantMeta,
        ] : null,
    ])->filter()->values();
    $showBackButton = $showBackButton ?? true;
    $showPreviewButton = $showPreviewButton ?? true;
    $showPublishButton = $showPublishButton ?? true;
    $hasSummaryActions = $showBackButton || (! empty($saveFormId) && $canUpdateSite) || $showPreviewButton || ($showPublishButton && $canUpdateSite);
    $visibilityRedirectTo = $visibilityRedirectTo ?? url()->current();
    $siteRenameModalName = "site-rename-{$site->id}";
@endphp

<section class="ui-card site-editor-summary">
    <div class="site-editor-summary__layout">
        <div class="site-editor-summary__content">
            <p class="site-editor-summary__eyebrow">Website overview</p>
            <div class="site-editor-summary__title-row">
                <h3 class="site-editor-summary__title">{{ $site->name }}</h3>

                @if ($canUpdateSite)
                    <button
                        type="button"
                        class="site-editor-summary__rename-trigger"
                        x-data=""
                        x-on:click="$dispatch('open-modal', '{{ $siteRenameModalName }}')"
                    >
                        Aendre navn
                    </button>
                @endif
            </div>
            <p class="site-editor-summary__lede">
                Hold styr paa website, sider og globale indstillinger for {{ $tenant?->name ?? 'denne kunde' }} fra et samlet overblik.
            </p>

            @if ($summaryDetails->isNotEmpty())
                <div class="site-editor-summary__details">
                    @foreach ($summaryDetails as $detail)
                        <article class="site-editor-summary__detail">
                            <span class="site-editor-summary__detail-label">{{ $detail['label'] }}</span>
                            <strong class="site-editor-summary__detail-title">{{ $detail['title'] }}</strong>
                            <p class="site-editor-summary__detail-copy">{{ $detail['copy'] }}</p>
                        </article>
                    @endforeach
                </div>
            @endif

            @if (! $canUpdateSite)
                <p class="site-editor-summary__copy site-editor-summary__copy--notice">
                    Du har laeseadgang til dette tenant-site. Preview er aabent, men redigering er laast for dit login.
                </p>
            @endif
        </div>

        <aside class="site-editor-summary__stats">
            @foreach ($summaryStats as $stat)
                <article class="site-editor-summary__stat{{ ! empty($stat['tone']) ? ' site-editor-summary__stat--' . $stat['tone'] : '' }}">
                    <div class="site-editor-summary__stat-header">
                        <span class="site-editor-summary__stat-label">{{ $stat['label'] }}</span>

                        @if (($stat['label'] ?? null) === 'Online' && $canUpdateSite)
                            <form method="POST" action="{{ route('cms.sites.visibility.update', $site) }}" class="site-editor-summary__stat-toggle-form">
                                @csrf
                                <input type="hidden" name="redirect_to" value="{{ $visibilityRedirectTo }}">
                                <input type="hidden" name="is_online" value="{{ $site->is_online ? 0 : 1 }}">
                                <button
                                    type="submit"
                                    class="site-editor-summary__toggle{{ $site->is_online ? ' site-editor-summary__toggle--on' : '' }}"
                                    role="switch"
                                    aria-checked="{{ $site->is_online ? 'true' : 'false' }}"
                                    aria-label="{{ $site->is_online ? 'Saet website offline' : 'Saet website online' }}"
                                >
                                    <span class="site-editor-summary__toggle-track">
                                        <span class="site-editor-summary__toggle-thumb"></span>
                                    </span>
                                </button>
                            </form>
                        @endif
                    </div>

                    <strong class="site-editor-summary__stat-value">{{ $stat['value'] }}</strong>
                </article>
            @endforeach
        </aside>
    </div>

    @if ($hasSummaryActions)
        <div class="site-editor-summary__footer">
            <div class="site-editor-summary__actions">
                @if ($showBackButton)
                    <a href="{{ $backHref ?? route('cms.sites.index') }}" class="ui-button ui-button--outline">{{ $backLabel ?? 'Tilbage til sider' }}</a>
                @endif

                @if (! empty($saveFormId) && $canUpdateSite)
                    <button type="submit" form="{{ $saveFormId }}" class="ui-button ui-button--accent">
                        Gem kladde
                    </button>
                @endif

                @if ($showPreviewButton)
                    <a href="{{ route('sites.show', $site) }}" class="ui-button ui-button--ink">Se preview</a>
                @endif

                @if ($showPublishButton && $canUpdateSite)
                    @if (! empty($saveFormId))
                        <button
                            type="submit"
                            form="{{ $saveFormId }}"
                            name="publish_after_save"
                            value="1"
                            class="ui-button ui-button--success"
                        >
                            OFFENTLIGGOER
                        </button>
                    @else
                        <form method="POST" action="{{ route('cms.sites.publish', $site) }}">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $publishRedirectTo ?? url()->current() }}">
                            <button type="submit" class="ui-button ui-button--success">
                                OFFENTLIGGOER
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    @endif
</section>

@if ($canUpdateSite)
    <x-modal name="{{ $siteRenameModalName }}" :show="$errors->getBag('updateSite')->isNotEmpty()" maxWidth="lg" focusable>
        <div class="site-page-settings-modal site-page-settings-modal--compact">
            <div class="site-page-settings-modal__header">
                <div>
                    <p class="site-editor-main-card__eyebrow">Website</p>
                    <h3 class="site-editor-main-card__title">Aendre websitenavn</h3>
                    <p class="site-editor-main-card__copy">
                        Her kan du opdatere projektnavnet, som vises i CMS-overblikket.
                    </p>
                </div>

                <button
                    type="button"
                    class="ui-button ui-button--outline site-page-settings-modal__close"
                    x-on:click="$dispatch('close-modal', '{{ $siteRenameModalName }}')"
                >
                    Luk
                </button>
            </div>

            @php($siteUpdateErrors = $errors->getBag('updateSite'))

            <form method="POST" action="{{ route('cms.sites.update', $site) }}" class="site-page-draft-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                @if ($siteUpdateErrors->any())
                    <div class="site-page-form-card__errors site-page-form-card__errors--inline">
                        <p class="ui-copy">Der er lige et par felter vi skal have rettet:</p>
                        <ul class="ui-list">
                            @foreach ($siteUpdateErrors->all() as $error)
                                <li class="ui-list__item">
                                    <span class="ui-list__dot"></span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <label class="ui-field">
                    <span class="ui-field__label ui-field__label--with-help">
                        Websitenavn
                        <x-help-tooltip text="Det navn du ser i CMS'et, naar du arbejder med det valgte website." />
                    </span>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $site->name) }}"
                        class="ui-field__control"
                    >
                </label>

                <div class="site-page-settings-modal__actions">
                    <div class="site-page-settings-modal__submit">
                        <button type="submit" class="ui-button ui-button--ink">
                            Gem websitenavn
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>
@endif
