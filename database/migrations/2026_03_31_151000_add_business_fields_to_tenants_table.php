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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('company_email')->nullable()->after('name');
            $table->string('billing_email')->nullable()->after('company_email');
            $table->string('phone', 50)->nullable()->after('billing_email');
            $table->string('cvr_number', 32)->nullable()->after('phone');
            $table->string('website_url')->nullable()->after('cvr_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'company_email',
                'billing_email',
                'phone',
                'cvr_number',
                'website_url',
            ]);
        });
    }
};
