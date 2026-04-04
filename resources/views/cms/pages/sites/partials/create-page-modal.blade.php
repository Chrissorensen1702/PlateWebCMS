<x-modal name="{{ $modalName }}" :show="$errors->getBag('createPage')->isNotEmpty()" maxWidth="2xl" focusable>
    <div class="site-create-page-modal">
        @include('cms.pages.sites.partials.page-form', [
            'page' => null,
            'action' => route('cms.pages.store', $site),
            'submitLabel' => 'Opret side i kladde',
            'sortOrder' => $sortOrder,
            'availablePageTemplates' => $availablePageTemplates,
            'canUpdateSite' => $canUpdateSite,
        ])
    </div>
</x-modal>
