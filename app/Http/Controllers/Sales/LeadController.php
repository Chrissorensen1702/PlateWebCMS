<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
