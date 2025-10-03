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
        Schema::table('logbook_template', function (Blueprint $table) {
            $table->boolean('has_been_assessed')->default(false)->after('institution_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbook_template', function (Blueprint $table) {
            $table->dropColumn('has_been_assessed');
        });
    }
};
