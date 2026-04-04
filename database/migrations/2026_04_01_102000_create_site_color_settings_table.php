<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_color_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('palette_key')->default('harbor');
            $table->timestamps();

            $table->unique('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_color_settings');
    }
};
