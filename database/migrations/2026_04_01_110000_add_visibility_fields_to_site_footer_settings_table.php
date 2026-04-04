<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_footer_settings', function (Blueprint $table): void {
            $table->boolean('show_contact_email')->default(true)->after('contact_email');
            $table->boolean('show_contact_phone')->default(true)->after('contact_phone');
            $table->boolean('show_contact_address')->default(true)->after('contact_address');
            $table->boolean('show_contact_cvr')->default(true)->after('contact_cvr');
        });
    }

    public function down(): void
    {
        Schema::table('site_footer_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'show_contact_email',
                'show_contact_phone',
                'show_contact_address',
                'show_contact_cvr',
            ]);
        });
    }
};
