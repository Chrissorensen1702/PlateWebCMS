<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['plan_id', 'sort_order']);
        });

        Schema::create('site_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['site_id', 'is_primary']);
        });

        $plans = DB::table('plans')->select(['id', 'features'])->get();

        foreach ($plans as $plan) {
            foreach ($this->decodeJsonArray($plan->features) as $index => $feature) {
                DB::table('plan_features')->insert([
                    'plan_id' => $plan->id,
                    'label' => $feature,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $sites = DB::table('sites')
            ->whereNotNull('primary_domain')
            ->where('primary_domain', '!=', '')
            ->select(['id', 'primary_domain'])
            ->get();

        foreach ($sites as $site) {
            DB::table('site_domains')->insert([
                'site_id' => $site->id,
                'domain' => trim((string) $site->primary_domain),
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['is_custom', 'features']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('primary_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('build_time');
            $table->json('features')->nullable()->after('sort_order');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->string('primary_domain')->nullable()->after('status');
        });

        $planFeatures = DB::table('plan_features')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('plan_id');

        foreach ($planFeatures as $planId => $features) {
            DB::table('plans')
                ->where('id', $planId)
                ->update([
                    'is_custom' => DB::table('plans')->where('id', $planId)->value('kind') === 'custom',
                    'features' => json_encode($features->pluck('label')->values()->all()),
                ]);
        }

        $siteDomains = DB::table('site_domains')
            ->where('is_primary', true)
            ->orWhereRaw('id in (select min(id) from site_domains group by site_id)')
            ->orderBy('id')
            ->get()
            ->unique('site_id');

        foreach ($siteDomains as $domain) {
            DB::table('sites')
                ->where('id', $domain->site_id)
                ->update([
                    'primary_domain' => $domain->domain,
                ]);
        }

        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('plan_features');
    }

    /**
     * @return list<string>
     */
    private function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn (mixed $item): string => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->map(fn (mixed $item): string => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }
};
