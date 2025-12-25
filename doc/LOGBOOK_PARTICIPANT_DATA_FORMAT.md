# Logbook Participant Data Format

## Overview
Sistem Logbook Participant menggunakan struktur data JSON yang fleksibel, dimana field-field yang diperlukan ditentukan oleh konfigurasi **Required Data Participant** dari masing-masing institution.

## Data Structure

### Database Schema
```sql
CREATE TABLE logbook_participants (
    id UUID PRIMARY KEY,
    template_id UUID NOT NULL,
    data JSONB NOT NULL,  -- Format: {"field_name": "value", ...}
    grade INTEGER NULL,    -- Range: 1-100
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES logbook_template(id)
);
```

### JSON Data Format
```json
{
    "Nama Lengkap": "John Doe",
    "NIM": "12345678",
    "Email": "john@example.com",
    "Nomor Telepon": "08123456789"
}
```

**Catatan Penting:**
- Keys dalam JSON diambil dari `required_data_participants.data_name`
- Jumlah field **bervariasi** tergantung konfigurasi institution
- Hanya field yang `is_active = true` yang digunakan

## Flow Diagram

```
1. Institution Admin → Required Data Participant Menu
   ↓
2. Tambah Required Data (e.g., "Nama Lengkap", "NIM", "Email")
   ↓
3. Saat Create Participant → Form fields dibuat dinamis
   ↓
4. Submit → Data disimpan sebagai JSON dengan keys dari required_data
   ↓
5. Display → Tabel kolom dinamis sesuai required_data
```

## Example Scenarios

### Scenario 1: University with 4 Required Fields

**Required Data Participant (Institution A):**
| data_name | is_active |
|-----------|-----------|
| Nama Lengkap | true |
| NIM | true |
| Email | true |
| Nomor Telepon | true |

**Stored Data:**
```json
{
    "Nama Lengkap": "John Doe",
    "NIM": "12345678",
    "Email": "john@example.com",
    "Nomor Telepon": "08123456789"
}
```

### Scenario 2: Company with 3 Required Fields

**Required Data Participant (Institution B):**
| data_name | is_active |
|-----------|-----------|
| Full Name | true |
| Employee ID | true |
| Department | true |

**Stored Data:**
```json
{
    "Full Name": "Jane Smith",
    "Employee ID": "EMP-001",
    "Department": "IT Support"
}
```

### Scenario 3: Hospital with Different Fields

**Required Data Participant (Institution C):**
| data_name | is_active |
|-----------|-----------|
| Nama Dokter | true |
| SIP Number | true |
| Spesialisasi | true |
| Rumah Sakit | true |
| Kontak | true |

**Stored Data:**
```json
{
    "Nama Dokter": "Dr. Ahmad",
    "SIP Number": "SIP-12345",
    "Spesialisasi": "Bedah",
    "Rumah Sakit": "RS Harapan",
    "Kontak": "08123456789"
}
```

## API Request Example

### Create Participant
```http
POST /api/logbook/participants
Authorization: Bearer {token}
Content-Type: application/json

{
    "template_id": "uuid-of-logbook-template",
    "data": {
        "Nama Lengkap": "John Doe",
        "NIM": "12345678",
        "Email": "john@example.com",
        "Nomor Telepon": "08123456789"
    },
    "grade": 85
}
```

### Response
```json
{
    "success": true,
    "message": "Participant created successfully",
    "data": {
        "id": "participant-uuid",
        "template_id": "template-uuid",
        "data": {
            "Nama Lengkap": "John Doe",
            "NIM": "12345678",
            "Email": "john@example.com",
            "Nomor Telepon": "08123456789"
        },
        "grade": 85,
        "created_at": "2025-12-21T10:00:00Z",
        "updated_at": "2025-12-21T10:00:00Z"
    }
}
```

## Frontend Implementation

### Dynamic Form Generation
```javascript
// Load required data fields from institution
const fields = await loadRequiredDataFields(institutionId);

// Generate form inputs
fields.forEach(field => {
    if (field.is_active) {
        createInput(field.data_name);
    }
});
```

### Data Collection
```javascript
const data = {};
inputs.forEach(input => {
    // Key = field.data_name from required_data_participants
    data[input.dataset.fieldName] = input.value;
});

// Result: { "Nama Lengkap": "John", "NIM": "123", ... }
```

### Dynamic Table Rendering
```javascript
// Header columns from required_data_participants
const columnHeaders = requiredDataFields.map(f => f.data_name);

// Render data in same order
participants.forEach(p => {
    columnHeaders.forEach(key => {
        renderCell(p.data[key]);
    });
});
```

## Validation Rules

### Backend (Laravel)
```php
$validator = Validator::make($request->all(), [
    'template_id' => 'required|uuid|exists:logbook_template,id',
    'data' => 'required|array|min:1',  // Flexible - any keys allowed
    'grade' => 'nullable|integer|min:1|max:100',
]);
```

### Frontend (JavaScript)
```javascript
// Minimum 1 field must be filled
if (Object.keys(data).length === 0) {
    throw new Error('Harap isi minimal satu field');
}
```

## Migration Path

### Initial Setup
1. Institution Admin creates Required Data Participants
2. System uses those fields for participant forms

### Updating Fields
1. Admin can add/remove required data fields
2. Existing participants retain their data structure
3. New participants use updated field configuration

### Data Consistency
- Old data remains intact (backward compatible)
- Display shows only available fields from current config
- Missing fields show as "-" in table

## Best Practices

1. **Naming Conventions**
   - Use clear, descriptive field names
   - Consistent language (all Indonesian or all English)
   - Example: "Nama Lengkap" not "nama" or "Name"

2. **Field Management**
   - Don't delete fields if historical data exists
   - Use `is_active = false` to hide fields
   - Add new fields as needed

3. **Data Entry**
   - Validate on frontend before submit
   - Trim whitespace from values
   - Handle empty values gracefully

4. **Display**
   - Use consistent column order (from required_data)
   - Show "-" for missing values
   - Format data appropriately (phone, email, etc.)

## Troubleshooting

### Issue: Validation error "data.name is required"
**Solution:** Updated validation to accept any field structure. No longer requires specific "name" field.

### Issue: Table columns don't match data
**Solution:** Table now uses required_data_participants for column order. Ensures consistency.

### Issue: No fields showing in form
**Solution:** Check if institution has active required_data_participants. Add fields if needed.

### Issue: Old participants missing new fields
**Expected behavior:** New fields only apply to new participants. Old data preserved as-is.

## Related Documentation
- [Required Data Participant System](./REQUIRED_DATA_PARTICIPANT.md)
- [Logbook Management Guide](./LOGBOOK_MANAGEMENT.md)
- [API Reference](./API_REFERENCE_GUIDE.md)
