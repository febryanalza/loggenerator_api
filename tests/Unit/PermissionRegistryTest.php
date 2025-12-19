<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PermissionRegistry;

class PermissionRegistryTest extends TestCase
{
    /** @test */
    public function it_returns_all_permissions_grouped_by_module()
    {
        $permissions = PermissionRegistry::getAllPermissions();
        
        $this->assertIsArray($permissions);
        $this->assertNotEmpty($permissions);
        
        // Should have expected modules
        $this->assertArrayHasKey('user_management', $permissions);
        $this->assertArrayHasKey('logbook_management', $permissions);
        $this->assertArrayHasKey('institution_management', $permissions);
    }

    /** @test */
    public function each_module_has_required_structure()
    {
        $permissions = PermissionRegistry::getAllPermissions();
        
        foreach ($permissions as $moduleKey => $module) {
            $this->assertArrayHasKey('label', $module, "Module {$moduleKey} missing 'label'");
            $this->assertArrayHasKey('icon', $module, "Module {$moduleKey} missing 'icon'");
            $this->assertArrayHasKey('permissions', $module, "Module {$moduleKey} missing 'permissions'");
            $this->assertIsArray($module['permissions']);
        }
    }

    /** @test */
    public function each_permission_has_required_metadata()
    {
        $permissions = PermissionRegistry::getAllPermissions();
        
        foreach ($permissions as $module) {
            foreach ($module['permissions'] as $permissionName => $permissionData) {
                $this->assertArrayHasKey('label', $permissionData, 
                    "Permission {$permissionName} missing 'label'");
                $this->assertArrayHasKey('description', $permissionData,
                    "Permission {$permissionName} missing 'description'");
                $this->assertArrayHasKey('risk_level', $permissionData,
                    "Permission {$permissionName} missing 'risk_level'");
                
                // Validate risk level
                $this->assertContains($permissionData['risk_level'], ['low', 'medium', 'high', 'critical'],
                    "Permission {$permissionName} has invalid risk_level: {$permissionData['risk_level']}");
            }
        }
    }

    /** @test */
    public function permission_names_follow_naming_convention()
    {
        $permissionNames = PermissionRegistry::getPermissionNames();
        
        foreach ($permissionNames as $name) {
            // Should follow pattern: module.action or module.action.scope
            $parts = explode('.', $name);
            
            $this->assertGreaterThanOrEqual(2, count($parts),
                "Permission '{$name}' should have at least 2 parts (module.action)");
            
            $this->assertLessThanOrEqual(3, count($parts),
                "Permission '{$name}' should have at most 3 parts (module.action.scope)");
            
            // Should not have spaces
            $this->assertStringNotContainsString(' ', $name,
                "Permission '{$name}' should not contain spaces");
            
            // Should be lowercase
            $this->assertEquals(strtolower($name), $name,
                "Permission '{$name}' should be lowercase");
        }
    }

    /** @test */
    public function get_permission_names_returns_flat_array()
    {
        $names = PermissionRegistry::getPermissionNames();
        
        $this->assertIsArray($names);
        $this->assertNotEmpty($names);
        
        // Should be a flat array of strings
        foreach ($names as $name) {
            $this->assertIsString($name);
        }
        
        // Should not have duplicates
        $this->assertEquals(count($names), count(array_unique($names)),
            'Permission names should be unique');
    }

    /** @test */
    public function get_permissions_by_risk_level_filters_correctly()
    {
        $criticalPerms = PermissionRegistry::getPermissionsByRiskLevel('critical');
        
        $this->assertIsArray($criticalPerms);
        
        // All returned permissions should have critical risk level
        $allPermissions = PermissionRegistry::getAllPermissions();
        foreach ($criticalPerms as $permName) {
            $found = false;
            foreach ($allPermissions as $module) {
                if (isset($module['permissions'][$permName])) {
                    $this->assertEquals('critical', $module['permissions'][$permName]['risk_level']);
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Permission {$permName} not found in registry");
        }
    }

    /** @test */
    public function get_permissions_by_risk_level_returns_empty_for_invalid_level()
    {
        $result = PermissionRegistry::getPermissionsByRiskLevel('invalid');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function exists_method_validates_permission_existence()
    {
        $this->assertTrue(PermissionRegistry::exists('users.view.all'));
        $this->assertTrue(PermissionRegistry::exists('logbooks.create'));
        $this->assertFalse(PermissionRegistry::exists('nonexistent.permission'));
        $this->assertFalse(PermissionRegistry::exists('invalid'));
    }

    /** @test */
    public function registry_has_minimum_expected_permissions()
    {
        $names = PermissionRegistry::getPermissionNames();
        
        // Should have at least 50 permissions for enterprise system
        $this->assertGreaterThanOrEqual(50, count($names),
            'Registry should have at least 50 permissions for enterprise grade');
    }

    /** @test */
    public function all_modules_have_at_least_one_permission()
    {
        $permissions = PermissionRegistry::getAllPermissions();
        
        foreach ($permissions as $moduleKey => $module) {
            $this->assertNotEmpty($module['permissions'],
                "Module {$moduleKey} should have at least one permission");
        }
    }

    /** @test */
    public function risk_levels_are_distributed_appropriately()
    {
        $low = PermissionRegistry::getPermissionsByRiskLevel('low');
        $medium = PermissionRegistry::getPermissionsByRiskLevel('medium');
        $high = PermissionRegistry::getPermissionsByRiskLevel('high');
        $critical = PermissionRegistry::getPermissionsByRiskLevel('critical');
        
        // Should have some permissions at each level
        $this->assertNotEmpty($low, 'Should have some low-risk permissions');
        $this->assertNotEmpty($medium, 'Should have some medium-risk permissions');
        
        // Most permissions should be low or medium risk
        $totalPerms = count(PermissionRegistry::getPermissionNames());
        $lowMediumCount = count($low) + count($medium);
        
        $this->assertGreaterThan($totalPerms * 0.5, $lowMediumCount,
            'At least 50% of permissions should be low or medium risk');
    }

    /** @test */
    public function permission_descriptions_are_meaningful()
    {
        $permissions = PermissionRegistry::getAllPermissions();
        
        foreach ($permissions as $module) {
            foreach ($module['permissions'] as $permissionName => $permissionData) {
                $description = $permissionData['description'];
                
                // Description should not be empty
                $this->assertNotEmpty($description,
                    "Permission {$permissionName} should have a non-empty description");
                
                // Description should have minimum length
                $this->assertGreaterThan(10, strlen($description),
                    "Permission {$permissionName} description should be at least 10 characters");
                
                // Description should start with capital letter
                $this->assertTrue(ctype_upper($description[0]),
                    "Permission {$permissionName} description should start with capital letter");
            }
        }
    }

    /** @test */
    public function module_labels_are_user_friendly()
    {
        $permissions = PermissionRegistry::getAllPermissions();
        
        foreach ($permissions as $moduleKey => $module) {
            $label = $module['label'];
            
            // Label should not be empty
            $this->assertNotEmpty($label);
            
            // Label should not be the same as key (should be formatted)
            $this->assertNotEquals($moduleKey, $label,
                "Module label should be user-friendly, not just the key");
            
            // Label should have proper capitalization
            $this->assertTrue(ctype_upper($label[0]),
                "Module {$moduleKey} label should start with capital letter");
        }
    }
}
