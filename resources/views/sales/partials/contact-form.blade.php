<form id="kontakt-form" method="POST" action="{{ route('leads.store') }}" class="ui-card contact-form">
    @csrf

    <div class="ui-field-grid ui-field-grid--two">
        <div class="ui-field">
            <label for="name" class="ui-field__label">Navn</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" class="ui-field__control" required>
            @error('name')
                <p class="ui-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ui-field">
            <label for="email" class="ui-field__label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="ui-field__control" required>
            @error('email')
                <p class="ui-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="ui-field">
            <label for="company" class="ui-field__label">Virksomhed</label>
            <input id="company" name="company" type="text" value="{{ old('company') }}" class="ui-field__control">
        </div>

        <div class="ui-field">
            <label for="phone" class="ui-field__label">Telefon</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" class="ui-field__control">
        </div>
    </div>

    <div class="ui-field">
        <label for="plan_id" class="ui-field__label">Interesse</label>
        <select id="plan_id" name="plan_id" class="ui-field__control">
            <option value="">Vaelg en loesning</option>
            @foreach ($plans as $plan)
                <option value="{{ $plan->id }}" @selected(old('plan_id', $selectedPlanId ?? null) == $plan->id)>{{ $plan->name }}</option>
            @endforeach
        </select>
        @error('plan_id')
            <p class="ui-field__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="ui-field">
        <label for="message" class="ui-field__label">Projektbeskrivelse</label>
        <textarea id="message" name="message" rows="5" class="ui-field__control ui-field__control--textarea" required>{{ old('message', $defaultLeadMessage ?? '') }}</textarea>
        @error('message')
            <p class="ui-field__error">{{ $message }}</p>
        @enderror
    </div>

    <div class="ui-form-actions">
        <p class="contact-form__note">
            Brug formularen til at starte en gratis proeveperiode eller faa et vejledende tilbud bekraeftet, foer noget bliver endeligt.
        </p>

        <button type="submit" class="ui-button ui-button--accent">
            Start dialogen
        </button>
    </div>
</form>
