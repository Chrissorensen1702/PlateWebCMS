<?php

use App\Models\SiteHeaderSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_header_settings', function (Blueprint $table): void {
            $table->string('background_style')->default(SiteHeaderSetting::BACKGROUND_AUTO)->after('show_cta');
            $table->string('shadow_style')->default(SiteHeaderSetting::SHADOW_AUTO)->after('background_style');
            $table->string('sticky_mode')->default(SiteHeaderSetting::STICKY_AUTO)->after('shadow_style');
        });
    }

    public function down(): void
    {
        Schema::table('site_header_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'background_style',
                'shadow_style',
                'sticky_mode',
            ]);
        });
    }
};
