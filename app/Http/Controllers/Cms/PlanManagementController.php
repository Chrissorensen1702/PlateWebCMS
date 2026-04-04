<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePlans(), 403);

        $plans = Plan::query()
            ->with('featureItems')
            ->withCount(['sites', 'leads'])
            ->ordered()
            ->get();

        return view('cms.pages.plans.index', [
            'plans' => $plans,
            'kindOptions' => $this->kindOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlans(), 403);

        $validated = $request->validateWithBag('createPlan', $this->rules());

        $plan = Plan::query()->create([
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug($validated['slug'] ?? null, $validated['name']),
            'kind' => $validated['kind'],
            'headline' => trim($validated['headline']),
            'summary' => trim($validated['summary']),
            'price_from' => $this->nullableInteger($validated['price_from'] ?? null),
            'build_time' => $this->nullableText($validated['build_time'] ?? null),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $plan->syncFeatures($this->featureLines($validated['features'] ?? null));

        return redirect()
            ->route('cms.plans.index')
            ->with('status', "Pakken '{$plan->name}' er oprettet.");
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlans(), 403);

        $validated = $request->validateWithBag($this->updateBag($plan), $this->rules($plan));

        $plan->update([
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug($validated['slug'] ?? null, $validated['name'], $plan),
            'kind' => $validated['kind'],
            'headline' => trim($validated['headline']),
            'summary' => trim($validated['summary']),
            'price_from' => $this->nullableInteger($validated['price_from'] ?? null),
            'build_time' => $this->nullableText($validated['build_time'] ?? null),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $plan->syncFeatures($this->featureLines($validated['features'] ?? null));

        return redirect()
            ->route('cms.plans.index')
            ->with('status', "Pakken '{$plan->name}' er opdateret.");
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Plan $plan = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'kind' => ['required', 'string', Rule::in(array_keys($this->kindOptions()))],
            'headline' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'price_from' => ['nullable', 'integer', 'min:0'],
            'build_time' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function kindOptions(): array
    {
        return [
            'template' => 'Template',
            'custom' => 'Custom',
        ];
    }

    private function updateBag(Plan $plan): string
    {
        return 'updatePlan'.$plan->id;
    }

    private function uniqueSlug(?string $explicitSlug, string $fallbackName, ?Plan $ignorePlan = null): string
    {
        $baseSlug = Str::slug(trim((string) ($explicitSlug ?: $fallbackName)));
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'pakke';
        $slug = $baseSlug;
        $suffix = 2;

        while (Plan::query()
            ->when($ignorePlan, fn ($query) => $query->whereKeyNot($ignorePlan->id))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return list<string>
     */
    private function featureLines(?string $value): array
    {
        return Collection::make(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function nullableText(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
