<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_booking_settings', function (Blueprint $table): void {
            $table->string('owner_name')->nullable()->after('dashboard_url');
            $table->string('owner_email')->nullable()->after('owner_name');
            $table->timestamp('provisioned_at')->nullable()->after('owner_email');
        });
    }

    public function down(): void
    {
        Schema::table('site_booking_settings', function (Blueprint $table): void {
            $table->dropColumn(['owner_name', 'owner_email', 'provisioned_at']);
        });
    }
};
