<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_booking_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('connection_mode')->default('create');
            $table->string('booking_reference')->nullable();
            $table->string('booking_url')->nullable();
            $table->string('dashboard_url')->nullable();
            $table->string('cta_label')->nullable();
            $table->boolean('use_on_website')->default(false);
            $table->boolean('show_in_header')->default(false);
            $table->boolean('show_in_contact_sections')->default(false);
            $table->boolean('open_in_new_tab')->default(false);
            $table->timestamps();

            $table->unique('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_booking_settings');
    }
};
