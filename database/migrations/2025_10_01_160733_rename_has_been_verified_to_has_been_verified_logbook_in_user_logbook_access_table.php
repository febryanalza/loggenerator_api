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
        Schema::table('user_logbook_access', function (Blueprint $table) {
            $table->renameColumn('has_been_verified', 'has_been_verified_logbook');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_logbook_access', function (Blueprint $table) {
            $table->renameColumn('has_been_verified_logbook', 'has_been_verified');
        });
    }
};
