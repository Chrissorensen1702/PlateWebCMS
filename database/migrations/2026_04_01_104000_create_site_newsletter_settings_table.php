<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_newsletter_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('headline')->nullable();
            $table->text('copy')->nullable();
            $table->string('button_label')->nullable();
            $table->string('placement')->default('footer');
            $table->string('delivery_mode')->default('cms');
            $table->text('consent_text')->nullable();
            $table->timestamps();

            $table->unique('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_newsletter_settings');
    }
};
