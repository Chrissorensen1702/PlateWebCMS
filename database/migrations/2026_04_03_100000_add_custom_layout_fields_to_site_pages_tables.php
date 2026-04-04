<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            $table->string('layout_mode')->default('structured')->after('template_key');
            $table->longText('custom_html')->nullable()->after('layout_mode');
            $table->longText('custom_css')->nullable()->after('custom_html');
        });

        Schema::table('site_page_drafts', function (Blueprint $table): void {
            $table->string('layout_mode')->default('structured')->after('template_key');
            $table->longText('custom_html')->nullable()->after('layout_mode');
            $table->longText('custom_css')->nullable()->after('custom_html');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_page_drafts', function (Blueprint $table): void {
            $table->dropColumn(['layout_mode', 'custom_html', 'custom_css']);
        });

        Schema::table('site_pages', function (Blueprint $table): void {
            $table->dropColumn(['layout_mode', 'custom_html', 'custom_css']);
        });
    }
};
