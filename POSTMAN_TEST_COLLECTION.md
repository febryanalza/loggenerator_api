# 📬 POSTMAN TEST COLLECTION - LOGBOOK ENTRIES

## 🔐 **Setup Authentication**

### 1. Login untuk mendapatkan token
```
POST {{base_url}}/api/login
```

**Request Body:**
```json
{
    "email": "admin@loggenerator.com",
    "password": "password",
    "device_name": "Postman Testing"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "IT Administrator",
            "email": "admin@loggenerator.com",
            "status": "active"
        },
        "token": "1|abcd1234efgh5678ijkl9012mnop3456qrst7890"
    }
}
```

**⚠️ IMPORTANT**: Copy token dan set sebagai Bearer Token di Authorization tab.

---

## 📋 **Test Logbook Entry Creation**

### 2. Get Template untuk melihat fields yang required
```
GET {{base_url}}/api/templates/{{template_id}}
```

**Headers:**
```
Authorization: Bearer {{your_token}}
Accept: application/json
```

### 3. Create Logbook Entry - Text Fields Only
```
POST {{base_url}}/api/logbook-entries
```

**Headers:**
```
Authorization: Bearer {{your_token}}
Content-Type: application/json
Accept: application/json
```

**Request Body Example 1 - Daily Production Log:**
```json
{
    "template_id": "172c162e-a9c4-41ad-ba3f-cef0e1878f20",
    "data": {
        "shift": "Morning Shift",
        "operator_name": "John Doe",
        "production_line": "Line A",
        "start_time": "08:00",
        "end_time": "16:00",
        "target_quantity": "1000",
        "actual_quantity": "950",
        "quality_check": "Pass",
        "notes": "Minor delay due to equipment maintenance",
        "date": "2025-09-27"
    }
}
```

**Request Body Example 2 - Quality Control Checklist:**
```json
{
    "template_id": "e6c756f1-ebae-4269-b42c-316335493789",
    "data": {
        "inspector_name": "Jane Smith",
        "inspection_date": "2025-09-27",
        "inspection_time": "14:30",
        "product_batch": "BATCH-2025-0927-001",
        "temperature": "25.5",
        "humidity": "60",
        "visual_inspection": "Approved",
        "weight_check": "Within tolerance",
        "color_check": "Consistent",
        "packaging_check": "Sealed properly",
        "overall_result": "Pass",
        "remarks": "All parameters within specification"
    }
}
```

### 4. Create Logbook Entry - With Image (Base64)
```
POST {{base_url}}/api/logbook-entries
```

**Request Body Example 3 - With Image Field:**
```json
{
    "template_id": "172c162e-a9c4-41ad-ba3f-cef0e1878f20",
    "data": {
        "shift": "Evening Shift",
        "operator_name": "Mike Johnson",
        "production_line": "Line B",
        "start_time": "16:00",
        "end_time": "00:00",
        "target_quantity": "800",
        "actual_quantity": "820",
        "quality_check": "Pass",
        "notes": "Exceeded target production",
        "date": "2025-09-27",
        "equipment_photo": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k="
    }
}
```

### 5. Create Logbook Entry - With Multiple Data Types
```
POST {{base_url}}/api/logbook-entries
```

**Request Body Example 4 - Mixed Data Types:**
```json
{
    "template_id": "your-template-id-here",
    "data": {
        "text_field": "Sample text data",
        "number_field": "123.45",
        "date_field": "2025-09-27",
        "time_field": "14:30",
        "image_field": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD...",
        "description": "Complete test entry with all field types",
        "status": "Active",
        "priority": "High"
    }
}
```

---

## ✅ **Expected Success Response**

```json
{
    "success": true,
    "message": "Logbook entry created successfully",
    "data": {
        "id": "01998b87-bd24-707b-8a75-abd852b19768",
        "template_id": "172c162e-a9c4-41ad-ba3f-cef0e1878f20",
        "writer": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "IT Administrator",
            "email": "admin@loggenerator.com"
        },
        "data": {
            "shift": "Morning Shift",
            "operator_name": "John Doe",
            "production_line": "Line A",
            "start_time": "08:00",
            "end_time": "16:00",
            "target_quantity": "1000",
            "actual_quantity": "950",
            "quality_check": "Pass",
            "notes": "Minor delay due to equipment maintenance",
            "date": "2025-09-27"
        },
        "created_at": "2025-09-27T14:30:00.000000Z",
        "updated_at": "2025-09-27T14:30:00.000000Z"
    }
}
```

---

## ❌ **Common Error Responses**

### 1. **Missing Template ID:**
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "template_id": ["The template id field is required."]
    }
}
```

### 2. **Template Not Found:**
```json
{
    "success": false,
    "message": "Failed to create logbook entry",
    "error": "No query results for model [App\\Models\\LogbookTemplate] xxx"
}
```

### 3. **Missing Required Fields:**
```json
{
    "success": false,
    "message": "Missing required fields",
    "missing_fields": ["operator_name", "production_line", "start_time"]
}
```

### 4. **Access Permission Error:**
```json
{
    "success": false,
    "message": "Insufficient logbook access. You do not have required access to this template.",
    "required_access": "Role: Editor,Supervisor,Owner for template",
    "user_template_access": []
}
```

### 5. **Authentication Error:**
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

---

## 🧪 **Testing Steps**

### Step 1: Setup Environment Variables
```
base_url = http://localhost/loggenerator_api
token = (akan didapat dari login response)
```

### Step 2: Test Authentication
1. Test login endpoint
2. Copy token dari response
3. Set token di Authorization tab (Bearer Token)

### Step 3: Get Template Information
1. Call GET `/api/templates` untuk melihat available templates
2. Pilih template_id yang akan digunakan
3. Call GET `/api/templates/{template_id}` untuk melihat fields yang required

### Step 4: Test Entry Creation
1. Mulai dengan data sederhana (text fields only)
2. Test dengan missing fields (harus error)
3. Test dengan complete data
4. Test dengan image data (base64)

### Step 5: Verify Results
1. Call GET `/api/logbook-entries/template/{template_id}` untuk melihat entries
2. Verify data tersimpan dengan benar

---

## 🔧 **Troubleshooting**

### Problem: "Insufficient logbook access"
**Solution:** 
- Pastikan user memiliki role Editor/Supervisor/Owner untuk template
- Run script: `php quick_fix_logbook_access.php` untuk fix missing access

### Problem: "Template not found"
**Solution:**
- Verify template_id dengan GET `/api/templates`
- Pastikan menggunakan UUID format yang benar

### Problem: "Missing required fields"
**Solution:**
- Check template fields dengan GET `/api/templates/{id}`
- Pastikan semua field template ada di request body

### Problem: Image upload gagal
**Solution:**
- Pastikan image dalam format base64
- Check ukuran file tidak terlalu besar
- Verify format: `data:image/jpeg;base64,{base64_string}`

---

## 📝 **Notes**

1. **Template ID**: Harus berupa valid UUID
2. **Data Field**: Harus mengandung SEMUA fields yang ada di template
3. **Image Format**: Base64 string dengan prefix `data:image/jpeg;base64,`
4. **Authentication**: Bearer token required untuk semua request
5. **Content-Type**: application/json untuk JSON requests

**Base URL Development:** `http://localhost/loggenerator_api/api`
**Base URL Production:** `https://yourdomain.com/api`