<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a trigger that assigns the default 'user' role to newly inserted users.
     */
    public function up(): void
    {
        // First create the trigger function
        DB::unprepared("
            CREATE OR REPLACE FUNCTION assign_default_role()
            RETURNS TRIGGER AS $$
            DECLARE
                user_role_id BIGINT;
            BEGIN
                -- Get the user role ID from Spatie roles table
                SELECT id INTO user_role_id FROM roles WHERE name = 'user' AND guard_name = 'web' LIMIT 1;
                
                -- Insert into model_has_roles (Spatie table)
                IF user_role_id IS NOT NULL THEN
                    INSERT INTO model_has_roles (role_id, model_type, model_id)
                    VALUES (user_role_id, 'App\\Models\\User', NEW.id);
                END IF;
                
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Then create the trigger
        DB::unprepared('
            CREATE TRIGGER after_user_insert
            AFTER INSERT ON users
            FOR EACH ROW
            EXECUTE FUNCTION assign_default_role();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_user_insert ON users');
        DB::unprepared('DROP FUNCTION IF EXISTS assign_default_role()');
    }
};
