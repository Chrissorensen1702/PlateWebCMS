<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class TenantAccessController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->canManageTenantAccess() || $user->canManageDeveloperAccounts(), 403);

        $tenants = $this->manageableTenantsFor($user)
            ->orderBy('name')
            ->get()
            ->each(function (Tenant $tenant): void {
                $tenant->setRelation(
                    'users',
                    $tenant->users
                        ->sortBy(fn (User $member): string => sprintf(
                            '%d-%s',
                            match ($member->pivot?->role) {
                                'owner' => 0,
                                'editor' => 1,
                                default => 2,
                            },
                            Str::lower($member->name),
                        ))
                        ->values(),
                );
            });

        return view('cms.pages.access.index', [
            'tenants' => $tenants,
            'developerAccounts' => $user->canManageDeveloperAccounts()
                ? User::query()->where('role', 'developer')->orderBy('name')->get()
                : collect(),
            'developerAccessOptions' => User::developerAccessOptions(),
            'canManageDeveloperAccounts' => $user->canManageDeveloperAccounts(),
        ]);
    }

    public function storeDeveloper(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        abort_unless($currentUser->canManageDeveloperAccounts(), 403);

        $email = Str::lower(trim((string) $request->input('email')));
        $existingUser = $email !== '' ? User::query()->firstWhere('email', $email) : null;

        if ($existingUser && ! $existingUser->isDeveloper()) {
            return back()
                ->withErrors([
                    'email' => 'Emailen tilhoerer allerede en kunde-bruger. Brug en anden email til developer-kontoen.',
                ], 'developerAccess')
                ->withInput();
        }

        $validated = $request->validateWithBag('developerAccess', [
            'name' => [$existingUser ? 'nullable' : 'required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [$existingUser ? 'nullable' : 'required', Password::defaults()],
            'developer_access' => ['required', Rule::in(array_keys(User::developerAccessOptions()))],
            'employment_role' => ['nullable', 'string', 'max:120'],
        ]);

        if ($existingUser && $existingUser->is($currentUser) && $validated['developer_access'] !== User::DEVELOPER_ACCESS_FULL) {
            return back()
                ->withErrors([
                    'developer_access' => 'Du kan ikke nedgradere din egen developer-adgang herfra.',
                ], 'developerAccess')
                ->withInput();
        }

        $statusMessage = '';

        DB::transaction(function () use ($existingUser, $validated, $email, &$statusMessage): void {
            if ($existingUser) {
                $existingUser->forceFill([
                    'name' => trim((string) ($validated['name'] ?: $existingUser->name)),
                    'developer_access' => $validated['developer_access'],
                    'employment_role' => filled($validated['employment_role'] ?? null)
                        ? trim((string) $validated['employment_role'])
                        : null,
                ])->save();

                $statusMessage = "{$existingUser->name} har nu developer-adgangen {$existingUser->developerAccessLabel()}.";

                return;
            }

            $developer = User::query()->create([
                'name' => trim((string) $validated['name']),
                'email' => $email,
                'password' => $validated['password'],
                'role' => 'developer',
                'developer_access' => $validated['developer_access'],
                'employment_role' => filled($validated['employment_role'] ?? null)
                    ? trim((string) $validated['employment_role'])
                    : null,
                'email_verified_at' => now(),
            ]);

            $statusMessage = "Developer-kontoen {$developer->name} er oprettet med {$developer->developerAccessLabel()}.";
        });

        return redirect()
            ->route('cms.access.index')
            ->with('status', $statusMessage);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->canManageTenantAccess($tenant), 403);

        $email = Str::lower(trim((string) $request->input('email')));
        $existingUser = $email !== '' ? User::query()->firstWhere('email', $email) : null;
        $bag = "tenantAccess{$tenant->id}";

        $validated = $request->validateWithBag($bag, [
            'form_target' => ['nullable', 'string', 'max:255'],
            'name' => [$existingUser ? 'nullable' : 'required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [$existingUser ? 'nullable' : 'required', Password::defaults()],
            'tenant_role' => ['required', Rule::in(['owner', 'editor', 'viewer'])],
        ]);

        $tenantRole = $validated['tenant_role'];
        $memberName = trim((string) ($validated['name'] ?? ''));
        $statusMessage = '';

        DB::transaction(function () use ($tenant, $existingUser, $email, $memberName, $validated, $tenantRole, &$statusMessage): void {
            if ($existingUser) {
                $isExistingMember = $tenant->users()->whereKey($existingUser->id)->exists();

                if ($isExistingMember) {
                    $tenant->users()->updateExistingPivot($existingUser->id, ['role' => $tenantRole]);
                    $statusMessage = "{$existingUser->name} har nu rollen {$tenantRole} paa {$tenant->name}.";
                } else {
                    $tenant->users()->attach($existingUser->id, ['role' => $tenantRole]);
                    $statusMessage = "{$existingUser->name} er tilfoejet til {$tenant->name} som {$tenantRole}.";
                }

                return;
            }

            $member = User::query()->create([
                'name' => $memberName,
                'email' => $email,
                'password' => $validated['password'],
                'role' => 'client',
                'email_verified_at' => now(),
            ]);

            $tenant->users()->attach($member->id, ['role' => $tenantRole]);
            $statusMessage = "Sub-accounten {$member->name} er oprettet og tilknyttet som {$tenantRole}.";
        });

        return redirect()
            ->route('cms.access.index')
            ->with('status', $statusMessage);
    }

    private function manageableTenantsFor(User $user)
    {
        $tenantQuery = Tenant::query()
            ->withCount('sites')
            ->with([
                'sites' => fn ($query) => $query->orderBy('name'),
                'users' => fn ($query) => $query->orderBy('name'),
            ]);

        if (! $user->isDeveloper()) {
            $tenantQuery->whereHas('users', fn ($query) => $query
                ->whereKey($user->id)
                ->where('tenant_user.role', 'owner'));
        }

        return $tenantQuery;
    }
}
