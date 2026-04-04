<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\ProjectFolderItem;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectManagementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user?->isDeveloper(), 403);

        $projectFolderItems = ProjectFolderItem::query()
            ->with([
                'tenant.users',
                'tenant.sites' => fn ($query) => $query->with('plan')->latest('updated_at'),
            ])
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->get();

        $selectedTenantIds = $projectFolderItems
            ->pluck('tenant_id')
            ->all();

        $tenants = Tenant::query()
            ->with([
                'users',
                'sites' => fn ($query) => $query->with('plan')->latest('updated_at'),
            ])
            ->withCount('sites')
            ->latest()
            ->get();

        return view('cms.pages.projects.index', [
            'tenants' => $tenants,
            'projectFolderItems' => $projectFolderItems,
            'selectedTenantIds' => $selectedTenantIds,
            'canManageProjects' => $user->canManageCustomers(),
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->canManageCustomers(), 403);

        ProjectFolderItem::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['sort_order' => ((int) ProjectFolderItem::query()->max('sort_order')) + 1]
        );

        return redirect()->route('cms.projects.index');
    }

    public function destroy(Request $request, ProjectFolderItem $projectFolderItem): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->canManageCustomers(), 403);

        $projectFolderItem->delete();

        return redirect()->route('cms.projects.index');
    }

    public function update(Request $request, ProjectFolderItem $projectFolderItem): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user?->canManageCustomers(), 403);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $notes = trim((string) ($validated['notes'] ?? ''));

        $projectFolderItem->update([
            'notes' => $notes !== '' ? $notes : null,
        ]);

        return redirect()->route('cms.projects.index');
    }
}
