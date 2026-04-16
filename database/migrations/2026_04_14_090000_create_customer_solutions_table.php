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
        Schema::create('customer_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_key');
            $table->unsignedSmallInteger('locations')->default(1);
            $table->unsignedSmallInteger('staff')->default(4);
            $table->unsignedInteger('bookings')->default(300);
            $table->unsignedTinyInteger('sections')->default(3);
            $table->string('source')->default('pricing_calculator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_solutions');
    }
};
