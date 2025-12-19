# 🔐 Dynamic Permission System - Implementation Guide

## 📋 Overview

Sistem permission telah di-upgrade dari **role-based hardcoded** menjadi **dynamic granular permission** menggunakan Spatie Permission package dengan caching dan performance optimization.

### ✅ Yang Berubah

| Aspek | Sebelumnya | Sekarang |
|-------|-----------|----------|
| **Middleware** | `CheckPermission` dengan raw queries | Enhanced dengan Spatie methods + caching |
| **Permission Check** | `hasRole('Super Admin')` | `can('users.view.all')` |
| **Granularity** | Role level (Admin, User) | Action level (users.create, users.delete) |
| **Performance** | DB query setiap request | Cached dengan Redis/memory |
| **Flexibility** | Hardcoded di code | Configurable via database |

### ❌ Yang TIDAK Berubah

- ✅ **Tabel database** - Tetap menggunakan Spatie tables
- ✅ **LogbookAccess middleware** - Tetap untuk data-level permissions
- ✅ **API contracts** - Endpoint URLs tidak berubah
- ✅ **Backward compatibility** - Old role checks masih berfungsi

---

## 🚀 Quick Start

### Step 1: Run Migration

```bash
# Backup database terlebih dahulu!
php artisan db:backup

# Run migration untuk add granular permissions
php artisan migrate

# Sync permissions dari registry ke database
php artisan permission:sync

# Verify status
php artisan permission:status
```

### Step 2: Clear Cache

```bash
php artisan permission:cache-reset
php artisan cache:clear
php artisan config:clear
```

### Step 3: Test API

```bash
# Test permission registry
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost/api/permission-registry

# Test your permissions
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost/api/permission-registry/my-permissions

# Test endpoint with new permission
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost/api/users
```

---

## 📖 Usage Guide

### Middleware Usage

#### ✅ Recommended: Permission-Based

```php
// Single permission
Route::middleware('permission:users.view.all')->get('/users', ...);

// Multiple permissions (OR logic)
Route::middleware('permission:users.create,users.update')->post('/users', ...);

// Multiple permissions (AND logic)
Route::middleware(['permission:users.create', 'permission:institutions.create'])
    ->post('/batch-create', ...);
```

#### ⚠️ Legacy: Role-Based (Still works)

```php
// Still functional but DEPRECATED
Route::middleware('role:Super Admin,Admin')->get('/users', ...);
```

### Controller Usage

#### ✅ Recommended: Permission Check

```php
// Check permission
if ($user->can('users.delete')) {
    // Allow delete
}

// Check multiple (OR)
if ($user->hasAnyPermission(['users.create', 'users.update'])) {
    // Allow
}

// Check multiple (AND)
if ($user->hasAllPermissions(['users.create', 'institutions.create'])) {
    // Allow
}

// Throw exception if no permission
$user->checkPermissionTo('users.delete');
```

#### ⚠️ Legacy: Role Check (Still works)

```php
// Still functional but DEPRECATED
if ($user->hasRole('Admin')) {
    // Do something
}
```

### Policy Usage (Recommended for complex logic)

```php
// app/Policies/UserPolicy.php
class UserPolicy {
    public function viewAny(User $user): bool {
        return $user->can('users.view.all');
    }
    
    public function create(User $user): bool {
        return $user->can('users.create');
    }
    
    public function delete(User $user, User $model): bool {
        // Can't delete Super Admin
        if ($model->hasRole('Super Admin')) {
            return false;
        }
        
        return $user->can('users.delete');
    }
}

// In Controller
$this->authorize('delete', $user);
```

---

## 🎯 Permission Naming Convention

Format: `{module}.{action}.{scope}`

### Examples

```
users.view.all        → View all users in system
users.view.own        → View own profile only
users.view.institution → View users in same institution

logbooks.create       → Create logbooks
logbooks.update.own   → Update own logbooks
logbooks.update.all   → Update any logbook
logbooks.delete.all   → Delete any logbook

institutions.update.own → Update own institution (Institution Admin)
institutions.update.all → Update any institution (Super Admin)
```

---

## 🔧 Artisan Commands

### Check Status

```bash
# Show overall system status
php artisan permission:status

# Show specific user permissions
php artisan permission:status --user-id=USER_UUID

# Show specific role permissions
php artisan permission:status --role="Super Admin"
```

### Sync Permissions

```bash
# Sync from registry to database
php artisan permission:sync

# Show diff only (don't sync)
php artisan permission:sync --show-only

# Force sync without confirmation
php artisan permission:sync --force
```

### Cache Management

```bash
# Clear permission cache
php artisan permission:cache-reset

# Clear all cache
php artisan cache:clear
```

