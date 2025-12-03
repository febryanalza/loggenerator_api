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
     * Removes verification columns from logbook_datas table
     * since verification is now handled in logbook_data_verifications table.
     * 
     * Before removing, this migration will migrate existing verification data
     * to the new table.
     */
    public function up(): void
    {
        // First, migrate existing verification data to new table
        $verifiedRecords = DB::table('logbook_datas')
            ->whereNotNull('verified_by')
            ->where('is_verified', true)
            ->get();

        foreach ($verifiedRecords as $record) {
            DB::table('logbook_data_verifications')->insert([
                'id' => DB::raw('uuid_generate_v4()'),
                'data_id' => $record->id,
                'verifier_id' => $record->verified_by,
                'is_verified' => true,
                'verified_at' => $record->verified_at,
                'verification_notes' => $record->verification_notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Then remove the verification columns from logbook_datas
        Schema::table('logbook_datas', function (Blueprint $table) {
            $table->dropColumn([
                'is_verified',
                'verified_by',
                'verified_at',
                'verification_notes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Restores verification columns to logbook_datas table
     * and migrates data back from logbook_data_verifications.
     */
    public function down(): void
    {
        // Add back the verification columns
        Schema::table('logbook_datas', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            $table->foreign('verified_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Migrate data back - get the first verification for each data_id
        $verifications = DB::table('logbook_data_verifications')
            ->select('data_id', 'verifier_id', 'is_verified', 'verified_at', 'verification_notes')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MIN(id)'))
                    ->from('logbook_data_verifications')
                    ->groupBy('data_id');
            })
            ->get();

        foreach ($verifications as $verification) {
            DB::table('logbook_datas')
                ->where('id', $verification->data_id)
                ->update([
                    'is_verified' => $verification->is_verified,
                    'verified_by' => $verification->verifier_id,
                    'verified_at' => $verification->verified_at,
                    'verification_notes' => $verification->verification_notes,
                ]);
        }
    }
};
