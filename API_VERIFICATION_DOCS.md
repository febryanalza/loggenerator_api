# API Documentation: Logbook Verification (New Workflow)

## Overview
API controller untuk mengelola status verifikasi logbook dengan alur sequential yang baru:
1. **Owner** memverifikasi logbook setelah semua data selesai diinput
2. **Supervisor** memverifikasi logbook setelah Owner memverifikasi
3. **Institution Admin** dapat melakukan assessment setelah Owner dan Supervisor memverifikasi

Kolom yang digunakan: `has_been_verified_logbook` (menggantikan `has_been_verified`)

## Endpoints

### 1. Update Verification Status
**Endpoint:** `PUT /api/logbook/verification`

**Authentication:** Required (Bearer Token)

**Authorization:** User harus memiliki role **Owner** atau **Supervisor** pada template yang akan diupdate

**Request Body:**
```json
{
    "template_id": "uuid-template-id",
    "has_been_verified_logbook": true
}
```

**Request Parameters:**
- `template_id` (required, UUID): ID template logbook
- `has_been_verified_logbook` (required, boolean): Status verifikasi logbook (true/false)

**Sequential Rules:**
- **Owner** dapat memverifikasi kapan saja setelah data logbook lengkap
- **Supervisor** hanya dapat memverifikasi setelah Owner sudah memverifikasi
- **Institution Admin** dapat assessment hanya setelah Owner dan Supervisor memverifikasi

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logbook verification status updated successfully",
    "data": {
        "template_id": "uuid-template-id",
        "template_name": "Template Name",
        "user_role": "Owner",
        "user_name": "Current User Name",
        "has_been_verified_logbook": true,
        "updated_at": "2025-10-01T12:00:00.000000Z"
    }
}
```

**Error Responses:**

**Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "template_id": ["Template ID is required."],
        "has_been_verified_logbook": ["Verification status is required."]
    }
}
```

**Unauthorized Access (403):**
```json
{
    "success": false,
    "message": "Unauthorized. Only Owner and Supervisor can update verification status."
}
```

**Template Access Denied (403):**
```json
{
    "success": false,
    "message": "You do not have access to this logbook template."
}
```

**Sequential Verification Error (422):**
```json
{
    "success": false,
    "message": "Supervisor cannot verify until Owner has verified first."
}
```

**Server Error (500):**
```json
{
    "success": false,
    "message": "An error occurred while updating verification status",
    "error": "Error message details"
}
```

### 2. Get Verification Status
**Endpoint:** `GET /api/logbook/verification/{templateId}`

**Authentication:** Required (Bearer Token)

**Authorization:** User harus memiliki akses ke template

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logbook verification status retrieved successfully",
    "data": {
        "template_id": "uuid-template-id",
        "template_name": "Template Name",
        "has_been_assessed": false,
        "owner_verification": {
            "user_id": "uuid-owner-id",
            "user_name": "John Doe",
            "user_email": "john@example.com",
            "has_been_verified_logbook": true,
            "updated_at": "2025-10-01T12:00:00.000000Z"
        },
        "supervisor_verification": {
            "user_id": "uuid-supervisor-id",
            "user_name": "Jane Smith", 
            "user_email": "jane@example.com",
            "has_been_verified_logbook": false,
            "updated_at": null
        },
        "assessment_ready": false,
        "verification_workflow": {
            "step_1_owner": "completed",
            "step_2_supervisor": "ready",
            "step_3_assessment": "waiting_for_verification"
        }
    }
}
```

## Business Rules

### Role-Based Access Control
- **Owner**: Dapat memverifikasi semua user dalam template (termasuk diri sendiri)
- **Supervisor**: Dapat memverifikasi semua user dalam template (termasuk diri sendiri)
- **Editor/Viewer**: Tidak dapat memverifikasi user manapun

### Verification Rules
1. User harus memiliki akses ke template untuk dapat memverifikasi
2. Target user yang akan diverifikasi harus memiliki akses ke template yang sama
3. Hanya Owner dan Supervisor yang dapat mengupdate status verifikasi
4. Status verifikasi dapat diubah dari `false` ke `true` atau sebaliknya

### Audit Logging
Setiap perubahan status verifikasi akan dicatat dalam audit log dengan:
- `action`: `UPDATE_VERIFICATION`
- `table_name`: `user_logbook_access`
- `record_id`: ID dari user logbook access yang diupdate
- `old_values`: Nilai lama `has_been_verified`
- `new_values`: Nilai baru `has_been_verified`
- `description`: Deskripsi lengkap perubahan

## Implementation Details

### Controller
**File:** `app/Http/Controllers/Api/LogbookVerificationController.php`

**Methods:**
- `updateVerificationStatus()`: Update status verifikasi
- `getVerificationStatus()`: Mendapatkan status verifikasi semua user dalam template

### Request Validation
**File:** `app/Http/Requests/UpdateVerificationStatusRequest.php`

**Validation Rules:**
- `template_id`: required, UUID, exists in logbook_template
- `user_id`: required, UUID, exists in users  
- `has_been_verified`: required, boolean

### Database Schema
**Table:** `user_logbook_access`

**Column:** `has_been_verified`
- Type: BOOLEAN
- Default: FALSE
- Nullable: NO

## Usage Examples

### JavaScript/Frontend
```javascript
// Update verification status
const updateVerification = async (templateId, userId, verified) => {
    try {
        const response = await fetch('/api/logbook/verification', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                template_id: templateId,
                user_id: userId,
                has_been_verified: verified
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Verification updated:', result.data);
            return result.data;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error updating verification:', error);
        throw error;
    }
};

// Get verification status
const getVerificationStatus = async (templateId) => {
    try {
        const response = await fetch(`/api/logbook/verification/${templateId}`, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result.data.verification_statuses;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error getting verification status:', error);
        throw error;
    }
};
```

### cURL Examples
```bash
# Update verification status
curl -X PUT http://localhost:8000/api/logbook/verification \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -d '{
    "template_id": "uuid-template-id",
    "user_id": "uuid-user-id",
    "has_been_verified": true
  }'

# Get verification status
curl -X GET http://localhost:8000/api/logbook/verification/uuid-template-id \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN" \
  -H "Accept: application/json"
```

## Security Considerations

### Authentication
- Semua endpoint memerlukan authentication dengan Bearer token
- Token harus valid dan tidak expired

### Authorization
- Role-based access control diterapkan secara ketat
- User hanya dapat mengakses template yang memiliki akses
- Hanya Owner dan Supervisor yang dapat mengupdate status verifikasi

### Input Validation  
- Semua input divalidasi menggunakan Laravel Form Request
- UUID format validation untuk template_id dan user_id
- Boolean validation untuk has_been_verified

### Data Integrity
- Transaction digunakan untuk memastikan konsistensi data
- Audit logging untuk tracking semua perubahan
- Error handling yang komprehensif

## Testing
Test script tersedia di `test_verification_api.php` untuk memverifikasi:
- Database operations
- Role-based access control
- Business logic validation
- Error handling
- Data cleanup

Run test dengan perintah:
```bash
php test_verification_api.php
```