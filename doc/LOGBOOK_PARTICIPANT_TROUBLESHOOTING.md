# Logbook Participant - Troubleshooting Guide

## Error: "Failed to create participant"

### Root Cause Analysis
Institution Admin tidak memiliki akses ke endpoint `/api/logbook/participants` karena endpoint tersebut memerlukan middleware `logbook.access:Owner`. Institution Admin yang bukan Owner dari logbook template tidak bisa mengakses endpoint ini.

### Solution Implemented

#### 1. **API Endpoint Changes**
Institution Admin sekarang menggunakan endpoint yang benar:
- ❌ `/api/logbook/participants` (untuk Owner dengan logbook.access middleware)
- ✅ `/api/participants` (untuk Institution Admin dengan permission middleware)

**Files Changed:**
- `resources/views/institution_admin/logbook_detail.blade.php`
  - loadParticipants: `/api/logbook/participants/list` → `/api/participants/list`
  - submitParticipant: `/api/logbook/participants` → `/api/participants`
  - deleteParticipant: `/api/logbook/participants/{id}` → `/api/participants/{id}`

#### 2. **Permission Updates**
Menambahkan permission `participants.manage` ke Institution Admin role.

**Files Changed:**
- `database/migrations/2025_12_20_000001_add_granular_permissions.php`
  - Added `participants.*` permissions to Institution Admin

#### 3. **Artisan Command**
Dibuat command untuk memberikan permission ke role yang sudah ada:
```bash
php artisan permission:grant-participant-permissions
```

**File Created:**
- `app/Console/Commands/GrantParticipantPermissions.php`

### Steps to Fix

#### Option 1: Run Artisan Command (Recommended)
```bash
php artisan permission:grant-participant-permissions
```

#### Option 2: Re-run Migration
```bash
php artisan migrate:refresh --seed
```
⚠️ **Warning:** This will delete all existing data!

#### Option 3: Manual Database Update
```sql
-- Get permission IDs
SELECT id, name FROM permissions WHERE name LIKE 'participants.%';

-- Get Institution Admin role ID
SELECT id FROM roles WHERE name = 'Institution Admin';

-- Grant permissions (replace {role_id} and {permission_id})
INSERT INTO role_has_permissions (permission_id, role_id) 
VALUES 
  ({participants.view_id}, {institution_admin_id}),
  ({participants.create_id}, {institution_admin_id}),
  ({participants.update_id}, {institution_admin_id}),
  ({participants.delete_id}, {institution_admin_id}),
  ({participants.manage_id}, {institution_admin_id});
```

### API Routes Structure

#### For Institution Admin (Permission-based)
```
POST   /api/participants              - Create participant
GET    /api/participants              - List participants  
GET    /api/participants/list         - List with details
GET    /api/participants/{id}         - Show participant
PUT    /api/participants/{id}         - Update participant
DELETE /api/participants/{id}         - Delete participant
PATCH  /api/participants/{id}/grade   - Update grade
```

#### For Logbook Owner (Role-based)
```
POST   /api/logbook/participants              - Create participant
GET    /api/logbook/participants              - List participants
GET    /api/logbook/participants/list         - List with details
GET    /api/logbook/participants/{id}         - Show participant
PUT    /api/logbook/participants/{id}         - Update participant
DELETE /api/logbook/participants/{id}         - Delete participant
```

#### For Logbook Supervisor (Role-based)
```
GET    /api/logbook/participants/view         - List participants
GET    /api/logbook/participants/view/{id}    - Show participant
PATCH  /api/logbook/participants/{id}/grade   - Update grade only
```

### Laravel Log Files Location

#### Windows (Local Development)
```
D:\Belajar_Bebas\Project\loggeneratorproject\loggenerator_api\storage\logs\laravel.log
```

#### Linux (Production/Server)
```
/path/to/your/project/storage/logs/laravel.log
```

#### View Logs via Artisan
```bash
# View latest log entries
php artisan tail

# Or manually
tail -f storage/logs/laravel.log

# View specific number of lines
tail -n 100 storage/logs/laravel.log
```

#### Clear Old Logs
```bash
# Delete all logs
rm storage/logs/*.log

# Or create fresh log file
> storage/logs/laravel.log
```

### Testing After Fix

1. **Login as Institution Admin**
2. **Open Browser Console** (F12)
3. **Navigate to Logbook Detail page**
4. **Click "Tambah Participant"**
5. **Fill the form and submit**
6. **Check console for:**
   - Request URL: Should be `/api/participants`
   - Response: Should be `{success: true, ...}`

### Expected Console Output

#### Success
```javascript
Sending participant data: {
  template_id: "uuid-here",
  data: {
    "Nama Lengkap": "John Doe",
    "NIM": "12345"
  },
  grade: 85
}

Response: {
  success: true,
  message: "Participant created successfully",
  data: {...}
}
```

#### Before Fix (Error)
```javascript
Response: {
  success: false,
  message: "Insufficient logbook access. You do not have required access to this template.",
  required_access: "One of roles [Owner] for template uuid"
}
```

### Verification Checklist

- [ ] Institution Admin can access `/institution-admin/logbooks/detail?id={uuid}`
- [ ] "Tambah Participant" button visible
- [ ] Modal opens with dynamic fields from required_data_participants
- [ ] Submit sends to `/api/participants` (not `/api/logbook/participants`)
- [ ] Success message appears
- [ ] Participant appears in table
- [ ] Console shows no errors

### Related Files

**Backend:**
- `routes/api.php` - API route definitions
- `app/Http/Controllers/Api/LogbookParticipantController.php` - Controller
- `app/Http/Middleware/CheckLogbookAccess.php` - Access middleware
- `database/migrations/2025_12_20_000001_add_granular_permissions.php` - Permissions
- `database/migrations/2025_12_21_091129_create_logbook_participants_table.php` - Table structure

**Frontend:**
- `resources/views/institution_admin/logbook_detail.blade.php` - Detail page
- `resources/views/institution_admin/logbooks.blade.php` - List page

**Documentation:**
- `doc/LOGBOOK_PARTICIPANT_DATA_FORMAT.md` - Data format guide
- `doc/LOGBOOK_PARTICIPANT_TROUBLESHOOTING.md` - This file

### Additional Debugging

If issue persists, check:

1. **Permission exists:**
   ```sql
   SELECT * FROM permissions WHERE name = 'participants.manage';
   ```

2. **Role has permission:**
   ```sql
   SELECT r.name, p.name 
   FROM roles r
   JOIN role_has_permissions rhp ON r.id = rhp.role_id
   JOIN permissions p ON rhp.permission_id = p.id
   WHERE r.name = 'Institution Admin' 
     AND p.name LIKE 'participants.%';
   ```

3. **User has role:**
   ```sql
   SELECT u.email, r.name
   FROM users u
   JOIN model_has_roles mhr ON u.id = mhr.model_id
   JOIN roles r ON mhr.role_id = r.id
   WHERE u.email = 'your-institution-admin@email.com';
   ```

4. **Check middleware in route:**
   ```bash
   php artisan route:list | grep participants
   ```

### Support
For additional help, check Laravel log at:
```
storage/logs/laravel.log
```
