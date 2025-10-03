# Verification and Assessment System

## Overview
Sistem verifikasi dan assessment untuk logbook yang memungkinkan:
- Owner dan Supervisor memverifikasi user dalam template logbook
- Institution Admin melakukan assessment template setelah semua Owner/Supervisor terverifikasi

## Database Schema Changes

### 1. Kolom has_been_verified di user_logbook_access
```sql
ALTER TABLE user_logbook_access 
ADD COLUMN has_been_verified BOOLEAN DEFAULT FALSE;
```

**Ketentuan:**
- Default value: `false`
- Hanya dapat diubah oleh user dengan role **Owner** atau **Supervisor**
- Digunakan untuk memverifikasi user lain dalam template yang sama

### 2. Kolom has_been_assessed di logbook_template
```sql
ALTER TABLE logbook_template 
ADD COLUMN has_been_assessed BOOLEAN DEFAULT FALSE;
```

**Ketentuan:**
- Default value: `false`
- Hanya dapat diubah oleh **Institution Admin**
- Hanya dapat diset `true` jika semua Owner dan Supervisor sudah terverifikasi

## API Endpoints

### 1. Update Verification Status
**Endpoint:** `PUT /api/logbook/verification`

**Access:** Authenticated users dengan role Owner atau Supervisor pada template terkait

**Request Body:**
```json
{
    "template_id": "uuid-template-id",
    "user_id": "uuid-user-id",
    "has_been_verified": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Verification status updated successfully",
    "data": {
        "template_id": "uuid-template-id",
        "user_id": "uuid-user-id", 
        "has_been_verified": true,
        "updated_by": "John Doe",
        "updated_at": "2025-10-01T15:30:00.000000Z"
    }
}
```

### 2. Get Verification Status
**Endpoint:** `GET /api/logbook/verification/{templateId}`

**Access:** Authenticated users yang memiliki akses ke template

**Response:**
```json
{
    "success": true,
    "message": "Verification statuses retrieved successfully",
    "data": {
        "template_id": "uuid-template-id",
        "verification_statuses": [
            {
                "user_id": "uuid-user-1",
                "user_name": "John Doe",
                "user_email": "john@example.com",
                "role_name": "Owner",
                "has_been_verified": true,
                "updated_at": "2025-10-01T15:30:00.000000Z"
            },
            {
                "user_id": "uuid-user-2",
                "user_name": "Jane Smith",
                "user_email": "jane@example.com",
                "role_name": "Supervisor",
                "has_been_verified": false,
                "updated_at": null
            }
        ]
    }
}
```

### 3. Update Assessment Status
**Endpoint:** `PUT /api/logbook/assessment`

**Access:** Institution Admin only

**Request Body:**
```json
{
    "template_id": "uuid-template-id",
    "has_been_assessed": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Assessment status updated successfully",
    "data": {
        "template_id": "uuid-template-id",
        "template_name": "Template Name",
        "has_been_assessed": true,
        "assessed_by": "Institution Admin",
        "updated_at": "2025-10-01T15:35:00.000000Z"
    }
}
```

## Business Logic

### Verification Workflow
1. **Owner** dan **Supervisor** dapat memverifikasi user lain dalam template yang sama
2. Status verifikasi disimpan dalam kolom `has_been_verified` di tabel `user_logbook_access`
3. Owner/Supervisor dapat memverifikasi diri sendiri atau user lain
4. Hanya Owner/Supervisor yang dapat mengubah status verifikasi

### Assessment Workflow
1. **Institution Admin** dapat melakukan assessment pada template dalam institusinya
2. Assessment hanya dapat dilakukan jika semua Owner dan Supervisor sudah terverifikasi
3. Status assessment disimpan dalam kolom `has_been_assessed` di tabel `logbook_template`
4. Institution Admin hanya dapat mengassess template dari institusi yang sama

### Validation Rules

#### Update Verification Status
- `template_id`: required, UUID, must exist in logbook_template
- `user_id`: required, UUID, must exist in users
- `has_been_verified`: required, boolean
- Current user must have Owner or Supervisor role in the template
- Target user must have access to the template

#### Update Assessment Status  
- `template_id`: required, UUID, must exist in logbook_template
- `has_been_assessed`: required, boolean
- Current user must have Institution Admin role
- Institution Admin must belong to same institution as template
- If setting to `true`: all Owner/Supervisor must be verified first

## Controller Implementation

### LogbookVerificationController
**Path:** `app/Http/Controllers/Api/LogbookVerificationController.php`

**Methods:**
- `updateVerificationStatus()` - Update verification status by Owner/Supervisor
- `getVerificationStatus()` - Get verification status for a template
- `updateAssessmentStatus()` - Update assessment status by Institution Admin

## Model Updates

### LogbookTemplate Model
**Added:**
- `has_been_assessed` to fillable array
- Boolean cast for `has_been_assessed`

## Migration Files
1. `2025_10_01_152839_add_has_been_verified_to_user_logbook_access_table.php`
2. `2025_10_01_152944_add_has_been_assessed_to_logbook_template_table.php`

## Security Features

### Access Control
- **Verification**: Only Owner/Supervisor can update verification status
- **Assessment**: Only Institution Admin can update assessment status
- **Institution Isolation**: Institution Admin can only assess templates from their institution

### Audit Logging
- All verification updates logged with action `UPDATE_VERIFICATION`
- All assessment updates logged with action `UPDATE_ASSESSMENT`
- Logs include user details, template info, and changes made

## Error Handling

### Common Error Responses

**Unauthorized Access:**
```json
{
    "success": false,
    "message": "Unauthorized. Only Owner and Supervisor can update verification status.",
    "status": 403
}
```

**Validation Error:**
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "template_id": ["The template id field is required."]
    },
    "status": 422
}
```

**Assessment Prerequisites Not Met:**
```json
{
    "success": false,
    "message": "Cannot assess template. Some Owner/Supervisor users have not verified yet.",
    "unverified_count": 2,
    "status": 422
}
```

## Usage Examples

### Frontend Implementation Example

```javascript
// Update verification status
const updateVerification = async (templateId, userId, verified) => {
    const response = await fetch('/api/logbook/verification', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            template_id: templateId,
            user_id: userId,
            has_been_verified: verified
        })
    });
    return response.json();
};

// Get verification status
const getVerificationStatus = async (templateId) => {
    const response = await fetch(`/api/logbook/verification/${templateId}`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};

// Update assessment status (Institution Admin only)
const updateAssessment = async (templateId, assessed) => {
    const response = await fetch('/api/logbook/assessment', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            template_id: templateId,
            has_been_assessed: assessed
        })
    });
    return response.json();
};
```

## Testing
Test script tersedia di `test_verification_system.php` untuk memverifikasi:
- Kolom database dan default values
- Role-based access control
- Verification workflow
- Assessment workflow
- Business logic validation
- Data cleanup

## Future Enhancements
1. Email notifications saat verification/assessment diupdate
2. Bulk verification untuk multiple users
3. Verification history tracking
4. Dashboard untuk Institution Admin
5. Reporting untuk verification statistics