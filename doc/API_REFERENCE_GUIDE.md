# API REFERENCE GUIDE
**LogGenerator API - Complete API Documentation**

---

## 📋 TABLE OF CONTENTS

1. [API Overview](#api-overview)
2. [Authentication Endpoints](#authentication-endpoints)
3. [Template Management API](#template-management-api)
4. [User Access Management API](#user-access-management-api)
5. [Logbook Data API](#logbook-data-api)
6. [Role & Permission API](#role--permission-api)
7. [File Management API](#file-management-api)
8. [Notification API](#notification-api)
9. [Error Handling](#error-handling)
10. [API Testing Guide](#api-testing-guide)

---

## 🌐 API OVERVIEW

### Base Configuration
- **Base URL**: `http://your-domain.com/api`
- **Authentication**: Bearer Token (Sanctum)
- **Content-Type**: `application/json`
- **Response Format**: JSON

### Standard Response Structure
```json
{
    "success": true|false,
    "message": "Human readable message",
    "data": {}, // Response data
    "errors": {}, // Validation errors (if any)
    "pagination": {} // Pagination info (for paginated responses)
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## 🔐 AUTHENTICATION ENDPOINTS

### 1. Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "token": "1|AbCdEf123...",
    "user": {
        "id": "uuid-user-1",
        "name": "John Doe",
        "email": "user@example.com",
        "roles": ["User"],
        "permissions": ["create_templates", "edit_templates"]
    }
}
```

### 2. Register
```http
POST /api/register
Content-Type: application/json

{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

### 3. Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

### 4. Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

---

## 📝 TEMPLATE MANAGEMENT API

### 1. Get All Templates
```http
GET /api/templates
Authorization: Bearer {token}

Query Parameters:
- per_page (optional): Items per page (default: 15)
- page (optional): Page number
```

### 2. Get User Templates
```http
GET /api/templates/user
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "User templates retrieved successfully",
    "data": [
        {
            "id": "uuid-template-1",
            "name": "Daily Report",
            "description": "Template untuk laporan harian",
            "created_at": "2025-09-22T10:00:00.000000Z",
            "updated_at": "2025-09-22T10:00:00.000000Z",
            "role_name": "owner",
            "role_description": "Full access to template",
            "access_granted_at": "2025-09-20T08:00:00.000000Z",
            "fields": [
                {
                    "id": "uuid-field-1",
                    "name": "Activity",
                    "data_type": "teks"
                }
            ]
        }
    ]
}
```

### 3. Get Template by ID
```http
GET /api/templates/{id}
Authorization: Bearer {token}
```

### 4. Create Template
```http
POST /api/templates
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "New Template",
    "description": "Template description",
    "fields": [
        {
            "name": "Field Name",
            "data_type": "teks|angka|gambar|tanggal|jam"
        }
    ]
}
```

### 5. Update Template
```http
PUT /api/templates/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Template",
    "description": "Updated description"
}
```

### 6. Delete Template
```http
DELETE /api/templates/{id}
Authorization: Bearer {token}
```

### 7. Get Template Fields
```http
GET /api/templates/{templateId}/fields
Authorization: Bearer {token}
```

### 8. Get User Template Permissions
```http
GET /api/templates/user/permissions
Authorization: Bearer {token}
```

---

## 👥 USER ACCESS MANAGEMENT API

### 1. Get User Access List
```http
GET /api/user-access
Authorization: Bearer {token}

Query Parameters:
- template_id (optional): Filter by template UUID
- user_id (optional): Filter by user UUID
- role_id (optional): Filter by role ID  
- per_page (optional): Items per page (default: 15)
- page (optional): Page number
```

**Response:**
```json
{
    "success": true,
    "message": "User logbook access retrieved successfully",
    "data": [
        {
            "id": "uuid-access-1",
            "user_id": "uuid-user-1",
            "logbook_template_id": "uuid-template-1",
            "logbook_role_id": 1,
            "granted_by": "uuid-owner",
            "created_at": "2025-09-22T10:00:00.000000Z",
            "user": {
                "id": "uuid-user-1",
                "name": "John Doe",
                "email": "john@example.com"
            },
            "logbook_template": {
                "id": "uuid-template-1",
                "name": "Daily Report",
                "description": "Template description"
            },
            "logbook_role": {
                "id": 1,
                "name": "owner",
                "description": "Full access to template"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15, 
        "total": 25,
        "last_page": 2
    }
}
```

### 2. Create User Access
```http
POST /api/user-access
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": "uuid-user-1",
    "template_id": "uuid-template-1", 
    "logbook_role_id": 2,
    "user_email": "user@example.com" // Alternative to user_id
}
```

### 3. Bulk Create User Access
```http
POST /api/user-access/bulk
Authorization: Bearer {token}
Content-Type: application/json

{
    "template_id": "uuid-template-1",
    "users": [
        {
            "user_id": "uuid-user-1",
            "logbook_role_id": 2
        },
        {
            "user_email": "user2@example.com",
            "logbook_role_id": 3
        }
    ]
}
```

### 4. Update User Access
```http
PUT /api/user-access/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "logbook_role_id": 3
}
```

### 5. Delete User Access
```http
DELETE /api/user-access/{id}
Authorization: Bearer {token}
```

### 6. Get User Access by Template
```http
GET /api/user-access/template/{templateId}
Authorization: Bearer {token}

Query Parameters:
- per_page (optional): Items per page
- page (optional): Page number
```

### 7. Get Template Access Statistics
```http
GET /api/user-access/template/{templateId}/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Template access statistics retrieved successfully",
    "data": {
        "template_id": "uuid-template-1",
        "template_name": "Daily Report",
        "total_users": 15,
        "role_breakdown": {
            "owner": 1,
            "supervisor": 3,
            "editor": 8,
            "viewer": 3
        },
        "recent_access": [
            {
                "user_name": "John Doe",
                "role_name": "editor", 
                "granted_at": "2025-09-22T10:00:00.000000Z"
            }
        ]
    }
}
```

---

## 📊 LOGBOOK DATA API

### 1. Get Logbook Entries
```http
GET /api/logbook-entries
Authorization: Bearer {token}

Query Parameters:
- template_id (optional): Filter by template
- writer_id (optional): Filter by writer
- per_page (optional): Items per page
- page (optional): Page number
```

### 2. Get Entries by Template
```http
GET /api/logbook-entries/template/{templateId}
Authorization: Bearer {token}
```

### 3. Create Logbook Entry
```http
POST /api/logbook-entries
Authorization: Bearer {token}
Content-Type: application/json

{
    "template_id": "uuid-template-1",
    "data": {
        "field1": "value1",
        "field2": "value2",
        "date_field": "2025-09-22",
        "time_field": "14:30:00"
    }
}
```

### 4. Update Logbook Entry
```http
PUT /api/logbook-entries/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "data": {
        "field1": "updated_value"
    }
}
```

### 5. Delete Logbook Entry
```http
DELETE /api/logbook-entries/{id}
Authorization: Bearer {token}
```

### 6. Get Template Summary
```http
GET /api/logbook-entries/template/{templateId}/summary
Authorization: Bearer {token}
```

---

## 🔒 ROLE & PERMISSION API

### 1. Get All Roles
```http
GET /api/roles
Authorization: Bearer {token}
```

### 2. Get Role Details
```http
GET /api/roles/{id}
Authorization: Bearer {token}
```

### 3. Get Role Users
```http
GET /api/roles/{id}/users
Authorization: Bearer {token}
```

### 4. Get All Permissions
```http
GET /api/permissions
Authorization: Bearer {token}
```

### 5. Assign Permissions to Role
```http
POST /api/roles/assign-permissions
Authorization: Bearer {token}
Content-Type: application/json

{
    "role_id": "role-uuid",
    "permissions": ["permission1", "permission2"]
}
```

### 6. Revoke Permissions from Role
```http
POST /api/roles/revoke-permissions
Authorization: Bearer {token}
Content-Type: application/json

{
    "role_id": "role-uuid", 
    "permissions": ["permission1", "permission2"]
}
```

---

## 📁 FILE MANAGEMENT API

### 1. Upload Image
```http
POST /api/upload/image
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "template_id": "uuid-template-1",
    "field_name": "photo_field",
    "image": [file]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Image uploaded successfully",
    "data": {
        "filename": "generated_filename.jpg",
        "original_name": "original_file.jpg",
        "path": "/storage/logbook_images/generated_filename.jpg",
        "url": "http://domain.com/api/images/logbook/generated_filename.jpg"
    }
}
```

### 2. Get Logbook Image
```http
GET /api/images/logbook/{filename}
```

---

## 🔔 NOTIFICATION API

### 1. Create Notification
```http
POST /api/notifications
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Notification Title",
    "message": "Notification message",
    "recipient_id": "uuid-user-1"
}
```

### 2. Send Notification to Role
```http
POST /api/notifications/send-to-role
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Role Notification",
    "message": "Message for role",
    "role_name": "Admin"
}
```

### 3. Send Notification to Multiple Users
```http
POST /api/notifications/send-to-users
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Bulk Notification",
    "message": "Message for users",
    "user_ids": ["uuid-1", "uuid-2", "uuid-3"]
}
```

---

## 🚨 ERROR HANDLING

### Validation Errors (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "template_id": ["The template id must be a valid UUID."]
    }
}
```

### Authorization Errors (403)
```json
{
    "success": false,
    "message": "Unauthorized. Only template owner, Super Admin, or Admin can perform this action."
}
```

### Not Found Errors (404)
```json
{
    "success": false,
    "message": "Template not found"
}
```

### Server Errors (500)
```json
{
    "success": false,
    "message": "Internal server error occurred"
}
```

---

## 🧪 API TESTING GUIDE

### Using cURL

#### Authentication Test
```bash
# Login
curl -X POST http://domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get user templates
curl -X GET http://domain.com/api/templates/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Template Management Test
```bash
# Create template
curl -X POST http://domain.com/api/templates \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Template",
    "description": "Test description",
    "fields": [{"name": "Activity", "data_type": "teks"}]
  }'
