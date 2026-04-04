<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('site_footer_settings');
    }

    public function down(): void
    {
        Schema::create('site_footer_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('layout')->default('classic');
            $table->string('brand_title')->nullable();
            $table->text('description')->nullable();
            $table->string('nav_title')->nullable();
            $table->string('contact_title')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('contact_cvr')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_href')->nullable();
            $table->string('bottom_note')->nullable();
            $table->timestamps();
        });
    }
};
