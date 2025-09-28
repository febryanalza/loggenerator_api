# Permission Management - Super Admin Only

## Overview
Permission management telah dimigrasi untuk menggunakan middleware dan hanya dapat diakses oleh **Super Admin** saja. Ini sesuai dengan arsitektur enterprise yang aman dimana hanya role tertinggi yang dapat mengelola permissions sistem.

## Changes Made

### 🔧 Controller Updates

#### Before (Manual Authorization):
```php
// Check authorization (only admins can create permissions)
if (!Auth::user()->hasRole('Admin')) {
    return response()->json([
        'success' => false,
        'message' => 'You are not authorized to create permissions'
    ], 403);
}
```

#### After (Middleware-Based):
```php
// Authorization is handled by middleware
// Only Super Admin can access these endpoints
```

### 🛡️ Route Protection

```php
// Permission routes - Super Admin only (critical system operations)
Route::middleware('role:Super Admin')->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::post('/permissions/batch', [PermissionController::class, 'storeBatch']);
    Route::post('/permissions/assign-to-role', [PermissionController::class, 'assignToRole']);
    Route::post('/permissions/revoke-from-role', [PermissionController::class, 'revokeFromRole']);
});
```

## API Endpoints

### 1. List All Permissions
```http
GET /api/permissions
```

**Query Parameters:**
- `search` - Search in name or description
- `type` - Filter by permission type
- `sort_by` - Sort by: name, description, created_at, updated_at (default: name)
- `sort_direction` - asc/desc (default: asc)
- `per_page` - Pagination limit (default: 15)

**Response:**
```json
{
  "success": true,
  "message": "Permissions retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "manage templates",
      "description": "Can create, update, and delete logbook templates",
      "created_at": "2025-09-26T10:00:00Z",
      "updated_at": "2025-09-26T10:00:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 38,
    "last_page": 3,
    "has_more": true
  }
}
```

### 2. Get Permission Details
```http
GET /api/permissions/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Permission retrieved successfully",
  "data": {
    "id": 1,
    "name": "manage templates",
    "description": "Can create, update, and delete logbook templates",
    "created_at": "2025-09-26T10:00:00Z",
    "updated_at": "2025-09-26T10:00:00Z"
  }
}
```

### 3. Create Single Permission
```http
POST /api/permissions
```

**Request Body:**
```json
{
  "name": "manage reports",
  "description": "Can create, view, and manage reports"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Permission created successfully",
  "data": {
    "id": 39,
    "name": "manage reports",
    "description": "Can create, view, and manage reports",
    "created_at": "2025-09-26T11:00:00Z",
    "updated_at": "2025-09-26T11:00:00Z"
  }
}
```

### 4. Create Multiple Permissions
```http
POST /api/permissions/batch
```

**Request Body:**
```json
{
  "permissions": [
    {
      "name": "export data",
      "description": "Can export data to various formats"
    },
    {
      "name": "import data",
      "description": "Can import data from external sources"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "2 permissions created successfully",
  "data": [
    {
      "id": 40,
      "name": "export data",
      "description": "Can export data to various formats",
      "created_at": "2025-09-26T11:00:00Z",
      "updated_at": "2025-09-26T11:00:00Z"
    },
    {
      "id": 41,
      "name": "import data", 
      "description": "Can import data from external sources",
      "created_at": "2025-09-26T11:00:00Z",
      "updated_at": "2025-09-26T11:00:00Z"
    }
  ]
}
```

### 5. Assign Permissions to Role
```http
POST /api/permissions/assign-to-role
```

**Request Body:**
```json
{
  "role_id": 3,
  "permission_ids": [1, 2, 3, 15, 20]
}
```

**Response:**
```json
{
  "success": true,
  "message": "5 permissions assigned to role 'Manager' successfully",
  "role": "Manager",
  "assigned_permissions": [
    "manage templates",
    "view users",
    "create users",
    "view reports",
    "manage notifications"
  ]
}
```

### 6. Revoke Permissions from Role
```http
POST /api/permissions/revoke-from-role
```

**Request Body:**
```json
{
  "role_id": 3,
  "permission_ids": [20]
}
```

**Response:**
```json
{
  "success": true,
  "message": "1 permissions revoked from role 'Manager' successfully",
  "role": "Manager",
  "revoked_permissions": [
    "manage notifications"
  ]
}
```

## Security Architecture

### Access Control Matrix:

