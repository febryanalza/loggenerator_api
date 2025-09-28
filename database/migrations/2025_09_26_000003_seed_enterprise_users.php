<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates initial enterprise users with proper role assignments.
     */
    public function up(): void
    {
        // Clear existing users for fresh migration
        DB::table('model_has_roles')->delete();
        DB::table('users')->delete();

        // ===== CREATE ENTERPRISE USERS =====
        
        // 1. SUPER ADMIN USER
        $superAdmin = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'System Super Administrator',
            'email' => 'superadmin@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('SuperAdmin2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $superAdmin->assignRole('Super Admin');

        // 2. ADMIN USERS
        $admin1 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'IT Administrator',
            'email' => 'admin@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Admin2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin1->assignRole('Admin');

        $admin2 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Operations Administrator',
            'email' => 'ops.admin@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('OpsAdmin2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin2->assignRole('Admin');

        // 3. MANAGER USERS
        $manager1 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Production Manager',
            'email' => 'production.manager@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Manager2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $manager1->assignRole('Manager');

        $manager2 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Quality Manager',
            'email' => 'quality.manager@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('QualityMgr2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $manager2->assignRole('Manager');

        $manager3 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Safety Manager',
            'email' => 'safety.manager@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('SafetyMgr2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $manager3->assignRole('Manager');

        // 4. REGULAR USERS
        $user1 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Production Operator',
            'email' => 'operator1@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('User2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user1->assignRole('User');

        $user2 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Quality Inspector',
            'email' => 'inspector1@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Inspector2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user2->assignRole('User');

        $user3 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Safety Officer',
            'email' => 'safety1@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Safety2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user3->assignRole('User');

        $user4 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Maintenance Technician',
            'email' => 'maintenance1@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Maintenance2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user4->assignRole('User');

        $user5 = User::create([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Data Analyst',
            'email' => 'analyst1@loggenerator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Analyst2025!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user5->assignRole('User');

        // ===== CREATE DEMO LOGBOOK TEMPLATES =====
        
        // Production Logbook Template
        $productionTemplateId = DB::table('logbook_template')->insertGetId([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Daily Production Log',
            'description' => 'Daily production activities and metrics tracking',
            'created_by' => $admin1->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Quality Control Template  
        $qualityTemplateId = DB::table('logbook_template')->insertGetId([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Quality Control Checklist',
            'description' => 'Quality control inspections and compliance checks',
            'created_by' => $admin2->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Safety Incident Template
        $safetyTemplateId = DB::table('logbook_template')->insertGetId([
            'id' => DB::raw('uuid_generate_v4()'),
            'name' => 'Safety Incident Report',
            'description' => 'Safety incidents, near misses, and corrective actions',
            'created_by' => $manager3->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ===== ASSIGN LOGBOOK ACCESS =====
        
        // Production Template Access
        $this->assignLogbookAccess($productionTemplateId, $manager1->id, 1); // Owner
        $this->assignLogbookAccess($productionTemplateId, $user1->id, 3); // Editor
        $this->assignLogbookAccess($productionTemplateId, $user4->id, 4); // Viewer
        
        // Quality Template Access
        $this->assignLogbookAccess($qualityTemplateId, $manager2->id, 1); // Owner
        $this->assignLogbookAccess($qualityTemplateId, $user2->id, 2); // Supervisor
        $this->assignLogbookAccess($qualityTemplateId, $user1->id, 4); // Viewer
        
        // Safety Template Access
        $this->assignLogbookAccess($safetyTemplateId, $manager3->id, 1); // Owner
        $this->assignLogbookAccess($safetyTemplateId, $user3->id, 2); // Supervisor
        $this->assignLogbookAccess($safetyTemplateId, $user1->id, 4); // Viewer
        $this->assignLogbookAccess($safetyTemplateId, $user4->id, 4); // Viewer

        echo "✅ Enterprise users created successfully!\n";
        echo "✅ Super Admin: superadmin@loggenerator.com (password: SuperAdmin2025!)\n";
        echo "✅ Admin: admin@loggenerator.com (password: Admin2025!)\n";
        echo "✅ Manager: production.manager@loggenerator.com (password: Manager2025!)\n";
        echo "✅ User: operator1@loggenerator.com (password: User2025!)\n";
        echo "✅ Total users created: " . User::count() . "\n";
        echo "✅ Demo templates with logbook access assigned!\n";
    }

    /**
     * Helper function to assign logbook access
     */
    private function assignLogbookAccess($templateId, $userId, $roleId)
    {
        DB::table('user_logbook_access')->insert([
            'id' => DB::raw('uuid_generate_v4()'),
            'user_id' => $userId,
            'logbook_template_id' => $templateId,
            'logbook_role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_logbook_access')->delete();
        DB::table('logbook_template')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('users')->delete();
    }
};