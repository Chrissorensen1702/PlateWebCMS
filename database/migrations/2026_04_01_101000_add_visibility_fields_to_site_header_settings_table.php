<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_header_settings', function (Blueprint $table): void {
            $table->boolean('show_brand_name')->default(true)->after('brand_name');
            $table->boolean('show_tagline')->default(true)->after('tagline');
        });
    }

    public function down(): void
    {
        Schema::table('site_header_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'show_brand_name',
                'show_tagline',
            ]);
        });
    }
};
