# AUTHENTICATION & AUTHORIZATION GUIDE
**LogGenerator API - Complete Authentication & Authorization Documentation**

---

## 📋 TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [Role & Permission Architecture](#role--permission-architecture)
3. [Authentication System](#authentication-system)
4. [Authorization Implementation](#authorization-implementation)
5. [Middleware Documentation](#middleware-documentation)
6. [Enterprise Role Structure](#enterprise-role-structure)
7. [Template Ownership & Access Control](#template-ownership--access-control)
8. [Testing & Troubleshooting](#testing--troubleshooting)

---

## 🏗️ SYSTEM OVERVIEW

### Authentication & Authorization Stack
- **Framework**: Laravel 12 with Sanctum
- **Permission System**: Spatie Laravel Permission
- **Database**: PostgreSQL with UUID support
- **Role Hierarchy**: 4-tier enterprise structure
- **Sub-Role System**: Template-specific access control

### Key Features
- JWT/Sanctum token-based authentication
- Role-based access control (RBAC)
- Template ownership with admin override
- Granular permission system (61 total permissions)
- Middleware-based route protection

---

## 🎯 ROLE & PERMISSION ARCHITECTURE

### 1. APPLICATION ROLES (Enterprise Hierarchy)

#### **Super Admin** (47 permissions)
```
├── System Administration
│   ├── super_admin_access
│   ├── manage_system_settings
│   ├── manage_database
│   └── view_system_logs
│
├── Enterprise Management
│   ├── manage_enterprise_roles
│   └── ALL Admin + Manager + User permissions
```

#### **Admin** (26 permissions)
```
├── Enterprise Operations
│   ├── admin_dashboard_access
│   ├── manage_all_users
│   ├── manage_all_templates
│   ├── manage_all_logbooks
│   ├── view_all_audit_logs
│   └── manage_permissions
│
├── Template Management
│   ├── create_templates
│   ├── edit_templates
│   ├── delete_templates
│   ├── manage_templates
│   └── assign_template_access
```

#### **Manager** (25 permissions)
```
├── Department Management
│   ├── manager_dashboard_access
│   ├── manage_team_users
│   ├── manage_department_templates
│   ├── view_department_logbooks
│   └── assign_logbook_access
│
├── Template Operations
│   ├── create_templates
│   ├── edit_templates
│   ├── delete_templates
│   ├── manage_templates
│   └── assign_template_access
```

#### **User** (16 permissions)
```
├── Basic Operations
│   ├── user_dashboard_access
│   ├── view_own_profile
│   ├── view_assigned_logbooks
│   ├── create_logbook_entries
│   └── edit_own_entries
│
├── Template Management (ENHANCED)
│   ├── create_templates
│   ├── edit_templates
│   ├── delete_templates
│   ├── manage_templates
│   └── assign_template_access
```

### 2. LOGBOOK SUB-ROLES (Template-Specific)

#### **Owner** (9 permissions)
- Full control over template
- Can assign access to other users
- Can modify template structure
- Admin override capability exists

#### **Supervisor** (6 permissions)
- Manage data and user access
- Cannot modify template structure
- Cannot delete logbook entries (FIXED)
- Can assign template access

#### **Editor** (4 permissions)
- Create and edit logbook entries
- Upload files
- Cannot manage users
- Cannot delete entries

#### **Viewer** (2 permissions)
- Read-only access
- View template and data only

---

## 🔐 AUTHENTICATION SYSTEM

### Token-Based Authentication
```php
// Login Endpoint
POST /api/login
{
    "email": "user@example.com",
    "password": "password"
}

// Response
{
    "success": true,
    "token": "bearer_token_here",
    "user": {
        "id": "uuid",
        "name": "User Name",
        "email": "user@example.com",
        "roles": ["User"]
    }
}
```

### Protected Routes
```php
// All API routes require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Protected endpoints here
});
```

### User Registration
```php
// Register Endpoint
POST /api/register
{
    "name": "New User",
    "email": "newuser@example.com", 
    "password": "password",
    "password_confirmation": "password"
}
```

---

## 🛡️ AUTHORIZATION IMPLEMENTATION

### Controller-Level Authorization

#### UserLogbookAccessController Authorization
```php
// Owner Check
private function isOwnerOfTemplate($templateId)
{
    return DB::table('user_logbook_access')
        ->where('user_id', auth()->id())
        ->where('template_id', $templateId)
        ->where('logbook_role_id', 1) // Owner role
        ->exists();
}

// Access Check
private function hasAccessToTemplate($templateId)
{
    return DB::table('user_logbook_access')
        ->where('user_id', auth()->id())
        ->where('template_id', $templateId)
        ->exists();
}

// Admin Override Check
private function isSuperAdminOrAdmin()
{
    $userId = auth()->id();
    return DB::table('model_has_roles')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('model_has_roles.model_id', $userId)
        ->whereIn('roles.name', ['Super Admin', 'Admin'])
        ->exists();
}
```

#### Protected Operations
```php
// Create Access - Only Owner, Super Admin, or Admin
public function store(Request $request)
{
    $templateId = $request->template_id;
    
    if (!$this->isOwnerOfTemplate($templateId) && !$this->isSuperAdminOrAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Only template owner, Super Admin, or Admin can grant access.'
        ], 403);
    }
    
    // Create access logic...
}

// View Access - User must have template access
public function show($id)
{
    $access = UserLogbookAccess::find($id);
    
    if (!$this->hasAccessToTemplate($access->template_id)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. You do not have access to this template.'
        ], 403);
    }
    
    // Show access logic...
}
```

### Template-Level Authorization
```php
// LogbookTemplateController
public function destroy($id)
{
    $template = LogbookTemplate::find($id);
    
    // Check if user is owner or admin
    if (!$this->isOwnerOfTemplate($id) && !$this->isSuperAdminOrAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Only template owner or admin can delete template.'
        ], 403);
    }
    
    // Delete template logic...
}
```

---

## ⚙️ MIDDLEWARE DOCUMENTATION

### Built-in Middleware
```php
// Authentication Middleware
Route::middleware('auth:sanctum')->group(function () {
    // All authenticated routes
});

// Permission-based Middleware (Spatie)
Route::middleware(['auth:sanctum', 'permission:manage_templates'])->group(function () {
    // Template management routes
});

// Role-based Middleware
Route::middleware(['auth:sanctum', 'role:Admin|Super Admin'])->group(function () {
    // Admin-only routes
});
```

### Custom Authorization Middleware
```php
// Template Owner Middleware
class TemplateOwnerMiddleware
{
    public function handle($request, Closure $next)
    {
        $templateId = $request->route('id') ?? $request->template_id;
        
        if (!$this->isOwnerOfTemplate($templateId) && !$this->isSuperAdminOrAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Owner access required.'
            ], 403);
        }
        
        return $next($request);
    }
}
```

---

## 🏢 ENTERPRISE ROLE STRUCTURE

### Hierarchy Flow
```
Super Admin
    ├── Can override all permissions
    ├── Full system access
    └── Can manage enterprise roles
        
Admin
    ├── Can override template ownership
    ├── Enterprise-level management
    └── Cannot access system settings
        
Manager
    ├── Department/team management
    ├── Template creation and management
    └── Cannot override ownership without admin
        
User
    ├── Template creation and management (ENHANCED)
    ├── Can assign template access to others
    └── Basic user operations
```

### Permission Inheritance
- **Super Admin**: Inherits ALL permissions (47 total)
- **Admin**: Inherits most permissions except system-level (26 total)
- **Manager**: Business operation permissions (25 total)
- **User**: Enhanced with template management (16 total)

---

## 📝 TEMPLATE OWNERSHIP & ACCESS CONTROL

### Template Creation Process
```
1. User creates template
2. User automatically becomes "Owner" (sub-role)
3. Owner can assign other users as Supervisor/Editor/Viewer
4. Admin can override ownership if needed
```

### Access Assignment Matrix
| Role | Create Template | Edit Template | Delete Template | Assign Access | Override Owner |
|------|----------------|---------------|-----------------|---------------|----------------|
| **Super Admin** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Admin** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Manager** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **User** | ✅ | ✅ | ✅ | ✅ | ❌ |

### Sub-Role Assignment Flow
```php
// User assigns template access
POST /api/user-access
{
    "template_id": "uuid",
    "user_id": "uuid", 
    "logbook_role_id": 2, // 1=Owner, 2=Supervisor, 3=Editor, 4=Viewer
    "granted_by": "current_user_id"
}
```

---

## 🧪 TESTING & TROUBLESHOOTING

### Permission Testing
```bash
# Test role permissions
php artisan tinker
>>> $user = User::find('uuid');
>>> $user->hasPermissionTo('create_templates');
>>> $user->hasRole('Admin');

# Test logbook access
>>> DB::table('user_logbook_access')
    ->where('user_id', 'uuid')
    ->where('template_id', 'template_uuid')
    ->get();
```

### Common Issues & Solutions

#### Issue: User can't assign template access
**Solution**: Check if user has `manage_access` permission in logbook sub-role
```sql
SELECT lr.name, lp.name 
FROM logbook_role_permissions lrp
JOIN logbook_roles lr ON lrp.logbook_role_id = lr.id  
JOIN logbook_permissions lp ON lrp.logbook_permission_id = lp.id
WHERE lr.name = 'Owner';
```

#### Issue: Supervisor can delete logbook entries
**Solution**: Verify Supervisor role doesn't have `delete logbook entries` permission
```sql
SELECT * FROM logbook_role_permissions lrp
JOIN logbook_permissions lp ON lrp.logbook_permission_id = lp.id
WHERE lrp.logbook_role_id = 2 AND lp.name = 'delete logbook entries';
-- Should return empty result
```

#### Issue: Route authorization failing
**Solution**: Check middleware stack and permission assignments
```bash
php artisan route:list --path=api
php artisan permission:show
```

### Debugging Commands
```bash
# Check user permissions
php artisan tinker --execute="User::find('uuid')->permissions"

# Check role assignments  
php artisan tinker --execute="User::find('uuid')->roles"

# Verify seeder data
php artisan migrate:fresh --seed
```

---

## 🔧 MAINTENANCE & UPDATES

### Adding New Permissions
1. Update `ApplicationPermissionSeeder.php`
2. Add permission to appropriate roles
3. Run `php artisan migrate:fresh --seed`
4. Update controller authorization logic

### Modifying Role Hierarchy
1. Update `ApplicationRoleSeeder.php`
2. Adjust permission assignments in `ApplicationPermissionSeeder.php`
3. Test authorization flows
4. Update documentation

### Security Best Practices
- Always validate template ownership before operations
- Use admin override sparingly
- Log authorization failures for audit
- Regularly review permission assignments
- Test authorization after any changes

---

---

## 🚨 RECENT FIXES & UPDATES

### Issue: Owner Cannot Access api/fields/batch Endpoint
**Date**: September 27, 2025
**Problem**: Users with Owner logbook role receiving "Insufficient permissions. Required permission: manage templates" error

#### Root Cause Analysis
- Endpoint `api/fields/batch` requires application-level permission "manage templates"
- Some users had logbook Owner role but no application role assignment
- Missing default role assignment for new users

#### Solutions Implemented

1. **Fixed Default Role Trigger**
   ```sql
   -- Updated trigger to use correct role name (User vs user)
   SELECT id FROM roles WHERE name = 'User' AND guard_name = 'web' LIMIT 1;
   ```

2. **Enhanced Permission Matrix**
   ```
   Owner Logbook Role + User Application Role = Full API Access
   ✅ manage templates permission via User role
   ✅ Template structure permissions via Owner logbook role
   ```

3. **Automatic Role Assignment**
   - All users now automatically get "User" role upon creation
   - Database trigger ensures no user exists without application role
   - All users have minimum "manage templates" permission

#### Verification Results
```bash
✅ ALL users have 'manage templates' permission
✅ Owner users can now access api/fields/batch
✅ Default role trigger working correctly
✅ Permission middleware functioning properly
```

#### Files Modified
- `database/migrations/2025_09_10_181541_create_user_default_role_trigger.php`
- Updated role name from 'user' to 'User' in trigger function

#### Testing Performed
- Verified all existing users have required permissions
- Tested new user creation with automatic role assignment
- Simulated middleware permission check
- Confirmed Owner logbook access working

---

**✅ Authentication & Authorization system is fully functional with enterprise-grade security and granular access control.**