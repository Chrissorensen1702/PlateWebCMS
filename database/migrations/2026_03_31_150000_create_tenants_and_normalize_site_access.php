<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('viewer');
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('plan_id')->constrained()->nullOnDelete();
        });

        $sites = DB::table('sites')
            ->select(['id', 'owner_id', 'name', 'slug'])
            ->orderBy('id')
            ->get();

        $tenantsByOwner = [];

        foreach ($sites as $site) {
            $tenantId = null;

            if ($site->owner_id !== null) {
                if (! array_key_exists($site->owner_id, $tenantsByOwner)) {
                    $user = DB::table('users')
                        ->where('id', $site->owner_id)
                        ->first(['id', 'name']);

                    $tenantId = DB::table('tenants')->insertGetId([
                        'name' => $user?->name ?: $site->name,
                        'slug' => $this->uniqueTenantSlug((string) ($user?->name ?: $site->slug ?: $site->name)),
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('tenant_user')->insert([
                        'tenant_id' => $tenantId,
                        'user_id' => $site->owner_id,
                        'role' => 'owner',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $tenantsByOwner[$site->owner_id] = $tenantId;
                }

                $tenantId = $tenantsByOwner[$site->owner_id];
            }

            if ($tenantId === null) {
                $tenantId = DB::table('tenants')->insertGetId([
                    'name' => $site->name,
                    'slug' => $this->uniqueTenantSlug((string) ($site->slug ?: $site->name)),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('sites')
                ->where('id', $site->id)
                ->update([
                    'tenant_id' => $tenantId,
                ]);
        }

        Schema::table('sites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('plan_id')->constrained('users')->nullOnDelete();
        });

        $sites = DB::table('sites')
            ->select(['id', 'tenant_id'])
            ->get();

        foreach ($sites as $site) {
            $ownerId = null;

            if ($site->tenant_id !== null) {
                $ownerId = DB::table('tenant_user')
                    ->where('tenant_id', $site->tenant_id)
                    ->orderByRaw("case role when 'owner' then 0 when 'editor' then 1 else 2 end")
                    ->value('user_id');
            }

            DB::table('sites')
                ->where('id', $site->id)
                ->update([
                    'owner_id' => $ownerId,
                ]);
        }

        Schema::table('sites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('tenants');
    }

    private function uniqueTenantSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'tenant';
        $slug = $baseSlug;
        $suffix = 2;

        while (DB::table('tenants')->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
};