---

## 📊 API Endpoints

### Permission Registry

```bash
# Get all permissions grouped by module
GET /api/permission-registry

# Get permissions by risk level
GET /api/permission-registry/risk-level/{low|medium|high|critical}

# Get current user's permissions
GET /api/permission-registry/my-permissions

# Get sync status (Admin only)
GET /api/permission-registry/sync-status

# Get role-permission matrix (Admin only)
GET /api/permission-registry/role-matrix

# Clear cache (Super Admin only)
POST /api/permission-registry/clear-cache
```

### Response Examples

**GET /api/permission-registry/my-permissions**
```json
{
  "success": true,
  "data": {
    "user_id": "uuid",
    "roles": ["Admin"],
    "direct_permissions": [],
    "role_permissions": ["users.view.all", "users.create", ...],
    "all_permissions": ["users.view.all", "users.create", ...]
  },
  "meta": {
    "total_count": 45
  }
}
```

---

## 🧪 Testing

### Test Permission Assignment

```php
// Assign permission to user
$user->givePermissionTo('users.create');

// Assign permission to role
$role = Role::findByName('Manager');
$role->givePermissionTo(['logbooks.create', 'logbooks.update.own']);

// Check permission
$user->can('users.create'); // true
```

### Test API with Postman

1. Login to get token
2. Set header: `Authorization: Bearer {token}`
3. Test endpoints dengan berbagai roles
4. Verify 403 response untuk unauthorized access

---

## 🔄 Migration Checklist

### Phase 1: Preparation ✅ (DONE)

- [x] Migration file created
- [x] PermissionRegistry service created
- [x] Enhanced middleware implemented
- [x] API endpoints added
- [x] Artisan commands created

### Phase 2: Execution (DO NOW)

- [ ] Run `php artisan migrate`
- [ ] Run `php artisan permission:sync`
- [ ] Test all endpoints with different roles
- [ ] Verify permission checks working

### Phase 3: Gradual Route Migration

**Priority Order:**
1. ✅ Read endpoints (GET) - Low risk
2. ✅ Create endpoints (POST) - Medium risk
3. ✅ Update endpoints (PUT/PATCH) - Medium risk
4. ✅ Delete endpoints (DELETE) - High risk

**Per Endpoint:**
```php
// 1. Before
Route::middleware('role:Admin')->get('/users', ...);

// 2. After (test thoroughly)
Route::middleware('permission:users.view.all')->get('/users', ...);

// 3. Test checklist:
// - Super Admin token ✓
// - Admin token ✓
// - User token (should 403) ✓
// - No token (should 401) ✓
```

### Phase 4: Controller Refactoring

Find and replace in controllers:
```php
// Find: hasRole('Super Admin')
// Replace: can('users.assign-role')

// Find: hasRole('Institution Admin')
// Replace: can('institutions.update.own')
```

### Phase 5: Cleanup

- [ ] Remove `role:` from all routes
- [ ] Update API documentation
- [ ] Remove deprecated comments
- [ ] Performance check

---

## 🐛 Troubleshooting

### Permission Not Working

```bash
# Clear cache
php artisan permission:cache-reset

# Check sync status
php artisan permission:sync --show-only

# Check user permissions
php artisan permission:status --user-id=UUID
```

### 403 Forbidden Errors

1. Check logs: `storage/logs/permission-migration.log`
2. Verify user has permission: `php artisan permission:status --user-id=UUID`
3. Check role has permission: `php artisan permission:status --role=Admin`
4. Clear cache: `php artisan permission:cache-reset`

### Performance Issues

```bash
# Enable Redis caching
# config/permission.php
'cache' => [
    'store' => 'redis',
],

# Clear and rebuild cache
php artisan permission:cache-reset
```

---

## 📝 Next Steps

1. **Test Phase** (1-2 hari)
   - Test semua endpoint dengan Postman
   - Verify permission checks
   - Check logs untuk errors

2. **Migrate Routes** (2-3 hari)
   - Start dengan read endpoints
   - Gradually migrate write endpoints
   - Test each endpoint thoroughly

3. **Controller Refactor** (1-2 hari)
   - Replace hasRole() with can()
   - Update institution scoping logic
   - Add policy classes

4. **Production Deploy**
   - Backup database
   - Run migration
   - Monitor logs
   - Be ready for rollback

---

## 🆘 Support

Jika ada masalah:
1. Check logs: `storage/logs/laravel.log`
2. Check permission logs: `storage/logs/permission-migration.log`
3. Run: `php artisan permission:status`
4. Clear cache: `php artisan cache:clear && php artisan permission:cache-reset`

---

**Last Updated:** December 19, 2025
**Status:** ✅ Ready for Testing
