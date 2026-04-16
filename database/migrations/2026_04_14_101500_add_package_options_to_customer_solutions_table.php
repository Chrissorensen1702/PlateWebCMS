<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_solutions', function (Blueprint $table) {
            $table->json('package_options')->nullable()->after('sections');
        });
    }

    public function down(): void
    {
        Schema::table('customer_solutions', function (Blueprint $table) {
            $table->dropColumn('package_options');
        });
    }
};
