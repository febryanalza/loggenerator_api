<?php

namespace App\Services;

/**
 * Permission Registry
 * 
 * Central registry for all application permissions.
 * Used for:
 * - Dynamic permission UI in admin panel
 * - Permission documentation
 * - Migration validation
 */
class PermissionRegistry
{
    /**
     * Get all permissions grouped by module
     * 
     * @return array
     */
    public static function getAllPermissions(): array
    {
        return [
            'user_management' => [
                'label' => 'User Management',
                'icon' => 'users',
                'permissions' => [
                    'users.view.all' => [
                        'label' => 'View All Users',
                        'description' => 'View all users in the system',
                        'risk_level' => 'low'
                    ],
                    'users.view.institution' => [
                        'label' => 'View Institution Users',
                        'description' => 'View users in same institution',
                        'risk_level' => 'low'
                    ],
                    'users.view.own' => [
                        'label' => 'View Own Profile',
                        'description' => 'View own profile only',
                        'risk_level' => 'low'
                    ],
                    'users.create' => [
                        'label' => 'Create Users',
                        'description' => 'Create new user accounts',
                        'risk_level' => 'medium'
                    ],
                    'users.update.all' => [
                        'label' => 'Update Any User',
                        'description' => 'Update any user profile',
                        'risk_level' => 'high'
                    ],
                    'users.update.institution' => [
                        'label' => 'Update Institution Users',
                        'description' => 'Update users in same institution',
                        'risk_level' => 'medium'
                    ],
                    'users.update.own' => [
                        'label' => 'Update Own Profile',
                        'description' => 'Update own profile',
                        'risk_level' => 'low'
                    ],
                    'users.delete' => [
                        'label' => 'Delete Users',
                        'description' => 'Delete user accounts',
                        'risk_level' => 'high'
                    ],
                    'users.assign-role' => [
                        'label' => 'Assign Roles',
                        'description' => 'Assign roles to users',
                        'risk_level' => 'critical'
                    ],
                    'users.export' => [
                        'label' => 'Export User Data',
                        'description' => 'Export user information',
                        'risk_level' => 'medium'
                    ],
                ]
            ],
            
            'institution_management' => [
                'label' => 'Institution Management',
                'icon' => 'building',
                'permissions' => [
                    'institutions.view.all' => [
                        'label' => 'View All Institutions',
                        'description' => 'View all institutions',
                        'risk_level' => 'low'
                    ],
                    'institutions.view.own' => [
                        'label' => 'View Own Institution',
                        'description' => 'View own institution only',
                        'risk_level' => 'low'
                    ],
                    'institutions.create' => [
                        'label' => 'Create Institutions',
                        'description' => 'Create new institutions',
                        'risk_level' => 'high'
                    ],
                    'institutions.update.all' => [
                        'label' => 'Update Any Institution',
                        'description' => 'Update any institution',
                        'risk_level' => 'high'
                    ],
                    'institutions.update.own' => [
                        'label' => 'Update Own Institution',
                        'description' => 'Update own institution',
                        'risk_level' => 'medium'
                    ],
                    'institutions.delete' => [
                        'label' => 'Delete Institutions',
                        'description' => 'Delete institutions',
                        'risk_level' => 'critical'
                    ],
                    'institutions.members.view' => [
                        'label' => 'View Institution Members',
                        'description' => 'View members of institutions',
                        'risk_level' => 'low'
                    ],
                    'institutions.members.manage' => [
                        'label' => 'Manage Institution Members',
                        'description' => 'Add/remove institution members',
                        'risk_level' => 'medium'
                    ],
                ]
            ],
            
            'logbook_management' => [
                'label' => 'Logbook Management',
                'icon' => 'book',
                'permissions' => [
                    'logbooks.view.all' => [
                        'label' => 'View All Logbooks',
                        'description' => 'View all logbooks in system',
                        'risk_level' => 'low'
                    ],
                    'logbooks.view.institution' => [
                        'label' => 'View Institution Logbooks',
                        'description' => 'View logbooks in institution',
                        'risk_level' => 'low'
                    ],
                    'logbooks.view.own' => [
                        'label' => 'View Own Logbooks',
                        'description' => 'View own logbooks only',
                        'risk_level' => 'low'
                    ],
                    'logbooks.create' => [
                        'label' => 'Create Logbooks',
                        'description' => 'Create new logbooks',
                        'risk_level' => 'low'
                    ],
                    'logbooks.update.all' => [
                        'label' => 'Update Any Logbook',
                        'description' => 'Update any logbook',
                        'risk_level' => 'medium'
                    ],
                    'logbooks.update.own' => [
                        'label' => 'Update Own Logbooks',
                        'description' => 'Update own logbooks',
                        'risk_level' => 'low'
                    ],
                    'logbooks.delete.all' => [
                        'label' => 'Delete Any Logbook',
                        'description' => 'Delete any logbook',
                        'risk_level' => 'high'
                    ],
                    'logbooks.delete.own' => [
                        'label' => 'Delete Own Logbooks',
                        'description' => 'Delete own logbooks',
                        'risk_level' => 'medium'
                    ],
                    'logbooks.verify' => [
                        'label' => 'Verify Logbook Entries',
                        'description' => 'Verify and approve entries',
                        'risk_level' => 'medium'
                    ],
                    'logbooks.export' => [
                        'label' => 'Export Logbooks',
                        'description' => 'Export logbook data',
                        'risk_level' => 'low'
                    ],
                ]
            ],
            
            'template_management' => [
                'label' => 'Template Management',
                'icon' => 'file-text',
                'permissions' => [
                    'templates.view' => [
                        'label' => 'View Templates',
                        'description' => 'View available templates',
                        'risk_level' => 'low'
                    ],
                    'templates.create' => [
                        'label' => 'Create Templates',
                        'description' => 'Create new templates',
                        'risk_level' => 'medium'
                    ],
                    'templates.update' => [
                        'label' => 'Update Templates',
                        'description' => 'Update existing templates',
                        'risk_level' => 'medium'
                    ],
                    'templates.delete' => [
                        'label' => 'Delete Templates',
                        'description' => 'Delete templates',
                        'risk_level' => 'high'
                    ],
                    'templates.publish' => [
                        'label' => 'Publish Templates',
                        'description' => 'Publish templates for use',
                        'risk_level' => 'medium'
                    ],
                ]
            ],
            
            'datatype_management' => [
                'label' => 'Data Type Management',
                'icon' => 'database',
                'permissions' => [
                    'datatypes.view' => [
                        'label' => 'View Data Types',
                        'description' => 'View available data types',
                        'risk_level' => 'low'
                    ],
                    'datatypes.create' => [
                        'label' => 'Create Data Types',
                        'description' => 'Create new data types',
                        'risk_level' => 'high'
                    ],
                    'datatypes.update' => [
                        'label' => 'Update Data Types',
                        'description' => 'Update existing data types',
                        'risk_level' => 'high'
                    ],
                    'datatypes.delete' => [
                        'label' => 'Delete Data Types',
                        'description' => 'Delete data types',
                        'risk_level' => 'critical'
                    ],
                ]
            ],
            
            'reports_analytics' => [
                'label' => 'Reports & Analytics',
                'icon' => 'bar-chart',
                'permissions' => [
                    'reports.view.basic' => [
                        'label' => 'View Basic Reports',
                        'description' => 'View basic reports',
                        'risk_level' => 'low'
                    ],
                    'reports.view.advanced' => [
                        'label' => 'View Advanced Reports',
                        'description' => 'View advanced analytics',
                        'risk_level' => 'medium'
                    ],
                    'reports.view.financial' => [
                        'label' => 'View Financial Reports',
                        'description' => 'View financial data',
                        'risk_level' => 'high'
                    ],
                    'reports.export' => [
                        'label' => 'Export Reports',
                        'description' => 'Export report data',
                        'risk_level' => 'medium'
                    ],
                ]
            ],
            
            'audit_logs' => [
                'label' => 'Audit Logs',
                'icon' => 'shield',
                'permissions' => [
                    'audit.view.own' => [
                        'label' => 'View Own Audit Logs',
                        'description' => 'View own activity logs',
                        'risk_level' => 'low'
                    ],
                    'audit.view.all' => [
                        'label' => 'View All Audit Logs',
                        'description' => 'View all system audit logs',
                        'risk_level' => 'high'
                    ],
                    'audit.export' => [
                        'label' => 'Export Audit Logs',
                        'description' => 'Export audit log data',
                        'risk_level' => 'high'
                    ],
                ]
            ],
            
            'role_permission_management' => [
                'label' => 'Roles & Permissions',
                'icon' => 'lock',
                'permissions' => [
                    'roles.view' => [
                        'label' => 'View Roles',
                        'description' => 'View system roles',
                        'risk_level' => 'low'
                    ],
                    'roles.create' => [
                        'label' => 'Create Roles',
                        'description' => 'Create custom roles',
                        'risk_level' => 'critical'
                    ],
                    'roles.update' => [
                        'label' => 'Update Roles',
                        'description' => 'Update role permissions',
                        'risk_level' => 'critical'
                    ],
                    'roles.delete' => [
                        'label' => 'Delete Roles',
                        'description' => 'Delete custom roles',
                        'risk_level' => 'critical'
                    ],
                    'permissions.view' => [
                        'label' => 'View Permissions',
                        'description' => 'View system permissions',
                        'risk_level' => 'low'
                    ],
                    'permissions.manage' => [
                        'label' => 'Manage Permissions',
                        'description' => 'Create/update permissions',
                        'risk_level' => 'critical'
                    ],
                ]
            ],
            
            'notifications' => [
                'label' => 'Notifications',
                'icon' => 'bell',
                'permissions' => [
                    'notifications.view' => [
                        'label' => 'View Notifications',
                        'description' => 'View notifications',
                        'risk_level' => 'low'
                    ],
                    'notifications.create' => [
                        'label' => 'Create Notifications',
                        'description' => 'Create system notifications',
                        'risk_level' => 'medium'
                    ],
                    'notifications.send.all' => [
                        'label' => 'Send to All Users',
                        'description' => 'Send notifications to all',
                        'risk_level' => 'high'
                    ],
                ]
            ],
        ];
    }

    /**
     * Get flat list of all permission names
     * 
     * @return array
     */
    public static function getPermissionNames(): array
    {
        $permissions = [];
        foreach (self::getAllPermissions() as $module) {
            $permissions = array_merge($permissions, array_keys($module['permissions']));
        }
        return $permissions;
    }

    /**
     * Get permissions by risk level
     * 
     * @param string $riskLevel
     * @return array
     */
    public static function getPermissionsByRiskLevel(string $riskLevel): array
    {
        $filtered = [];
        foreach (self::getAllPermissions() as $moduleKey => $module) {
            foreach ($module['permissions'] as $permName => $permData) {
                if ($permData['risk_level'] === $riskLevel) {
                    $filtered[$permName] = $permData;
                }
            }
        }
        return $filtered;
    }

    /**
     * Validate if permission exists in registry
     * 
     * @param string $permissionName
     * @return bool
     */
    public static function exists(string $permissionName): bool
    {
        return in_array($permissionName, self::getPermissionNames());
    }
}
