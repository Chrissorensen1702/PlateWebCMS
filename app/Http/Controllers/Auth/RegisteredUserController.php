<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sales\CustomerSolutionController;
use App\Models\Plan;
use App\Models\User;
use App\Support\Sales\PricingPackageCatalog;
use App\Support\Sites\SelfServiceWebsiteProvisioner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request, PricingPackageCatalog $pricingPackageCatalog): View|RedirectResponse
    {
        $pendingSelection = $request->session()->get(CustomerSolutionController::SESSION_KEY);

        if (! is_array($pendingSelection)) {
            return $this->redirectToPricingGuide();
        }

        $pendingSolution = null;

        $plans = Plan::query()
            ->active()
            ->ordered()
            ->with('featureItems')
            ->get();

        $pendingSolution = $pricingPackageCatalog->resolveSelection(
            $pricingPackageCatalog->packageMap($plans),
            $pendingSelection,
        );

        return view('cms.auth.register', [
            'pendingSolution' => $pendingSolution,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(
        Request $request,
        PricingPackageCatalog $pricingPackageCatalog,
        SelfServiceWebsiteProvisioner $selfServiceWebsiteProvisioner,
    ): RedirectResponse
    {
        $pendingSelection = $request->session()->get(CustomerSolutionController::SESSION_KEY);

        if (! is_array($pendingSelection)) {
            return $this->redirectToPricingGuide();
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:50'],
            'cvr_number' => ['nullable', 'string', 'max:32'],
            'registration_note' => ['nullable', 'string', 'max:2000'],
            'wants_callback' => ['nullable', 'boolean'],
            'accept_terms' => ['accepted'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $this->nullIfBlank($request->phone),
            'cvr_number' => $this->nullIfBlank($request->cvr_number),
            'registration_note' => $this->nullIfBlank($request->registration_note),
            'wants_callback' => $request->boolean('wants_callback'),
            'accepted_terms_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->forget(CustomerSolutionController::SESSION_KEY);

        $plans = Plan::query()
            ->active()
            ->ordered()
            ->with('featureItems')
            ->get();

        $normalizedSelection = $pricingPackageCatalog->normalizeSelection(
            $pendingSelection,
            $pricingPackageCatalog->packageMap($plans),
        );

        $solution = CustomerSolutionController::persistSolution($user, $normalizedSelection);
        $selfServiceWebsiteProvisioner->provisionForPricingSolution($user, $solution);

        return redirect(route('customer.solution.show', absolute: false));
    }

    private function nullIfBlank(?string $value): ?string
    {
        $trimmed = is_string($value) ? trim($value) : '';

        return $trimmed !== '' ? $trimmed : null;
    }

    private function redirectToPricingGuide(): RedirectResponse
    {
        return redirect()->to(route('templates').'#pricing-guide');
    }
}
