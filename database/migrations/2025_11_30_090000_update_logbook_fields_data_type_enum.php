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
     * Updates the data_type ENUM/CHECK constraint in logbook_fields table
     * from old Indonesian types to new English types that match frontend.
     * 
     * Old types: teks, angka, gambar, tanggal, jam
     * New types: text, textarea, number, date, time, datetime, image, url, phone, currency, percentage, location
     */
    public function up(): void
    {
        // For PostgreSQL, we need to drop and recreate the constraint
        // First, alter the column to remove the check constraint
        
        if (DB::getDriverName() === 'pgsql') {
            // PostgreSQL: Drop the existing check constraint and add new one
            DB::statement('ALTER TABLE logbook_fields DROP CONSTRAINT IF EXISTS logbook_fields_data_type_check');
            
            // Change column type to varchar temporarily, then add new check constraint
            DB::statement('ALTER TABLE logbook_fields ALTER COLUMN data_type TYPE VARCHAR(50)');
            
            // Add new check constraint with updated data types
            DB::statement("ALTER TABLE logbook_fields ADD CONSTRAINT logbook_fields_data_type_check CHECK (data_type::text = ANY (ARRAY['text'::text, 'textarea'::text, 'number'::text, 'date'::text, 'time'::text, 'datetime'::text, 'image'::text, 'url'::text, 'phone'::text, 'currency'::text, 'percentage'::text, 'location'::text, 'file'::text]))");
            
            // Migrate existing data from old types to new types
            DB::statement("UPDATE logbook_fields SET data_type = 'text' WHERE data_type = 'teks'");
            DB::statement("UPDATE logbook_fields SET data_type = 'number' WHERE data_type = 'angka'");
            DB::statement("UPDATE logbook_fields SET data_type = 'image' WHERE data_type = 'gambar'");
            DB::statement("UPDATE logbook_fields SET data_type = 'date' WHERE data_type = 'tanggal'");
            DB::statement("UPDATE logbook_fields SET data_type = 'time' WHERE data_type = 'jam'");
        } else {
            // MySQL: Modify the ENUM column
            DB::statement("ALTER TABLE logbook_fields MODIFY COLUMN data_type ENUM('text', 'textarea', 'number', 'date', 'time', 'datetime', 'image', 'url', 'phone', 'currency', 'percentage', 'location', 'file') NOT NULL");
            
            // Migrate existing data from old types to new types (if any exist)
            DB::statement("UPDATE logbook_fields SET data_type = 'text' WHERE data_type = 'teks'");
            DB::statement("UPDATE logbook_fields SET data_type = 'number' WHERE data_type = 'angka'");
            DB::statement("UPDATE logbook_fields SET data_type = 'image' WHERE data_type = 'gambar'");
            DB::statement("UPDATE logbook_fields SET data_type = 'date' WHERE data_type = 'tanggal'");
            DB::statement("UPDATE logbook_fields SET data_type = 'time' WHERE data_type = 'jam'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Migrate data back to old types first
            DB::statement("UPDATE logbook_fields SET data_type = 'teks' WHERE data_type = 'text'");
            DB::statement("UPDATE logbook_fields SET data_type = 'angka' WHERE data_type = 'number'");
            DB::statement("UPDATE logbook_fields SET data_type = 'gambar' WHERE data_type = 'image'");
            DB::statement("UPDATE logbook_fields SET data_type = 'tanggal' WHERE data_type = 'date'");
            DB::statement("UPDATE logbook_fields SET data_type = 'jam' WHERE data_type = 'time'");
            
            // Drop new constraint
            DB::statement('ALTER TABLE logbook_fields DROP CONSTRAINT IF EXISTS logbook_fields_data_type_check');
            
            // Add old check constraint
            DB::statement("ALTER TABLE logbook_fields ADD CONSTRAINT logbook_fields_data_type_check CHECK (data_type::text = ANY (ARRAY['teks'::text, 'angka'::text, 'gambar'::text, 'tanggal'::text, 'jam'::text]))");
        } else {
            // MySQL: Revert ENUM
            DB::statement("UPDATE logbook_fields SET data_type = 'teks' WHERE data_type = 'text'");
            DB::statement("UPDATE logbook_fields SET data_type = 'angka' WHERE data_type = 'number'");
            DB::statement("UPDATE logbook_fields SET data_type = 'gambar' WHERE data_type = 'image'");
            DB::statement("UPDATE logbook_fields SET data_type = 'tanggal' WHERE data_type = 'date'");
            DB::statement("UPDATE logbook_fields SET data_type = 'jam' WHERE data_type = 'time'");
            
            DB::statement("ALTER TABLE logbook_fields MODIFY COLUMN data_type ENUM('teks', 'angka', 'gambar', 'tanggal', 'jam') NOT NULL");
        }
    }
};
