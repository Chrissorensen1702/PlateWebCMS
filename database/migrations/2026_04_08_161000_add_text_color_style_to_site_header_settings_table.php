<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_header_settings', function (Blueprint $table): void {
            $table->string('text_color_style')->nullable()->after('background_style');
        });
    }

    public function down(): void
    {
        Schema::table('site_header_settings', function (Blueprint $table): void {
            $table->dropColumn('text_color_style');
        });
    }
};
