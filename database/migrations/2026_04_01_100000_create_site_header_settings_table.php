<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_header_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('logo_alt')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_href')->nullable();
            $table->boolean('show_cta')->default(false);
            $table->timestamps();

            $table->unique('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_header_settings');
    }
};
