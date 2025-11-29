<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration consolidates profile_picture and avatar_url into a single avatar_url column.
     * Data from profile_picture is migrated to avatar_url before dropping the column.
     */
    public function up(): void
    {
        // First, migrate any data from profile_picture to avatar_url where avatar_url is null
        DB::table('users')
            ->whereNull('avatar_url')
            ->whereNotNull('profile_picture')
            ->update(['avatar_url' => DB::raw('profile_picture')]);

        // Drop the profile_picture column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_picture');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_picture', 255)->nullable()->after('phone_number');
        });

        // Restore data from avatar_url back to profile_picture
        DB::table('users')
            ->whereNotNull('avatar_url')
            ->update(['profile_picture' => DB::raw('avatar_url')]);
    }
};
