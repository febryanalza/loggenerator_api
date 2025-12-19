<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\CheckPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckPermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run Spatie permission migrations
        $this->artisan('migrate', ['--path' => 'vendor/spatie/laravel-permission/database/migrations']);
        
        // Create test permissions
        Permission::firstOrCreate(['name' => 'users.view.all', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'users.update.all', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_allows_access_when_user_has_required_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.view.all');
        
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.view.all');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_denies_access_when_user_lacks_permission()
    {
        $user = User::factory()->create();
        // User has no permissions
        
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.view.all');
        
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('FORBIDDEN', $data['error_code']);
    }

    /** @test */
    public function it_returns_401_for_unauthenticated_users()
    {
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.view.all');
        
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /** @test */
    public function it_handles_comma_separated_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.create'); // Has one of the required permissions
        
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        // User needs users.view.all OR users.create (has users.create)
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.view.all,users.create');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_provides_helpful_error_messages()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'User', 'guard_name' => 'web']);
        $user->assignRole($role);
        
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.delete');
        
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('hint', $data);
        $this->assertArrayHasKey('required', $data);
        $this->assertArrayHasKey('current', $data);
        $this->assertStringContainsString('permission', strtolower($data['hint']));
    }

    /** @test */
    public function it_handles_multiple_permissions_with_or_logic()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('users.update.all'); // Has one of three
        
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        // Requires any one of these three
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.view.all', 'users.create', 'users.update.all');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function forbidden_response_includes_user_context()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'TestRole', 'guard_name' => 'web']);
        $user->assignRole($role);
        $user->givePermissionTo('users.view.all');
        
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'users.delete');
        
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('current', $data);
        $this->assertArrayHasKey('roles', $data['current']);
        $this->assertArrayHasKey('permissions', $data['current']);
        $this->assertContains('TestRole', $data['current']['roles']);
        $this->assertContains('users.view.all', $data['current']['permissions']);
    }

    /** @test */
    public function it_handles_empty_permission_array()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $request = Request::create('/test', 'GET');
        $middleware = new CheckPermission();
        
        // No permissions required - should allow access
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        });
        
        // With no permissions specified, it should still check auth
        // In this case, user is authenticated so it passes
        $this->assertEquals(200, $response->getStatusCode());
    }
}
