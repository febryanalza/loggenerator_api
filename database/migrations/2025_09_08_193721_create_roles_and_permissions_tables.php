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
        // Kita tidak perlu membuat tabel roles dan permissions karena sudah dibuat oleh migrasi sebelumnya
        // Langsung membuat tabel-tabel lainnya

        // Role Permissions
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });

        // User Roles
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        // Roles in Data
        Schema::create('roles_in_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Permission in Data
        Schema::create('permission_in_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Data Roles Permission
        Schema::create('data_roles_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_data_id')->unsigned()->nullable();
            $table->integer('permission_data_id')->unsigned()->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('role_data_id')->references('id')->on('roles_in_data')->onDelete('set null');
            $table->foreign('permission_data_id')->references('id')->on('permission_in_data')->onDelete('set null');
        });

        // User Data
        Schema::create('user_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->char('template_id', 36)->nullable();
            $table->integer('role_data_id')->unsigned();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('logbook_template')->onDelete('cascade');
            $table->foreign('role_data_id')->references('id')->on('roles_in_data')->onDelete('cascade');
            $table->index(['id', 'user_id', 'template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_data');
        Schema::dropIfExists('data_roles_permission');
        Schema::dropIfExists('permission_in_data');
        Schema::dropIfExists('roles_in_data');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
    }
};
