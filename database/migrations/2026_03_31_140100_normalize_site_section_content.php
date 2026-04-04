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

        $sections = DB::table('site_sections')->select(['id', 'data'])->get();

        foreach ($sections as $section) {
            foreach ($this->decodePayload($section->data) as $fieldKey => $value) {
                $values = is_array($value) ? array_values($value) : [$value];

                foreach ($values as $index => $item) {
                    $stringValue = trim((string) $item);

                    if ($stringValue === '') {
                        continue;
                    }

                    DB::table('site_section_fields')->insert([
                        'site_section_id' => $section->id,
                        'field_key' => $fieldKey,
                        'position' => $index + 1,
                        'value' => $stringValue,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Schema::table('site_sections', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_sections', function (Blueprint $table) {
            $table->json('data')->nullable()->after('type');
        });

        $sections = DB::table('site_sections')->select('id')->get();

        foreach ($sections as $section) {
            $payload = DB::table('site_section_fields')
                ->where('site_section_id', $section->id)
                ->orderBy('field_key')
                ->orderBy('position')
                ->get()
                ->groupBy('field_key')
                ->map(function ($group) {
                    $values = $group->pluck('value')->values()->all();

                    return count($values) === 1 ? $values[0] : $values;
                })
                ->all();

            DB::table('site_sections')
                ->where('id', $section->id)
                ->update([
                    'data' => empty($payload) ? null : json_encode($payload),
                ]);
        }

        Schema::dropIfExists('site_section_fields');
    }

    /**
     * @return array<string, string|list<string>>
     */
    private function decodePayload(mixed $value): array
    {
        if (is_array($value)) {
            return $this->normalizePayload($value);
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return $this->normalizePayload($decoded);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string|list<string>>
     */
    private function normalizePayload(array $payload): array
    {
        return collect($payload)
            ->map(function (mixed $value): string|array {
                if (is_array($value)) {
                    return collect($value)
                        ->map(fn (mixed $item): string => trim((string) $item))
                        ->filter()
                        ->values()
                        ->all();
                }

                return trim((string) $value);
            })
            ->filter(fn (mixed $value): bool => is_array($value) ? $value !== [] : $value !== '')
            ->all();
    }
};
