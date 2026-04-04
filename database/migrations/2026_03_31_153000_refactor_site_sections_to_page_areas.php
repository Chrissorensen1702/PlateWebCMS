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
        Schema::dropIfExists('site_page_area_fields');
        Schema::dropIfExists('site_page_areas');

        if (! Schema::hasColumn('site_pages', 'template_key')) {
            Schema::table('site_pages', function (Blueprint $table) {
                $table->string('template_key')->nullable()->after('title');
            });
        }

        Schema::create('site_page_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->string('area_key');
            $table->string('area_type');
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_page_id', 'area_key'], 'site_page_areas_page_key_unique');
            $table->index(['site_page_id', 'sort_order'], 'site_page_areas_page_sort_idx');
            $table->index('area_type', 'site_page_areas_type_idx');
        });

        Schema::create('site_page_area_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_area_id')->constrained('site_page_areas')->cascadeOnDelete();
            $table->string('field_key');
            $table->unsignedSmallInteger('position')->default(1);
            $table->text('value');
            $table->timestamps();

            $table->unique(['site_page_area_id', 'field_key', 'position'], 'site_page_area_fields_area_field_pos_unique');
            $table->index(['field_key', 'position'], 'site_page_area_fields_key_pos_idx');
        });

        $editorLabels = DB::table('site_section_fields')
            ->where('field_key', 'editor_label')
            ->orderBy('position')
            ->pluck('value', 'site_section_id');

        $sections = DB::table('site_sections')
            ->orderBy('site_page_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($sections as $section) {
            $areaId = DB::table('site_page_areas')->insertGetId([
                'site_page_id' => $section->site_page_id,
                'area_key' => $section->key,
                'area_type' => $section->type,
                'label' => $editorLabels[$section->id] ?? null,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ]);

            $fields = DB::table('site_section_fields')
                ->where('site_section_id', $section->id)
                ->where('field_key', '!=', 'editor_label')
                ->orderBy('field_key')
                ->orderBy('position')
                ->get();

            foreach ($fields as $field) {
                DB::table('site_page_area_fields')->insert([
                    'site_page_area_id' => $areaId,
                    'field_key' => $field->field_key,
                    'position' => $field->position,
                    'value' => $field->value,
                    'created_at' => $field->created_at,
                    'updated_at' => $field->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('site_section_fields');
        Schema::dropIfExists('site_sections');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('site_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_page_id')->constrained('site_pages')->cascadeOnDelete();
            $table->string('key');
            $table->string('type');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_page_id', 'key']);
        });

        Schema::create('site_section_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_section_id')->constrained('site_sections')->cascadeOnDelete();
            $table->string('field_key');
            $table->unsignedSmallInteger('position')->default(1);
            $table->text('value');
            $table->timestamps();

            $table->unique(['site_section_id', 'field_key', 'position']);
            $table->index(['field_key', 'position']);
        });

        $areas = DB::table('site_page_areas')
            ->orderBy('site_page_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($areas as $area) {
            $sectionId = DB::table('site_sections')->insertGetId([
                'site_page_id' => $area->site_page_id,
                'key' => $area->area_key,
                'type' => $area->area_type,
                'sort_order' => $area->sort_order,
                'is_active' => $area->is_active,
                'created_at' => $area->created_at,
                'updated_at' => $area->updated_at,
            ]);

            if ($area->label !== null && trim((string) $area->label) !== '') {
                DB::table('site_section_fields')->insert([
                    'site_section_id' => $sectionId,
                    'field_key' => 'editor_label',
                    'position' => 1,
                    'value' => trim((string) $area->label),
                    'created_at' => $area->created_at,
                    'updated_at' => $area->updated_at,
                ]);
            }

            $fields = DB::table('site_page_area_fields')
                ->where('site_page_area_id', $area->id)
                ->orderBy('field_key')
                ->orderBy('position')
                ->get();

            foreach ($fields as $field) {
                DB::table('site_section_fields')->insert([
                    'site_section_id' => $sectionId,
                    'field_key' => $field->field_key,
                    'position' => $field->position,
                    'value' => $field->value,
                    'created_at' => $field->created_at,
                    'updated_at' => $field->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('site_page_area_fields');
        Schema::dropIfExists('site_page_areas');

        if (Schema::hasColumn('site_pages', 'template_key')) {
            Schema::table('site_pages', function (Blueprint $table) {
                $table->dropColumn('template_key');
            });
        }
    }
};
