<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Store a new lead from the public sales site.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Lead::create($validated);

        return redirect()->to(route('contact', [], false).'#kontakt-form')
            ->with('status', 'Tak. Din forespoergsel er sendt, og vi kan bruge den som udgangspunkt for dit tilbud.');
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'newsletter_email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->to(url()->previous().'#footer-newsletter')
                ->withErrors($validator, 'newsletterSignup')
                ->withInput();
        }

        $validated = $validator->validated();

        $email = Str::lower(trim($validated['newsletter_email']));

        $alreadySubscribed = Lead::query()
            ->where('email', $email)
            ->where('status', 'newsletter')
            ->exists();

        if (! $alreadySubscribed) {
            $displayName = Str::of(Str::before($email, '@'))
                ->replace(['.', '_', '-'], ' ')
                ->squish()
                ->title()
                ->value();

            Lead::create([
                'name' => $displayName !== '' ? $displayName : 'Nyhedsbrev',
                'email' => $email,
                'message' => 'Nyhedsbrevstilmelding fra sales-footer ('.$request->path().').',
                'status' => 'newsletter',
            ]);
        }

        return redirect()->to(url()->previous().'#footer-newsletter')
            ->with(
                'newsletter_status',
                $alreadySubscribed
                    ? 'Den email er allerede tilmeldt nyhedsbrevet.'
                    : 'Tak. Du er nu tilmeldt nyhedsbrevet.'
            );
    }
}
