<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_footer_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'title',
                'intro_text',
                'cta_label',
                'cta_href',
                'bottom_note',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('site_footer_settings', function (Blueprint $table): void {
            $table->string('title')->nullable()->after('site_id');
            $table->text('intro_text')->nullable()->after('social_links');
            $table->string('cta_label')->nullable()->after('contact_cvr');
            $table->string('cta_href')->nullable()->after('cta_label');
            $table->string('bottom_note')->nullable()->after('cta_href');
        });
    }
};
