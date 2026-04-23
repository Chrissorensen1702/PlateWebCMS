<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isDeveloper(), 403);

        $leads = Lead::query()
            ->with('plan')
            ->latest()
            ->get();

        $statusLabels = [
            'new' => 'Ny',
            'newsletter' => 'Nyhedsbrev',
            'contacted' => 'Kontaktet',
            'closed' => 'Lukket',
        ];

        return view('cms.pages.leads.index', [
            'leads' => $leads,
            'statusLabels' => $statusLabels,
        ]);
    }
}
