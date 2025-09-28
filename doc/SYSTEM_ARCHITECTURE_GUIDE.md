# SYSTEM ARCHITECTURE GUIDE
**LogGenerator API - Complete System Architecture & Implementation**

---

## 📋 TABLE OF CONTENTS

1. [System Overview](#system-overview)
2. [Database Architecture](#database-architecture)
3. [Enterprise Role Architecture](#enterprise-role-architecture)
4. [Seeder System](#seeder-system)
5. [Application Structure](#application-structure)
6. [Security Architecture](#security-architecture)
7. [Template Ownership System](#template-ownership-system)
8. [Data Flow & Relationships](#data-flow--relationships)
9. [System Performance](#system-performance)
10. [Deployment Architecture](#deployment-architecture)

---

## 🏗️ SYSTEM OVERVIEW

### Technology Stack
- **Framework**: Laravel 12
- **Database**: PostgreSQL with UUID primary keys
- **Authentication**: Laravel Sanctum
- **Permission System**: Spatie Laravel Permission
- **Frontend API**: RESTful JSON API
- **File Storage**: Local storage with API endpoints

### Core Components
```
LogGenerator API
├── Authentication System (Sanctum)
├── Role-Based Access Control (Spatie)
├── Template Management System
├── Logbook Data Management
├── User Access Control System
├── File Management System
└── Notification System
```

### Key Features
- 🔐 **Enterprise-grade authentication & authorization**
- 👥 **4-tier role hierarchy with granular permissions**
- 📝 **Dynamic template creation and management**
- 🎯 **Template-specific access control (sub-roles)**
- 📊 **Comprehensive audit logging**
- 📁 **File upload and management**
- 🔔 **Notification system**

---

## 🗄️ DATABASE ARCHITECTURE

### Core Tables

#### 1. User Management
```sql
-- Users table (Laravel default + modifications)
users (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255),
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Spatie Permission Tables
roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

permissions (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

model_has_roles (
    role_id BIGINT,
    model_type VARCHAR(255),
    model_id UUID,
    PRIMARY KEY (role_id, model_id, model_type)
);

model_has_permissions (
    permission_id BIGINT,
    model_type VARCHAR(255),
    model_id UUID,
    PRIMARY KEY (permission_id, model_id, model_type)
);

role_has_permissions (
    permission_id BIGINT,
    role_id BIGINT,
    PRIMARY KEY (permission_id, role_id)
);
```

#### 2. Template System
```sql
-- Logbook Template
logbook_template (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_by UUID, -- FK to users.id
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Template Fields
logbook_fields (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL,
    data_type ENUM('teks', 'angka', 'gambar', 'tanggal', 'jam'),
    template_id UUID NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (template_id) REFERENCES logbook_template(id) ON DELETE CASCADE
);

-- Logbook Data Entries
logbook_datas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    template_id UUID NOT NULL,
    writer_id UUID NOT NULL,
    data JSON NOT NULL, -- Stores field data as JSON
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (template_id) REFERENCES logbook_template(id) ON DELETE CASCADE,
    FOREIGN KEY (writer_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 3. Access Control System
```sql
-- Logbook Sub-Roles (Template-specific roles)
logbook_roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL, -- Owner, Supervisor, Editor, Viewer
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Logbook Permissions (Template-specific permissions)
logbook_permissions (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Role-Permission Junction
logbook_role_permissions (
    logbook_role_id BIGINT,
    logbook_permission_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    PRIMARY KEY (logbook_role_id, logbook_permission_id),
    FOREIGN KEY (logbook_role_id) REFERENCES logbook_roles(id),
    FOREIGN KEY (logbook_permission_id) REFERENCES logbook_permissions(id)
);

-- User Template Access
user_logbook_access (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL,
    template_id UUID NOT NULL,
    logbook_role_id BIGINT NOT NULL,
    granted_by UUID, -- User who granted access
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES logbook_template(id) ON DELETE CASCADE,
    FOREIGN KEY (logbook_role_id) REFERENCES logbook_roles(id),
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE(user_id, template_id) -- One role per user per template
);
```

#### 4. System Tables
```sql
-- Audit Logs
audit_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID,
    action VARCHAR(255),
    resource_type VARCHAR(255),
    resource_id UUID,
    old_values JSON,
    new_values JSON,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Notifications
notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    type VARCHAR(255),
    notifiable_type VARCHAR(255),
    notifiable_id UUID,
    data JSON,
    read_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Personal Access Tokens (Sanctum)
personal_access_tokens (
    id BIGINT PRIMARY KEY,
    tokenable_type VARCHAR(255),
    tokenable_id UUID,
    name VARCHAR(255),
    token VARCHAR(64) UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Database Relationships
```
Users (1) ←→ (M) User_Logbook_Access (M) ←→ (1) Logbook_Template
                          ↓
                     (1) Logbook_Roles
                          ↓
              (M) Logbook_Role_Permissions (M)
                          ↓
                 (1) Logbook_Permissions

Logbook_Template (1) ←→ (M) Logbook_Fields
Logbook_Template (1) ←→ (M) Logbook_Datas

Users (M) ←→ (M) Roles (via model_has_roles)
Roles (M) ←→ (M) Permissions (via role_has_permissions)
```

---

## 🏢 ENTERPRISE ROLE ARCHITECTURE

### 1. Application Role Hierarchy
```
┌─────────────────┐
│   Super Admin   │ ← Full System Control (47 permissions)
│   (System God)  │
└─────────────────┘
         │
         ├─────────────────┐
         │      Admin      │ ← Enterprise Management (26 permissions)
         │  (Can Override) │
         └─────────────────┘
                  │
                  ├─────────────────┐
                  │    Manager      │ ← Department Management (25 permissions)
                  │ (Team Leaders)  │
                  └─────────────────┘
                           │
                           ├─────────────────┐
                           │      User       │ ← Enhanced Template Powers (16 permissions)
                           │ (Template Owners)│
                           └─────────────────┘
```

### 2. Template Sub-Role Hierarchy
```
Template Level Access Control:

┌─────────────────┐
│     Owner       │ ← Full Template Control (9 permissions)
│ (Template Creator)│   • Can assign access to others
└─────────────────┘   • Can modify template structure
         │              • Admin can override
         ├─────────────────┐
         │   Supervisor    │ ← Data Management (6 permissions)
         │ (Data Manager)  │   • Can manage users
         └─────────────────┘   • Cannot delete entries (FIXED)
                  │              • Can assign access
                  ├─────────────────┐
                  │     Editor      │ ← Content Creation (4 permissions)
                  │ (Content Writer)│   • Create & edit entries
                  └─────────────────┘   • Upload files
                           │
                           ├─────────────────┐
                           │     Viewer      │ ← Read-Only (2 permissions)
                           │ (Read Only)     │   • View data only
                           └─────────────────┘
```

### 3. Permission Matrix
| Role | Template Management | User Management | Data Operations | System Access |
|------|-------------------|----------------|-----------------|---------------|
| **Super Admin** | Full Control | Full Control | Full Control | Full Control |
| **Admin** | Full Control | Enterprise Level | All Templates | Limited System |
| **Manager** | Department Level | Team Level | Department Data | No System |
| **User** | Own Templates | Via Sub-roles | Own/Assigned | No System |

---

## 🌱 SEEDER SYSTEM

### Seeder Architecture
```
DatabaseSeeder (Orchestrator)
├── ApplicationRoleSeeder      → Creates 4 enterprise roles
├── ApplicationPermissionSeeder → Creates 47 permissions + assignments
├── LogbookRoleSeeder          → Creates 4 template sub-roles  
├── LogbookPermissionSeeder    → Creates 14 logbook permissions + assignments
└── UserSeeder                 → Creates sample users for testing
```

### 1. ApplicationRoleSeeder
**Purpose**: Creates enterprise role hierarchy
```php
$roles = [
    'Super Admin' => 'Full system administrator with all permissions',
    'Admin' => 'Administrative access to enterprise features',
    'Manager' => 'Management access to department operations', 
    'User' => 'Regular user with template creation abilities'
];
```

### 2. ApplicationPermissionSeeder
**Purpose**: Creates application permissions and assigns to roles
```php
$permissions = [
    // Template Management (Enhanced for User role)
    'create templates' => 'Can create new logbook templates',
    'edit templates' => 'Can edit existing logbook templates',
    'delete templates' => 'Can delete logbook templates',
    'manage templates' => 'Can manage template structure',
    'assign template access' => 'Can assign users to templates',
    
    // User Management
    'view users' => 'Can view user information',
    'manage users' => 'Can create and manage users',
    
    // File & Notification Management
    'upload files' => 'Can upload files',
    'manage files' => 'Can manage uploaded files', 
    'send notifications' => 'Can send notifications',
    
    // System Management
    'view system info' => 'Can view system information',
    'manage system' => 'Can perform system operations'
];
```

**Role Assignments:**
- **Super Admin**: ALL permissions (47 total)
- **Admin**: Enterprise permissions (26 total)
- **Manager**: Business permissions (25 total) 
- **User**: Template + basic permissions (16 total)

### 3. LogbookRoleSeeder
**Purpose**: Creates template-specific sub-roles
```php
$logbookRoles = [
    1 => ['name' => 'Owner', 'description' => 'Full control over template'],
    2 => ['name' => 'Supervisor', 'description' => 'Manage data and users'],
    3 => ['name' => 'Editor', 'description' => 'Create and edit entries'],
    4 => ['name' => 'Viewer', 'description' => 'Read-only access']
];
```

### 4. LogbookPermissionSeeder
**Purpose**: Creates template permissions and assigns to sub-roles
```php
$logbookPermissions = [
    1 => 'view logbook data',
    2 => 'create logbook entries', 
    3 => 'edit logbook entries',
    4 => 'delete logbook entries',
    5 => 'manage template users',
    6 => 'view template users',
    7 => 'edit template structure',
    8 => 'delete template',
    9 => 'manage_access', // FIXED: Added for Owner
    // ... more permissions
];

$rolePermissions = [
    1 => [1,2,3,4,5,6,7,8,9], // Owner - Full permissions
    2 => [1,2,3,5,6,9],       // Supervisor - No delete entries (FIXED)
    3 => [1,2,3,6],           // Editor - Content permissions
    4 => [1,6]                // Viewer - Read-only
];
```

### 5. UserSeeder
**Purpose**: Creates sample users for testing
```php
$users = [
    ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => 'Super Admin'],
    ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'Admin'],
    ['name' => 'Manager User', 'email' => 'manager@example.com', 'role' => 'Manager'],
    ['name' => 'Regular User', 'email' => 'user@example.com', 'role' => 'User']
];
```

---

## 🏗️ APPLICATION STRUCTURE

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── LogbookTemplateController.php
│   │   │   ├── LogbookDataController.php
│   │   │   ├── LogbookFieldController.php
│   │   │   ├── UserLogbookAccessController.php
│   │   │   ├── FileController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── RoleController.php
│   │   │   └── PermissionController.php
│   │   └── ...
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   └── ...
│   ├── Resources/
│   │   ├── UserResource.php
│   │   ├── TemplateResource.php
│   │   └── ...
│   └── Middleware/
│       ├── Authenticate.php
│       └── Custom middleware...
├── Models/
│   ├── User.php
│   ├── LogbookTemplate.php
│   ├── LogbookData.php
│   ├── LogbookField.php
│   ├── UserLogbookAccess.php
│   ├── AuditLog.php
│   └── Notification.php
└── Providers/
    └── AppServiceProvider.php
```

### Controller Architecture
```php
// Base API Response Pattern
class BaseController extends Controller
{
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    protected function errorResponse($message, $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
```

### Model Relationships
```php
// User Model
class User extends Authenticatable
{
    use HasRoles, HasPermissions; // Spatie traits
    
    public function logbookTemplates()
    {
        return $this->hasMany(LogbookTemplate::class, 'created_by');
    }
    
    public function logbookAccess()
    {
        return $this->hasMany(UserLogbookAccess::class);
    }
    
    public function logbookEntries()
    {
        return $this->hasMany(LogbookData::class, 'writer_id');
    }
}

// LogbookTemplate Model
class LogbookTemplate extends Model
{
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function fields()
    {
        return $this->hasMany(LogbookField::class, 'template_id');
    }
    
    public function entries()
    {
        return $this->hasMany(LogbookData::class, 'template_id');
    }
    
    public function userAccess()
    {
        return $this->hasMany(UserLogbookAccess::class, 'template_id');
    }
}
```

---

## 🔒 SECURITY ARCHITECTURE

### Multi-Layer Security
```
Request → Authentication → Authorization → Controller → Database
   ↓           ↓              ↓             ↓           ↓
Bearer Token → User Check → Permission → Business Logic → Query
```

### Security Components

#### 1. Authentication Layer
- **Sanctum Tokens**: Stateless API authentication
- **Token Expiration**: Configurable token lifetime
- **Token Abilities**: Granular token permissions

#### 2. Authorization Layer
- **Role-Based Access Control**: Enterprise role hierarchy
- **Permission-Based Access**: Granular permission checking
- **Template Ownership**: Owner-level access control
- **Admin Override**: Emergency access for administrators

#### 3. Data Protection
- **UUID Primary Keys**: Non-sequential identifiers
- **SQL Injection Protection**: Eloquent ORM usage
- **XSS Protection**: JSON API responses
- **File Upload Security**: Type and size validation

#### 4. Audit Trail
```php
// Audit Log Structure
{
    "user_id": "uuid",
    "action": "template.created",
    "resource_type": "LogbookTemplate",
    "resource_id": "uuid",
    "old_values": null,
    "new_values": {"name": "New Template"},
    "ip_address": "192.168.1.1",
    "user_agent": "Browser info",
    "created_at": "timestamp"
}
```

---

## 📝 TEMPLATE OWNERSHIP SYSTEM

### Ownership Flow
```
1. User creates template → Automatically becomes Owner (sub-role)
2. Owner can assign other users → Supervisor/Editor/Viewer sub-roles
3. Admin can override ownership → Emergency access/management
4. Template deletion → Only Owner or Admin can delete
```

### Access Control Matrix
| Operation | Owner | Supervisor | Editor | Viewer | Admin Override |
|-----------|-------|------------|--------|--------|---------------|
| View Template | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit Structure | ✅ | ❌ | ❌ | ❌ | ✅ |
| Delete Template | ✅ | ❌ | ❌ | ❌ | ✅ |
| Create Entries | ✅ | ✅ | ✅ | ❌ | ✅ |
| Edit Entries | ✅ | ✅ | ✅ | ❌ | ✅ |
| Delete Entries | ✅ | ❌ | ❌ | ❌ | ✅ |
| Assign Access | ✅ | ✅ | ❌ | ❌ | ✅ |
| Manage Users | ✅ | ✅ | ❌ | ❌ | ✅ |

### Business Rules
1. **Template Creator = Automatic Owner**: User who creates template gets Owner sub-role
2. **One Owner Per Template**: Only one Owner allowed per template
3. **Admin Override Capability**: Admin can perform any operation regardless of ownership
4. **Supervisor Restrictions**: Cannot delete entries (data protection)
5. **Editor Limitations**: Cannot manage users or delete entries
6. **Viewer Read-Only**: Can only view template and data

---

## 📊 DATA FLOW & RELATIONSHIPS

### Template Creation Flow
```
1. User creates template via API
   ↓
2. LogbookTemplate record created
   ↓
3. LogbookFields records created for each field
   ↓
4. UserLogbookAccess record created (user = Owner)
   ↓
5. Template ready for use
```

### User Access Assignment Flow
```
1. Owner/Admin assigns access via API
   ↓
2. System validates ownership/admin status
   ↓
3. UserLogbookAccess record created
   ↓
4. User can access template with assigned role
```

### Data Entry Flow
```
1. User creates logbook entry
   ↓
2. System validates template access
   ↓
3. System validates sub-role permissions
   ↓
4. LogbookData record created with JSON data
   ↓
5. Entry available to all template users
```

---

## ⚡ SYSTEM PERFORMANCE

### Database Optimization
- **UUID Indexes**: Primary keys and foreign keys indexed
- **Query Optimization**: Eager loading for relationships
- **JSON Field Usage**: Flexible data storage for template fields
- **Composite Indexes**: user_id + template_id combinations

### API Performance
- **Response Caching**: Template and user data caching
- **Pagination**: All list endpoints support pagination
- **Selective Loading**: Only required fields loaded
- **Efficient Queries**: N+1 query prevention

### Scalability Considerations
- **Horizontal Scaling**: Stateless API design
- **Database Sharding**: UUID-based partitioning ready
- **File Storage**: Separate storage layer for images
- **Background Jobs**: Heavy operations can be queued

---

## 🚀 DEPLOYMENT ARCHITECTURE

### Environment Configuration
```
Production Environment:
├── Application Server (Laravel)
├── Database Server (PostgreSQL)
├── File Storage (Local/S3)
├── Cache Layer (Redis - optional)
└── Load Balancer (if needed)
```

### Deployment Checklist
- [ ] **Environment Variables**: APP_KEY, DB credentials, etc.
- [ ] **Database Migration**: `php artisan migrate`
- [ ] **Seeder Execution**: `php artisan db:seed`
- [ ] **Storage Linking**: `php artisan storage:link`
- [ ] **Cache Optimization**: `php artisan config:cache`
- [ ] **Route Caching**: `php artisan route:cache`

### Monitoring Points
- **API Response Times**: < 300ms average
- **Database Queries**: Monitor N+1 issues
- **Authentication Failures**: Security monitoring
- **File Upload Performance**: < 1000ms for images
- **Error Rates**: < 1% error rate target

---

**✅ System Architecture complete with comprehensive database design, security layers, and scalability considerations.**