```

#### User Access Test
```bash
# Grant template access
curl -X POST http://domain.com/api/user-access \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "template_id": "uuid-template-1",
    "user_email": "user@example.com",
    "logbook_role_id": 2
  }'
```

### Using Postman

#### Environment Variables
```
base_url: http://your-domain.com/api
token: YOUR_BEARER_TOKEN
```

#### Collection Structure
```
LogGenerator API/
├── Authentication/
│   ├── Login
│   ├── Register  
│   └── Logout
├── Templates/
│   ├── Get All Templates
│   ├── Get User Templates
│   ├── Create Template
│   └── Delete Template
├── User Access/
│   ├── Get Access List
│   ├── Create Access
│   └── Bulk Create Access
└── Logbook Data/
    ├── Get Entries
    ├── Create Entry
    └── Update Entry
```

### Response Time Benchmarks
- Authentication: < 200ms
- Template Operations: < 300ms
- User Access Operations: < 250ms
- File Upload: < 1000ms (depends on file size)

---

## 📊 API RATE LIMITING

### Rate Limits
- **Authentication endpoints**: 5 requests/minute
- **CRUD operations**: 60 requests/minute
- **File uploads**: 10 requests/minute
- **Bulk operations**: 30 requests/minute

### Rate Limit Headers
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1632150000
```

---

**✅ API Reference complete with all endpoints, authentication, and comprehensive testing guide.**