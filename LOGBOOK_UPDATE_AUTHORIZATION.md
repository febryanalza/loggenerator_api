# LogbookDataController Update Authorization

## Overview
Telah dilakukan perubahan pada authorization logic untuk method `update` di `LogbookDataController`. Sekarang tidak hanya writer asli yang dapat mengupdate logbook entries, tetapi juga user dengan logbook roles **Owner** dan **Editor**.

## Changes Made

### 1. Import Model
Added `UserLogbookAccess` model import:
```php
use App\Models\UserLogbookAccess;
```

### 2. Updated Authorization Logic

**Before:**
```php
// Check if user can update this entry (only writer)
$user = Auth::user();
if ($logbookData->writer_id !== $user->id) {
    return response()->json([
        'success' => false,
        'message' => 'You do not have permission to update this entry'
    ], 403);
}
```

**After:**
```php
// Check if user can update this entry (Owner, Editor, or original writer)
$user = Auth::user();

// Check if user has Owner or Editor role for this template
$userAccess = UserLogbookAccess::where('user_id', $user->id)
    ->where('logbook_template_id', $logbookData->template_id)
    ->with('logbookRole')
    ->first();

$canUpdate = false;

// Allow if user is the original writer
if ($logbookData->writer_id === $user->id) {
    $canUpdate = true;
}

// Allow if user has Owner or Editor role for this template
if ($userAccess && in_array($userAccess->logbookRole->name, ['Owner', 'Editor'])) {
    $canUpdate = true;
}

if (!$canUpdate) {
    return response()->json([
        'success' => false,
        'message' => 'You do not have permission to update this entry. Only the original writer, Owner, or Editor can update logbook entries.'
    ], 403);
}
```

### 3. Enhanced Audit Logging

**Before:**
```php
AuditLog::create([
    'user_id' => Auth::id(),
    'action' => 'UPDATE_LOGBOOK_ENTRY',
    'description' => 'Updated logbook entry for ' . $logbookData->template->name,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

**After:**
```php
// Determine update context for audit log
$updateContext = '';
if ($logbookData->writer_id === $user->id) {
    $updateContext = ' (as original writer)';
} elseif ($userAccess && $userAccess->logbookRole->name === 'Owner') {
    $updateContext = ' (as Owner)';
} elseif ($userAccess && $userAccess->logbookRole->name === 'Editor') {
    $updateContext = ' (as Editor)';
}

// Create audit log
AuditLog::create([
    'user_id' => Auth::id(),
    'action' => 'UPDATE_LOGBOOK_ENTRY',
    'description' => "Updated logbook entry for {$logbookData->template->name}{$updateContext}. Original writer: {$logbookData->writer->name}",
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

## Authorization Rules

### Who Can Update Logbook Entries:

✅ **Original Writer** (any role)
- Can always update entries they created themselves
- Maintains ownership of their own work

✅ **Owner** (regardless of who wrote the entry)
- Can update any entry in templates they have Owner access to
- Full editing authority within their templates

✅ **Editor** (regardless of who wrote the entry)  
- Can update any entry in templates they have Editor access to
- Designated editing role with broad permissions

❌ **Supervisor** 
- Cannot update entries (role focused on verification, not editing)
- Can only verify logbooks after data completion

❌ **Viewer**
- Cannot update entries (unless they are the original writer)
- Read-only access by default

## API Impact

### Endpoint: `PUT /api/logbook-entries/{id}`

**Authorization Flow:**
1. Check if current user is the original writer → **Allow**
2. Check if current user has **Owner** role for the template → **Allow**  
3. Check if current user has **Editor** role for the template → **Allow**
4. Otherwise → **Deny with 403 error**

**Error Response:**
```json
{
    "success": false,
    "message": "You do not have permission to update this entry. Only the original writer, Owner, or Editor can update logbook entries."
}
```

## Audit Trail Enhancement

Audit logs now include context about who performed the update:

**Examples:**
- `"Updated logbook entry for Daily Report (as original writer). Original writer: John Doe"`
- `"Updated logbook entry for Daily Report (as Owner). Original writer: Jane Smith"`
- `"Updated logbook entry for Daily Report (as Editor). Original writer: Bob Wilson"`

## Use Cases

### Scenario 1: Content Management
- **Owner** can edit any entry to maintain quality standards
- **Editor** can fix typos, update formatting, add missing information
- **Original Writer** retains ability to edit their own work

### Scenario 2: Collaborative Editing
- Multiple **Editors** can refine entries created by **Viewers**
- **Owner** has final say on all content
- **Supervisor** remains focused on verification workflow

### Scenario 3: Quality Control
- **Owner** can correct errors in any entry
- **Editor** can standardize formatting across entries
- Clear audit trail shows who made what changes

## Testing

Comprehensive test script `test_update_authorization.php` validates:
- ✅ Original writer can update own entries
- ✅ Owner can update any entry in template
- ✅ Editor can update any entry in template  
- ✅ Supervisor cannot update entries
- ✅ Regular Viewers cannot update entries (unless original writer)
- ✅ Audit logging works correctly with role context

## Security Considerations

### Template Isolation
- Users can only update entries in templates they have access to
- Cross-template access is prevented

### Role Validation
- Role checking is performed against `UserLogbookAccess` table
- Ensures users have legitimate access to the template

### Audit Compliance
- All updates are logged with user context
- Original writer information is preserved
- Role-based action tracking

## Migration Path

### For Existing Systems:
1. No database changes required
2. Controller update is backward compatible
3. Original writers retain their update permissions
4. New role-based permissions are additive

### For New Implementations:
1. Assign **Owner** roles to template managers
2. Assign **Editor** roles to content editors
3. Assign **Viewer** roles to data entry users
4. **Supervisor** roles for verification workflow

This change provides more flexible content management while maintaining security and audit compliance.