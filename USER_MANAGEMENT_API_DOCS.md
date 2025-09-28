# User Management API Documentation

API untuk membuat dan mengelola user dengan role Admin, Manager, dan User. **Hanya dapat diakses oleh Super Admin**.

## Base URL
```
http://your-domain.com/api
```

## Authentication
Semua endpoint memerlukan:
- Header: `Authorization: Bearer {token}`
- Token didapat dari login sebagai Super Admin

## Endpoints

### 1. Create User with Role
**POST** `/admin/users`

Membuat user baru dengan role tertentu.

#### Headers
```
Authorization: Bearer {super_admin_token}
Content-Type: application/json
Accept: application/json
```

#### Request Body
```json
{
    "name": "John Doe",
    "email": "john@example.com", 
    "password": "password123",
    "phone_number": "+1234567890",
    "role": "Admin"
}
```

#### Request Body Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Nama user (max 100 karakter) |
| email | string | Yes | Email user (max 150 karakter, harus unique) |
| password | string | Yes | Password user (min 8 karakter) |
| phone_number | string | No | Nomor telepon (max 20 karakter) |
| role | string | Yes | Role user: "Admin", "Manager", atau "User" |

#### Success Response (201)
```json
{
    "success": true,
    "message": "User created successfully",
    "data": {
        "user": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "John Doe",
            "email": "john@example.com",
            "phone_number": "+1234567890",
            "status": "active",
            "role": "Admin",
            "created_at": "2025-09-27T10:30:00.000000Z"
        }
    }
}
```

#### Error Responses

**403 Forbidden** - Bukan Super Admin
```json
{
    "success": false,
    "message": "Unauthorized. Only Super Admin can create users with roles."
}
```

**422 Validation Error**
```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {
        "email": ["Email is already registered"],
        "role": ["Role must be one of: Admin, Manager, User"]
    }
}
```

---

### 2. Get Users List
**GET** `/admin/users`

Mendapatkan daftar semua user dengan role mereka.

#### Headers
```
Authorization: Bearer {super_admin_token}
Accept: application/json
```

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| per_page | integer | No | Jumlah user per halaman (default: 15) |

#### Success Response (200)
```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": {
        "users": [
            {
                "id": "550e8400-e29b-41d4-a716-446655440000",
                "name": "John Doe",
                "email": "john@example.com",
                "phone_number": "+1234567890",
                "status": "active",
                "roles": ["Admin"],
                "created_at": "2025-09-27T10:30:00.000000Z",
                "last_login": "2025-09-27T11:00:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 50,
            "last_page": 4
        }
    }
}
```

---

### 3. Update User Role
**PUT** `/admin/users/{userId}/role`

Mengubah role user.

#### Headers
```
Authorization: Bearer {super_admin_token}
Content-Type: application/json
Accept: application/json
```

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| userId | string | Yes | UUID user yang akan diubah |

#### Request Body
```json
{
    "role": "Manager"
}
```

#### Request Body Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| role | string | Yes | Role baru: "Admin", "Manager", atau "User" |

#### Success Response (200)
```json
{
    "success": true,
    "message": "User role updated successfully",
    "data": {
        "user": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "John Doe",
            "email": "john@example.com",
            "old_roles": ["User"],
            "new_role": "Manager"
        }
    }
}
```

#### Error Responses

**422 Validation Error** - Tidak bisa mengubah role sendiri
```json
{
    "success": false,
    "message": "You cannot change your own role."
}
```

---

## Security Features

1. **Role-based Access Control**: Hanya Super Admin yang dapat mengakses endpoint ini
2. **Input Validation**: Validasi ketat untuk semua input
3. **Audit Logging**: Semua operasi dicatat dalam audit log
4. **Password Hashing**: Password otomatis di-hash menggunakan Laravel Hash
5. **Email Uniqueness**: Sistem memastikan email tidak duplikat
6. **Self-protection**: Super Admin tidak bisa mengubah role diri sendiri

## Audit Logging

Setiap operasi akan mencatat:
- `CREATE_USER`: Ketika Super Admin membuat user baru
- `USER_CREATED`: Ketika user baru berhasil dibuat
- `UPDATE_USER_ROLE`: Ketika Super Admin mengubah role user

## Error Handling

- **401 Unauthorized**: Token tidak valid atau expired
- **403 Forbidden**: User bukan Super Admin
- **422 Validation Error**: Input tidak valid
- **500 Internal Server Error**: Error sistem

## Example Usage dengan CURL

### Login sebagai Super Admin
```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@example.com",
    "password": "your_password",
    "device_name": "API Client"
  }'
```

### Buat User Admin
```bash
curl -X POST http://your-domain.com/api/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Admin",
    "email": "newadmin@example.com",
    "password": "password123",
    "phone_number": "+1234567890",
    "role": "Admin"
  }'
```

### Get Users List
```bash
curl -X GET http://your-domain.com/api/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### Update User Role
```bash
curl -X PUT http://your-domain.com/api/admin/users/USER_ID_HERE/role \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "role": "Manager"
  }'
```

## Testing

Untuk menjalankan test otomatis:

1. Pastikan server web berjalan
2. Pastikan ada user dengan role Super Admin
3. Update kredensial di `test_user_management_api.php`
4. Jalankan: `php test_user_management_api.php`

## Integration dengan Spatie Permission

API ini terintegrasi penuh dengan sistem Spatie Laravel Permission yang sudah ada:

- **Roles**: Admin, Manager, User (sudah ada dalam sistem)
- **Guard**: menggunakan guard `web`
- **Model Relations**: User model sudah memiliki `HasRoles` trait
- **Role Assignment**: menggunakan `assignRole()` dan `syncRoles()` method
- **Role Checking**: menggunakan `hasRole()` method

## Database Impact

Operasi ini akan mempengaruhi tabel:
- `users`: User baru ditambahkan
- `model_has_roles`: Relasi user-role ditambahkan
- `audit_logs`: Log operasi ditambahkan

## Production Considerations

1. **Rate Limiting**: Pertimbangkan untuk menambahkan rate limiting
2. **Email Notifications**: Bisa ditambahkan notifikasi email untuk user baru
3. **Password Policy**: Implementasikan kebijakan password yang lebih ketat
4. **Bulk Operations**: Bisa ditambahkan endpoint untuk bulk user creation
5. **User Deactivation**: Bisa ditambahkan fitur untuk menonaktifkan user