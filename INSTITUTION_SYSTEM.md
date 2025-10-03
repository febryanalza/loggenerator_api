# Institution System Implementation

## Overview
Implementasi sistem institusi untuk mengelola client institusi yang bekerja sama dengan aplikasi logbook. Sistem ini memungkinkan:
- Manajemen institusi dengan admin khusus
- Template logbook yang terikat dengan institusi tertentu
- User dengan role `institution_admin` yang memiliki akses penuh ke template institusinya

## Database Schema Changes

### 1. Tabel Institutions
```sql
CREATE TABLE institutions (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### 2. Kolom institution_id di tabel users
- Kolom: `institution_id UUID NULL`
- Foreign Key: references `institutions(id)` ON DELETE SET NULL
- Default: NULL (untuk user umum)

### 3. Kolom institution_id di tabel logbook_template
- Kolom: `institution_id UUID NULL`
- Foreign Key: references `institutions(id)` ON DELETE SET NULL
- Default: NULL (untuk template global)

## Models

### Institution Model
- Path: `App\Models\Institution`
- Features:
  - UUID primary key
  - Relations dengan Users dan LogbookTemplates
  - Scopes untuk counting users dan templates

### User Model Updates
- Tambahan field `institution_id` di fillable
- Relasi `belongsTo` dengan Institution
- Method helper: `isInstitutionAdmin()`, `belongsToInstitution()`

### LogbookTemplate Model Updates
- Tambahan field `institution_id` di fillable
- Relasi `belongsTo` dengan Institution
- Scopes: `forInstitution()`, `global()`

## Role & Permissions

### Institution Admin Role
- Role name: `Institution Admin`
- Permissions:
  - Template management: `view_templates`, `create_templates`, `edit_templates`, `delete_templates`
  - User management: `view_institution_users`, `create_institution_users`, `edit_institution_users`, `delete_institution_users`
  - Logbook data: `view_logbook_data`, `create_logbook_data`, `edit_logbook_data`, `delete_logbook_data`
  - Institution specific: `manage_institution_templates`, `assign_template_access`, `view_institution_reports`

## API Changes

### Route Updates
- `/admin/users` routes sekarang accessible oleh `Super Admin` dan `Admin`
- Institution Admin dapat dibuat melalui route `/admin/users`

### UserManagementController Updates

#### Create User Endpoint
- Support role `Institution Admin`
- Required `institution_id` untuk role `Institution Admin`
- Validasi institution exists
- Response includes institution info

#### Get Users Endpoint
- Shows institution information for users
- Accessible by both Super Admin and Admin

## Usage Examples

### 1. Membuat Institution
```php
$institution = Institution::create([
    'name' => 'Universitas Indonesia',
    'description' => 'Institusi pendidikan tinggi'
]);
```

### 2. Membuat Institution Admin via API
```bash
POST /api/admin/users
{
    "name": "Admin UI",
    "email": "admin@ui.ac.id",
    "password": "password123",
    "role": "Institution Admin",
    "institution_id": "uuid-of-institution"
}
```

### 3. Template untuk Institution
```php
$template = LogbookTemplate::create([
    'name' => 'Template Praktek UI',
    'description' => 'Template khusus untuk UI',
    'institution_id' => $institution->id
]);
```

### 4. Query Templates by Institution
```php
// Templates untuk institution tertentu
$institutionTemplates = LogbookTemplate::forInstitution($institutionId)->get();

// Templates global (tidak terikat institution)
$globalTemplates = LogbookTemplate::global()->get();
```

## Migration Files Created
1. `2025_10_01_143849_create_institutions_table.php`
2. `2025_10_01_144022_add_institution_id_to_logbook_templates_table.php`
3. `2025_10_01_144052_add_institution_id_to_users_table.php`

## Seeder Created
- `InstitutionAdminRoleSeeder.php` - Creates institution_admin role with permissions

## Logic Flow

### Institution Admin Access Control
1. Institution Admin hanya dapat mengelola template dengan `institution_id` yang sama
2. Institution Admin hanya dapat mengelola user dalam institusinya
3. Super Admin dan Admin dapat mengelola semua institution admin
4. Template dengan `institution_id = NULL` adalah template global

### User Role Hierarchy
1. **Super Admin**: Akses penuh ke semua fitur dan institusi
2. **Admin**: Dapat mengelola user dan membuat institution admin
3. **Institution Admin**: Akses penuh ke template dan user dalam institusinya
4. **Manager/User**: Akses terbatas sesuai permission existing

## Testing
Script test tersedia di `test_institution_system.php` untuk memverifikasi:
- Institution creation
- Role assignment
- Relations
- Scopes and queries
- Data cleanup

## Future Enhancements
1. Institution-specific permissions configuration
2. Template sharing between institutions
3. Institution admin dashboard
4. Bulk user import for institutions
5. Institution usage analytics