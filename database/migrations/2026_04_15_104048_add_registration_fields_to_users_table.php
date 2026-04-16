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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('cvr_number', 32)->nullable()->after('phone');
            $table->text('registration_note')->nullable()->after('cvr_number');
            $table->boolean('wants_callback')->default(false)->after('registration_note');
            $table->timestamp('accepted_terms_at')->nullable()->after('wants_callback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'cvr_number',
                'registration_note',
                'wants_callback',
                'accepted_terms_at',
            ]);
        });
    }
};
