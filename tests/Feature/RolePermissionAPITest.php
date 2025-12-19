<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class RolePermissionAPITest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run Spatie permission migrations
        $this->artisan('migrate', ['--path' => 'vendor/spatie/laravel-permission/database/migrations']);
        
        // Create test permissions
        $this->createTestPermissions();
        
        // Create test roles
        $this->createTestRoles();
    }

    private function createTestPermissions(): void
    {
        $permissions = [
            'users.view.all',
            'users.create',
            'users.update.all',
            'users.delete',
            'permissions.view',
            'permissions.manage',
            'roles.view',
            'roles.manage'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    private function createTestRoles(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(['users.view.all', 'users.create', 'users.update.all', 'permissions.view', 'roles.view']);

        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        // User has no special permissions
    }

    /** @test */
    public function permission_registry_endpoint_is_accessible_to_authenticated_users()
    {
        $user = User::factory()->create();
        $user->assignRole('User');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permission-registry');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'label',
                    'icon',
                    'permissions'
                ]
            ]
        ]);
    }

    /** @test */
    public function my_permissions_endpoint_returns_user_permissions()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/permission-registry/my-permissions');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'roles',
                'permissions',
                'all_permissions'
            ]
        ]);
        
        $data = $response->json('data');
        $this->assertContains('Admin', $data['roles']);
        $this->assertNotEmpty($data['permissions']);
    }

    /** @test */
    public function sync_status_requires_permissions_view_permission()
    {
        // User without permission
        $user = User::factory()->create();
        $user->assignRole('User');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permission-registry/sync-status');
        $response->assertStatus(403);

        // Admin with permission
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/permission-registry/sync-status');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'registry_count',
                'database_count',
                'in_sync'
            ]
        ]);
    }

    /** @test */
    public function role_permission_matrix_requires_permissions_view()
    {
        $user = User::factory()->create();
        $user->assignRole('User');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permission-registry/role-matrix');
        $response->assertStatus(403);
    }

    /** @test */
    public function clear_cache_requires_permissions_manage()
    {
        // Admin can view but not manage
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/permission-registry/clear-cache');
        $response->assertStatus(403);

        // Super Admin can manage
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');
        
        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/permission-registry/clear-cache');
        $response->assertStatus(200);
    }

    /** @test */
    public function get_by_risk_level_endpoint_works()
    {
        $user = User::factory()->create();
        $user->assignRole('User');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permission-registry/risk-level/critical');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'risk_level',
                'permissions'
            ]
        ]);
    }

    /** @test */
    public function invalid_risk_level_returns_400()
    {
        $user = User::factory()->create();
        $user->assignRole('User');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permission-registry/risk-level/invalid');
        
        // Should return error for invalid risk level
        $this->assertContains($response->getStatusCode(), [400, 404]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_protected_endpoints()
    {
        $response = $this->getJson('/api/permission-registry/sync-status');
        $response->assertStatus(401);

        $response = $this->getJson('/api/permission-registry/my-permissions');
        $response->assertStatus(401);

        $response = $this->postJson('/api/permission-registry/clear-cache');
        $response->assertStatus(401);
    }

    /** @test */
    public function super_admin_has_all_permissions()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');
        
        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/permission-registry/my-permissions');
        
        $data = $response->json('data');
        $permissions = $data['permissions'];
        
        // Super Admin should have all test permissions
        $this->assertContains('users.view.all', $permissions);
        $this->assertContains('users.create', $permissions);
        $this->assertContains('users.delete', $permissions);
        $this->assertContains('permissions.manage', $permissions);
    }

    /** @test */
    public function admin_has_limited_permissions()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/permission-registry/my-permissions');
        
        $data = $response->json('data');
        $permissions = $data['permissions'];
        
        // Admin should have view permissions but not delete/manage
        $this->assertContains('users.view.all', $permissions);
        $this->assertContains('users.create', $permissions);
        $this->assertNotContains('users.delete', $permissions);
        $this->assertNotContains('permissions.manage', $permissions);
    }

    /** @test */
    public function regular_user_has_minimal_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('User');
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/permission-registry/my-permissions');
        
        $data = $response->json('data');
        $permissions = $data['permissions'];
        
        // Regular user should have no special permissions
        $this->assertEmpty($permissions);
    }
}
