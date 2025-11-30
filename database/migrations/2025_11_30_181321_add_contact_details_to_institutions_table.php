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
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('description');
            $table->text('address')->nullable()->after('phone_number');
            $table->string('company_type', 100)->nullable()->after('address');
            $table->string('company_email', 150)->nullable()->after('company_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'address', 'company_type', 'company_email']);
        });
    }
};
