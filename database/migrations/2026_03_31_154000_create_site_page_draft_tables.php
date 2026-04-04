<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            if (! Schema::hasColumn('sites', 'draft_initialized_at')) {
                $table->timestamp('draft_initialized_at')->nullable()->after('launched_at');
            }

            if (! Schema::hasColumn('sites', 'last_published_at')) {
                $table->timestamp('last_published_at')->nullable()->after('draft_initialized_at');
            }
        });

        Schema::create('site_page_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_page_id')->nullable()->constrained('site_pages')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('title');
            $table->string('template_key')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_home')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'slug'], 'site_page_drafts_site_slug_unique');
            $table->unique('source_page_id', 'site_page_drafts_source_unique');
            $table->index(['site_id', 'sort_order'], 'site_page_drafts_site_sort_idx');
        });

        Schema::create('site_page_draft_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_draft_id')->constrained('site_page_drafts')->cascadeOnDelete();
            $table->foreignId('source_area_id')->nullable()->constrained('site_page_areas')->nullOnDelete();
            $table->string('area_key');
            $table->string('area_type');
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_page_draft_id', 'area_key'], 'site_page_draft_areas_page_key_unique');
            $table->unique('source_area_id', 'site_page_draft_areas_source_unique');
            $table->index(['site_page_draft_id', 'sort_order'], 'site_page_draft_areas_page_sort_idx');
        });

        Schema::create('site_page_draft_area_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_draft_area_id')->constrained('site_page_draft_areas')->cascadeOnDelete();
            $table->string('field_key');
            $table->unsignedSmallInteger('position')->default(1);
            $table->text('value');
            $table->timestamps();

            $table->unique(['site_page_draft_area_id', 'field_key', 'position'], 'site_page_draft_area_fields_area_field_pos_unique');
            $table->index(['field_key', 'position'], 'site_page_draft_area_fields_key_pos_idx');
        });

        $pages = DB::table('site_pages')
            ->orderBy('site_id')
            ->orderByDesc('is_home')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($pages as $page) {
            $draftPageId = DB::table('site_page_drafts')->insertGetId([
                'site_id' => $page->site_id,
                'source_page_id' => $page->id,
                'name' => $page->name,
                'slug' => $page->slug,
                'title' => $page->title,
                'template_key' => $page->template_key,
                'meta_description' => $page->meta_description,
                'is_home' => $page->is_home,
                'is_published' => $page->is_published,
                'sort_order' => $page->sort_order,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ]);

            $areas = DB::table('site_page_areas')
                ->where('site_page_id', $page->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($areas as $area) {
                $draftAreaId = DB::table('site_page_draft_areas')->insertGetId([
                    'site_page_draft_id' => $draftPageId,
                    'source_area_id' => $area->id,
                    'area_key' => $area->area_key,
                    'area_type' => $area->area_type,
                    'label' => $area->label,
                    'sort_order' => $area->sort_order,
                    'is_active' => $area->is_active,
                    'created_at' => $area->created_at,
                    'updated_at' => $area->updated_at,
                ]);

                $fields = DB::table('site_page_area_fields')
                    ->where('site_page_area_id', $area->id)
                    ->orderBy('field_key')
                    ->orderBy('position')
                    ->get();

                foreach ($fields as $field) {
                    DB::table('site_page_draft_area_fields')->insert([
                        'site_page_draft_area_id' => $draftAreaId,
                        'field_key' => $field->field_key,
                        'position' => $field->position,
                        'value' => $field->value,
                        'created_at' => $field->created_at,
                        'updated_at' => $field->updated_at,
                    ]);
                }
            }
        }

        DB::table('sites')->update([
            'draft_initialized_at' => now(),
            'last_published_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_page_draft_area_fields');
        Schema::dropIfExists('site_page_draft_areas');
        Schema::dropIfExists('site_page_drafts');

        Schema::table('sites', function (Blueprint $table) {
            if (Schema::hasColumn('sites', 'last_published_at')) {
                $table->dropColumn('last_published_at');
            }

            if (Schema::hasColumn('sites', 'draft_initialized_at')) {
                $table->dropColumn('draft_initialized_at');
            }
        });
    }
};
