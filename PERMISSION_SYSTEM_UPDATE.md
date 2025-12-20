# Permission System Update - Logbook Access Integration

## 📋 Overview
Updated permission system to allow **User role** to create and manage their own logbooks. Users who create templates automatically become **Owners** with full control.

## 🔑 Key Changes

### 1. **New Permissions Added** (Migration: `2025_12_20_000001_add_granular_permissions.php`)
```php
['name' => 'templates.update.own', 'description' => 'Update own templates'],
['name' => 'templates.delete.own', 'description' => 'Delete own templates'],
```

### 2. **User Role Permissions Updated** (Seeder: `RolePermissionSeeder.php`)
User role now has:
- ✅ `templates.view` - View templates
- ✅ `templates.create` - **Create new templates**
- ✅ `templates.update.own` - **Update their own templates**
- ✅ `templates.delete.own` - **Delete their own templates**
- ✅ `logbooks.create` - Create logbooks
- ✅ `logbooks.update.own` - Update own logbooks
- ✅ `logbooks.delete.own` - Delete own logbooks

### 3. **Route Middleware Updated** (`routes/api.php`)
Changed from static permission check to ownership-based:

**Before:**
```php
Route::middleware('permission:manage templates')->group(function () {
    Route::post('/fields/batch', ...);
});
```

**After:**
```php
Route::middleware('template.owner')->group(function () {
    Route::post('/fields/batch', ...);
});
```

## 🎯 How It Works

### Template Creation Flow
1. **User creates template** → POST `/api/templates`
2. **System auto-creates** `user_logbook_access` entry with **Owner** role
3. **User now has full control** over that template via LogbookAccess

### Permission Check Priority
```
1. Super Admin/Admin/Manager/Institution Admin → ✅ Full Access (bypass ownership)
2. Template Owner (via LogbookAccess) → ✅ Full Access to their template
3. Other roles (Supervisor/Editor/Viewer) → ❌ Cannot manage fields
4. No access → ❌ Forbidden
```

### Middleware Chain
```
CheckTemplateOwnership Middleware:
├─ Check: Is Super Admin/Admin/Manager/Institution Admin?
│  └─ YES → Allow (admin override)
│  └─ NO → Continue
├─ Check: Is Owner in UserLogbookAccess table?
│  └─ YES → Allow (owner rights)
│  └─ NO → Deny (403 Forbidden)
```

## 📊 Role Hierarchy & Permissions

| Role | Template Create | Manage Own | Manage All | Override |
|------|----------------|------------|------------|----------|
| **User** | ✅ | ✅ | ❌ | ❌ |
| **Manager** | ✅ | ✅ | ✅ | ✅ |
| **Institution Admin** | ✅ | ✅ | ✅ (institution) | ✅ |
| **Admin** | ✅ | ✅ | ✅ | ✅ |
| **Super Admin** | ✅ | ✅ | ✅ | ✅ |

## 🔒 Logbook Access Roles

Each template has its own access control via `user_logbook_access`:

| Role | Read | Write | Delete | Verify | Manage Members |
|------|------|-------|--------|--------|----------------|
| **Viewer** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Editor** | ✅ | ✅ | ✅ (own) | ❌ | ❌ |
| **Supervisor** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Owner** | ✅ | ✅ | ✅ | ✅ | ✅ |

## 🚀 Migration Steps Completed

1. ✅ Rollback last migration: `php artisan migrate:rollback --step=1`
2. ✅ Re-run migration with new permissions: `php artisan migrate`
3. ✅ Update role permissions: `php artisan db:seed --class=RolePermissionSeeder`

## 📝 Testing Guide

### Test User Creating Template
```bash
# 1. Login as User
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}

# 2. Create Template (auto becomes Owner)
POST /api/templates
Authorization: Bearer {token}
{
  "name": "My Personal Logbook",
  "description": "Created by regular user"
}
# Response includes user_logbook_access with Owner role

# 3. Add Fields (now works because User is Owner)
POST /api/fields/batch
Authorization: Bearer {token}
{
  "template_id": "{template_id}",
  "fields": [
    {"name": "Date", "data_type": "Date"},
    {"name": "Notes", "data_type": "Text"}
  ]
}
# Should succeed (was failing before with 403)
```

### Verify Ownership
```bash
GET /api/templates/{id}
Authorization: Bearer {token}

# Response includes:
{
  "user_access": [
    {
      "user_id": "...",
      "logbook_role": {
        "name": "Owner"
      }
    }
  ]
}
```

## 🐛 Previous Error Fixed

**Error Before:**
```json
{
  "success": false,
  "message": "Insufficient permissions. You need one of: manage templates",
  "error_code": "FORBIDDEN"
}
```

**After Fix:**
✅ User can create and manage their own templates via Owner role in LogbookAccess

## 🔄 System Architecture

```
User creates Template
    ↓
LogbookTemplate::created event
    ↓
Auto-create UserLogbookAccess
    ├─ user_id: {creator}
    ├─ logbook_template_id: {template}
    └─ logbook_role_id: Owner
    ↓
User is now Owner
    ↓
CheckTemplateOwnership middleware
    └─ Allows full access to fields/data
```

## ⚙️ Configuration Files Updated

1. ✅ `database/migrations/2025_12_20_000001_add_granular_permissions.php`
2. ✅ `database/seeders/RolePermissionSeeder.php`
3. ✅ `routes/api.php`
4. ✅ `app/Http/Middleware/CheckTemplateOwnership.php` (already correct)
5. ✅ `app/Models/LogbookTemplate.php` (auto-creates Owner access)

## 📚 Permission Naming Convention

- `{resource}.view.{scope}` - View permissions (all/institution/own)
- `{resource}.create` - Create new resources
- `{resource}.update.{scope}` - Update permissions (all/institution/own)
- `{resource}.delete.{scope}` - Delete permissions (all/institution/own)
- `{resource}.manage` - Full management (admin only)

## 🎓 Best Practices

1. **Always use LogbookAccess** for template-specific permissions
2. **Admin roles** can override via `CheckTemplateOwnership` middleware
3. **Owner role** is automatically assigned on template creation
4. **Field management** requires Owner role or admin privileges
5. **Permission checks** cascade: Admin → Owner → Role-based → Deny

## 🔐 Security Notes

- ✅ Users can only manage their OWN templates (unless admin)
- ✅ Admin/Super Admin can override all ownership checks
- ✅ LogbookAccess provides granular per-template permissions
- ✅ Role-based permissions integrated with ownership model
- ✅ Database triggers ensure Owner access is always created

---

**Status:** ✅ **COMPLETED** - User role can now create and manage their own logbooks with full Owner permissions.
