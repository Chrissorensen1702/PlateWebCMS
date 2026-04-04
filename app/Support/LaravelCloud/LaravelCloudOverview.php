<?php

namespace App\Support\LaravelCloud;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LaravelCloudOverview
{
    /**
     * @return array<string, mixed>
     */
    public function fetch(): array
    {
        return $this->fetchPanels()[0] ?? $this->notConfiguredPanel('Laravel Cloud', null, '24h');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchPanels(): array
    {
        $config = config('services.laravel_cloud', []);
        $projects = collect($config['projects'] ?? [])
            ->filter(fn ($project) => is_array($project))
            ->map(fn (array $project) => $this->normalizeProjectConfig($project))
            ->values();

        if ($projects->isEmpty()) {
            $projects = collect([
                $this->normalizeProjectConfig([
                    'label' => 'Laravel Cloud',
                    'token' => $config['token'] ?? null,
                    'environment_id' => $config['environment_id'] ?? null,
                    'dashboard_url' => $config['dashboard_url'] ?? null,
                    'metrics_period' => $config['metrics_period'] ?? '24h',
                ]),
            ]);
        }

        return $projects
            ->map(fn (array $project): array => $this->fetchPanel($project))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPanel(array $project): array
    {
        $label = (string) ($project['label'] ?? 'Laravel Cloud');
        $token = trim((string) ($project['token'] ?? ''));
        $environmentId = trim((string) ($project['environment_id'] ?? ''));
        $dashboardUrl = $this->normalizeDashboardUrl($project['dashboard_url'] ?? null);
        $period = (string) ($project['metrics_period'] ?? '24h');

        if ($token === '' || $environmentId === '') {
            return $this->notConfiguredPanel($label, $dashboardUrl, $period);
        }

        return Cache::remember(
            'dashboard.laravel_cloud.'.md5($label.'|'.$environmentId.'|'.$period),
            now()->addMinute(),
            fn (): array => $this->loadFromApi($label, $token, $environmentId, $period, $dashboardUrl),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function notConfiguredPanel(string $label, ?string $dashboardUrl, string $period): array
    {
        return [
            'panel_label' => $label,
            'configured' => false,
            'connected' => false,
            'dashboard_url' => $dashboardUrl,
            'title' => $label,
            'copy' => 'Tilføj API-token og environment-id for at vise deploys og driftsdata direkte i dashboardet.',
            'status_label' => 'Ikke forbundet',
            'status_tone' => 'neutral',
            'items' => [],
            'error' => null,
            'updated_at' => null,
            'metrics_period' => $period,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadFromApi(string $label, string $token, string $environmentId, string $period, ?string $dashboardUrl): array
    {
        try {
            $environmentResponse = $this->request($token)->get("/environments/{$environmentId}", [
                'include' => 'application,primaryDomain',
            ])->throw()->json();

            $deploymentsResponse = $this->request($token)->get("/environments/{$environmentId}/deployments")->throw()->json();

            $metricsResponse = $this->request($token)->get("/environments/{$environmentId}/metrics", [
                'period' => $period,
            ])->throw()->json();

            $environment = $this->parseEnvironment($environmentResponse, $deploymentsResponse);
            $metrics = $this->parseMetrics($metricsResponse, $period);

            return [
                'panel_label' => $label,
                'configured' => true,
                'connected' => true,
                'dashboard_url' => $dashboardUrl,
                'title' => $environment['title'],
                'copy' => $environment['copy'],
                'status_label' => $environment['status_label'],
                'status_tone' => $environment['status_tone'],
                'items' => array_merge($environment['items'], $metrics['items']),
                'error' => null,
                'updated_at' => $metrics['updated_at'] ?? $environment['updated_at'],
                'metrics_period' => $period,
            ];
        } catch (Throwable $exception) {
            Log::warning('Laravel Cloud dashboard panel could not be loaded.', [
                'label' => $label,
                'error' => $exception->getMessage(),
            ]);

            return [
                'panel_label' => $label,
                'configured' => true,
                'connected' => false,
                'dashboard_url' => $dashboardUrl,
                'title' => $label,
                'copy' => 'Panelet kunne ikke hente seneste deploy- og miljødata lige nu. Tjek token, environment-id eller netværksadgang.',
                'status_label' => 'Forbindelsesfejl',
                'status_tone' => 'danger',
                'items' => [],
                'error' => 'Forbindelsesfejl',
                'updated_at' => null,
                'metrics_period' => $period,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $project
     * @return array<string, mixed>
     */
    private function normalizeProjectConfig(array $project): array
    {
        return [
            'label' => trim((string) ($project['label'] ?? 'Laravel Cloud')) ?: 'Laravel Cloud',
            'token' => $project['token'] ?? null,
            'environment_id' => $project['environment_id'] ?? null,
            'dashboard_url' => $project['dashboard_url'] ?? null,
            'metrics_period' => (string) ($project['metrics_period'] ?? '24h'),
        ];
    }

    /**
     * @param  array<string, mixed>  $environmentResponse
     * @param  array<string, mixed>  $deploymentsResponse
     * @return array<string, mixed>
     */
    private function parseEnvironment(array $environmentResponse, array $deploymentsResponse): array
    {
        $environment = (array) ($environmentResponse['data'] ?? []);
        $environmentAttributes = (array) ($environment['attributes'] ?? []);
        $included = collect($environmentResponse['included'] ?? []);

        $applicationName = data_get($included->firstWhere('type', 'applications'), 'attributes.name');
        $environmentName = Arr::get($environmentAttributes, 'name') ?: 'Laravel Cloud miljø';
        $environmentStatus = Arr::get($environmentAttributes, 'status') ?: 'ukendt';
        $primaryDomain = data_get($included->firstWhere('type', 'domains'), 'attributes.domain')
            ?: Arr::get($environmentAttributes, 'vanity_domain');

        $deployment = collect($deploymentsResponse['data'] ?? [])
            ->sortByDesc(fn ($item) => data_get($item, 'attributes.finished_at') ?: data_get($item, 'attributes.started_at'))
            ->values()
            ->first();

        $deploymentAttributes = (array) data_get($deployment, 'attributes', []);
        $deploymentStatus = Arr::get($deploymentAttributes, 'status');
        $commitHash = Arr::get($deploymentAttributes, 'commit_hash');
        $startedAt = $this->parseDate(Arr::get($deploymentAttributes, 'started_at'));
        $finishedAt = $this->parseDate(Arr::get($deploymentAttributes, 'finished_at'));
        $deployTime = $finishedAt ?? $startedAt;

        $title = $applicationName
            ? "{$applicationName} - {$environmentName}"
            : (string) $environmentName;

        $copyParts = array_filter([
            $this->humanStatus($environmentStatus),
            $primaryDomain ? "Domæne: {$primaryDomain}" : null,
            $deployTime ? 'Seneste deploy '.Str::lower($deployTime->diffForHumans()) : null,
        ]);

        return [
            'title' => $title,
            'copy' => implode(' · ', $copyParts),
            'status_label' => $this->humanStatus($environmentStatus),
            'status_tone' => $this->statusTone($environmentStatus),
            'updated_at' => $deployTime?->toIso8601String(),
            'items' => [
                [
                    'label' => 'Seneste deploy',
                    'value' => $deploymentStatus ? $this->humanStatus($deploymentStatus) : 'Ingen deploy fundet',
                    'meta' => $deployTime ? $deployTime->format('d.m.Y H:i') : 'Ikke tilgængelig',
                ],
                [
                    'label' => 'Commit',
                    'value' => $commitHash ? Str::upper(Str::substr($commitHash, 0, 7)) : 'Ukendt',
                    'meta' => Arr::get($deploymentAttributes, 'branch_name') ?: 'Ingen branch angivet',
                ],
                [
                    'label' => 'Miljø',
                    'value' => (string) $environmentName,
                    'meta' => $primaryDomain ?: 'Intet primært domæne endnu',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $metricsResponse
     * @return array<string, mixed>
     */
    private function parseMetrics(array $metricsResponse, string $period): array
    {
        $metrics = (array) ($metricsResponse['data'] ?? []);
        $resolvedPeriod = (string) ($metricsResponse['meta']['period'] ?? $period);

        return [
            'updated_at' => now()->toIso8601String(),
            'items' => [
                [
                    'label' => 'CPU',
                    'value' => $this->formatPercentMetric($metrics, 'cpu_usage'),
                    'meta' => "Gennemsnit {$resolvedPeriod}",
                ],
                [
                    'label' => 'Memory',
                    'value' => $this->formatMemoryMetric($metrics, 'memory_usage'),
                    'meta' => "Gennemsnit {$resolvedPeriod}",
                ],
                [
                    'label' => 'HTTP svar',
                    'value' => $this->formatWholeMetric($metrics, 'http_response_count'),
                    'meta' => "Gennemsnit {$resolvedPeriod}",
                ],
                [
                    'label' => 'Replicas',
                    'value' => $this->formatWholeMetric($metrics, 'replica_count'),
                    'meta' => "Gennemsnit {$resolvedPeriod}",
                ],
            ],
        ];
    }

    private function request(string $token)
    {
        return Http::baseUrl('https://cloud.laravel.com/api')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->withToken($token);
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    private function metricAverage(array $metrics, string $key, bool $sumSeries = false): ?float
    {
        $average = data_get($metrics, "{$key}.average");

        if (is_numeric($average)) {
            return (float) $average;
        }

        if (is_array($average)) {
            $values = collect($average)
                ->filter(fn ($value) => is_numeric($value))
                ->map(fn ($value) => (float) $value)
                ->values();

            if ($values->isEmpty()) {
                return null;
            }

            if ($sumSeries && $values->count() > 1) {
                return $values->sum();
            }

            return $values->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    private function formatPercentMetric(array $metrics, string $key): string
    {
        $value = $this->metricAverage($metrics, $key);

        if ($value === null) {
            return 'Ikke tilgængelig';
        }

        return number_format($value, $value >= 10 ? 0 : 1, ',', '.').' %';
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    private function formatWholeMetric(array $metrics, string $key): string
    {
        $value = $this->metricAverage($metrics, $key, true);

        if ($value === null) {
            return 'Ikke tilgængelig';
        }

        return number_format($value, 0, ',', '.');
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    private function formatMemoryMetric(array $metrics, string $key): string
    {
        $value = $this->metricAverage($metrics, $key);

        if ($value === null) {
            return 'Ikke tilgængelig';
        }

        if ($value >= 1024 * 1024) {
            $megabytes = $value / 1024 / 1024;

            if ($megabytes >= 1024) {
                return number_format($megabytes / 1024, 1, ',', '.').' GB';
            }

            return number_format($megabytes, $megabytes >= 100 ? 0 : 1, ',', '.').' MB';
        }

        if ($value >= 1024) {
            return number_format($value / 1024, 1, ',', '.').' GB';
        }

        return number_format($value, 0, ',', '.').' MB';
    }

    private function parseDate(mixed $value): ?CarbonInterface
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function humanStatus(?string $status): string
    {
        if (! filled($status)) {
            return 'Ukendt status';
        }

        $normalized = Str::of((string) $status)
            ->trim()
            ->lower()
            ->replace('_', '-')
            ->value();

        if (Str::startsWith($normalized, 'deployment.')) {
            $normalized = Str::after($normalized, 'deployment.');
        }

        return match ($normalized) {
            'active' => 'Aktivt miljø',
            'deploying' => 'Deploy i gang',
            'ready' => 'Klar',
            'running' => 'Kører',
            'pending' => 'Afventer',
            'failed' => 'Fejlede',
            'succeeded' => 'Gennemført',
            'successful' => 'Gennemført',
            'stopped' => 'Stoppet',
            default => Str::headline((string) $status),
        };
    }

    private function statusTone(?string $status): string
    {
        if (! filled($status)) {
            return 'neutral';
        }

        $normalized = Str::of((string) $status)
            ->trim()
            ->lower()
            ->replace('_', '-')
            ->value();

        if (Str::startsWith($normalized, 'deployment.')) {
            $normalized = Str::after($normalized, 'deployment.');
        }

        return match ($normalized) {
            'active', 'running', 'ready', 'successful', 'succeeded' => 'success',
            'deploying', 'pending' => 'warning',
            'failed', 'stopped' => 'danger',
            default => 'neutral',
        };
    }

    private function normalizeDashboardUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
