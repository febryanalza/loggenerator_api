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
        // Remove has_been_assessed from logbook_template table
        Schema::table('logbook_template', function (Blueprint $table) {
            if (Schema::hasColumn('logbook_template', 'has_been_assessed')) {
                $table->dropColumn('has_been_assessed');
            }
        });

        // Remove has_been_verified_logbook from user_logbook_access table
        Schema::table('user_logbook_access', function (Blueprint $table) {
            if (Schema::hasColumn('user_logbook_access', 'has_been_verified_logbook')) {
                $table->dropColumn('has_been_verified_logbook');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore has_been_assessed to logbook_template table
        Schema::table('logbook_template', function (Blueprint $table) {
            $table->boolean('has_been_assessed')->default(false)->after('institution_id');
        });

        // Restore has_been_verified_logbook to user_logbook_access table
        Schema::table('user_logbook_access', function (Blueprint $table) {
            $table->boolean('has_been_verified_logbook')->default(false)->after('logbook_role_id');
        });
    }
};