| Operation | Super Admin | Admin | Manager | User |
|-----------|-------------|--------|---------|------|
| **View Permissions** | ✅ | ✅ | ❌ | ❌ |
| **Create Permissions** | ✅ | ❌ | ❌ | ❌ |
| **Update Permissions** | ✅ | ❌ | ❌ | ❌ |
| **Delete Permissions** | ✅ | ❌ | ❌ | ❌ |
| **Assign to Role** | ✅ | ✅ | ❌ | ❌ |
| **Revoke from Role** | ✅ | ✅ | ❌ | ❌ |
| **View Roles** | ✅ | ✅ | ❌ | ❌ |
| **Manage Role Permissions** | ✅ | ✅ | ❌ | ❌ |

### Security Architecture:

#### Super Admin Only:
1. **Create New Permissions**: Critical system security - only Super Admin dapat menambah permissions baru
2. **Create New Roles**: System structure management - hanya Super Admin yang menentukan role hierarchy
3. **Delete Operations**: Irreversible changes yang bisa merusak sistem

#### Admin+ Access:
1. **View Operations**: Admin perlu visibility untuk troubleshooting dan monitoring
2. **Role-Permission Management**: Admin dapat mengelola existing assignments untuk operational efficiency
3. **User Support**: Admin dapat membantu resolve access issues tanpa escalate ke Super Admin

#### Why This Balance?
- **Separation of Duties**: Structure management vs Operational management
- **Operational Efficiency**: Admin dapat handle daily permission assignments
- **Security Control**: Critical system changes tetap Super Admin only
- **Audit Trail**: Semua operations tracked dengan proper user attribution

## Error Responses

### Authentication Required (401):
```json
{
  "success": false,
  "message": "Authentication required",
  "required_access": "Must be logged in"
}
```

### Insufficient Role (403):
```json
{
  "success": false,
  "message": "Insufficient permissions. Required role: Super Admin",
  "required_access": "One of: Super Admin",
  "user_roles": ["Admin"]
}
```

### Validation Error (422):
```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "name": ["The name field is required."],
    "permissions.0.name": ["The permissions.0.name field is required."]
  }
}
```

### Server Error (500):
```json
{
  "success": false,
  "message": "Failed to create permission",
  "error": "Database connection failed"
}
```

## Audit Logging

Semua permission operations di-log untuk audit:

```php
AuditLog::create([
    'user_id' => Auth::id(),
    'action' => 'CREATE_PERMISSION',
    'description' => 'Created new permission: manage reports',
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

**Audit Actions:**
- `CREATE_PERMISSION` - Single permission creation
- `CREATE_PERMISSIONS_BATCH` - Batch permission creation
- `ASSIGN_PERMISSIONS_TO_ROLE` - Assign permissions to role
- `REVOKE_PERMISSIONS_FROM_ROLE` - Revoke permissions from role

## Testing Examples

### Test Super Admin Access:
```bash
# Login as Super Admin
POST /api/login
{
  "email": "super.admin@example.com",
  "password": "password"
}

# Create permission (should work)
POST /api/permissions
Authorization: Bearer {token}
{
  "name": "test permission",
  "description": "Test description"
}
```

### Test Admin Access (Should Fail):
```bash
# Login as Admin
POST /api/login
{
  "email": "admin@example.com",
  "password": "password"
}

# Try to create permission (should fail with 403)
POST /api/permissions
Authorization: Bearer {token}
{
  "name": "test permission",
  "description": "Test description"
}
```

## Integration with Existing System

### Existing Enterprise Permissions:
System sudah memiliki 38 application-level permissions yang sudah di-assign ke roles:

- **Super Admin**: All 38 permissions
- **Admin**: 18 permissions
- **Manager**: 14 permissions  
- **User**: 8 permissions

### Logbook Permissions:
System juga memiliki 14 logbook-specific permissions untuk template-level access control.

### Permission Naming Convention:
- **Application Level**: `manage templates`, `view users`, `create users`
- **Logbook Level**: `view logbook data`, `create logbook entries`, `edit logbook entries`

## Conclusion

✅ **Permission management sekarang 100% Super Admin only**
✅ **Menggunakan middleware untuk clean separation of concerns**
✅ **Complete CRUD operations untuk permissions**
✅ **Role-permission assignment capabilities**
✅ **Comprehensive audit logging**
✅ **Enterprise-grade security architecture**

Sistem permission sekarang mengikuti best practices enterprise security dengan clear access control dan proper audit trail.