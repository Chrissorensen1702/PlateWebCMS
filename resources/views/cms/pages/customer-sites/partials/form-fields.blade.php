<div class="dashboard-create-site-form__details" x-data="{ customerType: @js(old('customer_type', 'company')) }">
    <div class="dashboard-create-site-form__type-switch">
        <div>
            <p class="ui-field__label">Kundetype</p>
            <p class="dashboard-create-site-form__type-copy">Vælg om vi opretter en virksomhed eller en privatperson.</p>
        </div>

        <div class="dashboard-create-site-form__type-toggle" role="tablist" aria-label="Kundetype">
            <input type="hidden" name="customer_type" x-model="customerType">

            <button
                type="button"
                class="dashboard-create-site-form__type-option"
                x-bind:class="{ 'dashboard-create-site-form__type-option--active': customerType === 'company' }"
                x-on:click="customerType = 'company'"
            >
                Virksomhed
            </button>

            <button
                type="button"
                class="dashboard-create-site-form__type-option"
                x-bind:class="{ 'dashboard-create-site-form__type-option--active': customerType === 'private' }"
                x-on:click="customerType = 'private'"
            >
                Privatperson
            </button>
        </div>
    </div>

    <div class="dashboard-create-site-form__grid">
        <label class="ui-field">
            <span class="ui-field__label" x-text="customerType === 'company' ? 'Virksomhedsnavn' : 'Navn'"></span>
            <input type="text" name="tenant_name" value="{{ old('tenant_name') }}" class="ui-field__control" required>
        </label>

        <label class="ui-field">
            <span class="ui-field__label" x-text="customerType === 'company' ? 'Firma-e-mail' : 'E-mail'"></span>
            <input type="email" name="company_email" value="{{ old('company_email') }}" class="ui-field__control">
        </label>

        <label class="ui-field" x-show="customerType === 'company'" x-cloak>
            <span class="ui-field__label">CVR</span>
            <input type="text" name="cvr_number" value="{{ old('cvr_number') }}" class="ui-field__control" placeholder="Valgfri">
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Telefon</span>
            <input type="text" name="phone" value="{{ old('phone') }}" class="ui-field__control">
        </label>

        <label class="ui-field">
            <span class="ui-field__label" x-text="customerType === 'company' ? 'Kontaktperson' : 'Fulde navn'"></span>
            <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="ui-field__control">
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Login-e-mail</span>
            <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="ui-field__control" required>
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Login-adgangskode</span>
            <input type="text" name="contact_password" value="{{ old('contact_password') }}" class="ui-field__control">
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Site-navn</span>
            <input type="text" name="site_name" value="{{ old('site_name') }}" class="ui-field__control" required>
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Site-slug</span>
            <input type="text" name="site_slug" value="{{ old('site_slug') }}" class="ui-field__control" placeholder="Valgfri - laves automatisk hvis tom">
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Tema</span>
            <select name="theme" class="ui-field__control">
                @foreach ($availableThemes as $themeKey => $theme)
                    <option value="{{ $themeKey }}" @selected(old('theme', 'base') === $themeKey)>{{ $theme['label'] }}</option>
                @endforeach
            </select>
        </label>

        <label class="ui-field">
            <span class="ui-field__label">Plan</span>
            <select name="plan_id" class="ui-field__control">
                <option value="">Ingen plan endnu</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected((string) old('plan_id') === (string) $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
        </label>
    </div>
</div>
