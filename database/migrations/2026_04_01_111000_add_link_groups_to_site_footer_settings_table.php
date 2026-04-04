<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_footer_settings', function (Blueprint $table): void {
            $table->json('navigation_links')->nullable()->after('site_id');
            $table->json('information_links')->nullable()->after('navigation_links');
            $table->json('social_links')->nullable()->after('information_links');
        });
    }

    public function down(): void
    {
        Schema::table('site_footer_settings', function (Blueprint $table): void {
            $table->dropColumn(['navigation_links', 'information_links', 'social_links']);
        });
    }
};
