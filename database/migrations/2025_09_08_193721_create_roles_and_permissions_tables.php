<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates data-level permissions for logbook management.
     * Application-level permissions handled by Spatie tables.
     */
    public function up(): void
    {
        // Remove duplicate tables - use Spatie for application-level RBAC
        // Keep only logbook-specific data-level permission tables

        // Logbook Roles (data-level: owner, editor, viewer, supervisor)
        Schema::create('logbook_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique(); // owner, editor, viewer, supervisor
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Logbook Permissions (data-level actions)
        Schema::create('logbook_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->unique(); // read_logbook, create_entry, edit_entry, delete_entry, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Logbook Role-Permission relationships
        Schema::create('logbook_role_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('logbook_role_id')->unsigned();
            $table->integer('logbook_permission_id')->unsigned();
            $table->timestamps();

            $table->foreign('logbook_role_id')->references('id')->on('logbook_roles')->onDelete('cascade');
            $table->foreign('logbook_permission_id')->references('id')->on('logbook_permissions')->onDelete('cascade');
            $table->unique(['logbook_role_id', 'logbook_permission_id'], 'logbook_role_permission_unique');
        });

        // User permissions scoped to specific logbook templates
        Schema::create('user_logbook_access', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('user_id');
            $table->uuid('logbook_template_id');
            $table->integer('logbook_role_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('logbook_template_id')->references('id')->on('logbook_template')->onDelete('cascade');
            $table->foreign('logbook_role_id')->references('id')->on('logbook_roles')->onDelete('cascade');
            
            // One role per user per template
            $table->unique(['user_id', 'logbook_template_id'], 'user_template_unique');
            $table->index(['user_id', 'logbook_template_id'], 'user_template_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logbook_access');
        Schema::dropIfExists('logbook_role_permissions');
        Schema::dropIfExists('logbook_permissions');
        Schema::dropIfExists('logbook_roles');
    }
};
