# 🧪 Testing Guide - Admin Auto-Verify

Quick testing guide untuk memvalidasi auto-verify implementation.

---

## ✅ Pre-Test Checklist

- [ ] Clear config cache: `php artisan config:clear`
- [ ] Clear application cache: `php artisan cache:clear`
- [ ] Database seeded: `php artisan db:seed`
- [ ] API server running: `php artisan serve`

---

## 🔧 Test Cases

### Test 1: Super Admin Creates User ✅

**Expected:** User langsung ter-verifikasi, bisa login tanpa email verification.

```bash
# Step 1: Login as Super Admin
curl -X POST http://localhost/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@example.com",
    "password": "password"
  }'

# Response: Copy the "token" value
# Example: "token": "1|abc123..."

# Step 2: Create User
curl -X POST http://localhost/api/users/create \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 1",
    "email": "testuser1@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "User"
  }'

# Expected Response:
# {
#   "success": true,
#   "message": "User created successfully",
#   "data": {
#     "user": {
#       "email_verified_at": "2025-12-15T10:00:00.000000Z",  ✅ Should have timestamp
#       ...
#     }
#   }
# }

# Step 3: Verify user can login
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "testuser1@example.com",
    "password": "password123"
  }'

# Expected: Success login ✅
```

**Result:** 
- [ ] User created successfully
- [ ] `email_verified_at` has timestamp
- [ ] User can login immediately
- [ ] No email sent

---

### Test 2: Admin Creates User ✅

**Expected:** User langsung ter-verifikasi.

```bash
# Step 1: Login as Admin
curl -X POST http://localhost/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# Step 2: Create User
curl -X POST http://localhost/api/users/create \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 2",
    "email": "testuser2@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "User"
  }'

# Expected Response:
# {
#   "data": {
#     "user": {
#       "email_verified_at": "2025-12-15T10:00:00.000000Z"  ✅
#     }
#   }
# }
```

**Result:**
- [ ] User created successfully
- [ ] `email_verified_at` has timestamp
- [ ] User can login

---

### Test 3: Institution Admin Adds Member ✅

**Expected:** Member langsung ter-verifikasi.

```bash
# Step 1: Login as Institution Admin
# (First create an Institution Admin if not exists)

curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "institutionadmin@example.com",
    "password": "password"
  }'

# Step 2: Add Institution Member
curl -X POST http://localhost/api/institution/add-member \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Institution Member",
    "email": "member@example.com",
    "password": "password123",
    "phone_number": "081234567890",
    "role": "User"
  }'

# Expected Response:
# {
#   "success": true,
#   "data": {
#     "user": {
#       "email_verified_at": "2025-12-15T10:00:00.000000Z"  ✅
#     }
#   }
# }
```

**Result:**
- [ ] Member added successfully
- [ ] `email_verified_at` has timestamp
- [ ] Member belongs to institution
- [ ] Member can login

---

### Test 4: Self Registration (Should Still Send Email) 📧

**Expected:** Email verification TETAP dikirim (unchanged behavior).

```bash
# Register new user
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Self Registered User",
    "email": "selfregister@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Expected Response:
# {
#   "success": true,
#   "message": "User registered successfully",
#   "data": {
#     "user": {
#       "email_verified_at": null,  ❌ Should be null
#       ...
#     }
#   },
#   "verification_sent": true  ✅ Email sent
# }

# Try to login (should work but might need verification depending on your middleware)
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "selfregister@example.com",
    "password": "password123"
  }'
```

**Result:**
- [ ] User registered successfully
- [ ] `email_verified_at` is NULL
- [ ] `verification_sent: true`
- [ ] Email received (check inbox)
- [ ] Verification link works

---

### Test 5: Google OAuth (Should Still Auto-Verify) ✅

**Expected:** Google users tetap auto-verified (unchanged).

```bash
# Login via Google OAuth
curl -X POST http://localhost/api/auth/google \
  -H "Content-Type: application/json" \
  -d '{
    "id_token": "VALID_GOOGLE_ID_TOKEN"
  }'

# Expected Response:
# {
#   "data": {
#     "user": {
#       "email_verified_at": "2025-12-15T10:00:00.000000Z",  ✅
#       "google_verified": true
#     }
#   }
# }
```

**Result:**
- [ ] Login successful
- [ ] `email_verified_at` has timestamp
- [ ] No email sent

---

### Test 6: Database Seeder Users ✅

**Expected:** Seeded users sudah ter-verifikasi.

```bash
# Run seeders
php artisan migrate:fresh --seed

# Check via Tinker
php artisan tinker

# Query seeded users
>>> use App\Models\User;
>>> User::whereEmail('superadmin@example.com')->first()->email_verified_at
// Should return: "2025-12-15 10:00:00"  ✅

>>> User::whereEmail('admin@example.com')->first()->email_verified_at
// Should return: "2025-12-15 10:00:00"  ✅

>>> User::whereEmail('test1@example.com')->first()->email_verified_at
// Should return: "2025-12-15 10:00:00"  ✅

# Count all verified users
>>> User::whereNotNull('email_verified_at')->count()
// Should return: number of seeded users  ✅
```

**Result:**
- [ ] All seeded users have `email_verified_at`
- [ ] Super Admin verified
- [ ] Admin verified
- [ ] Test users verified

---

## 🗄️ Database Check

### Check via SQL

```sql
-- Check all users with verification status
SELECT 
    id,
    name,
    email,
    email_verified_at,
    CASE 
        WHEN email_verified_at IS NOT NULL THEN '✅ Verified'
        ELSE '❌ Not Verified'
    END as status
FROM users
ORDER BY created_at DESC;

-- Count verified vs unverified
SELECT 
    COUNT(*) as total_users,
    COUNT(email_verified_at) as verified_users,
    COUNT(*) - COUNT(email_verified_at) as unverified_users
FROM users;
```

---

## 📊 Test Results Matrix

| Test Case | Status | email_verified_at | Can Login | Email Sent |
|-----------|--------|-------------------|-----------|------------|
| Super Admin Create | ⏳ | ⏳ | ⏳ | ⏳ |
| Admin Create | ⏳ | ⏳ | ⏳ | ⏳ |
| Institution Admin | ⏳ | ⏳ | ⏳ | ⏳ |
| Self Registration | ⏳ | ⏳ | ⏳ | ⏳ |
| Google OAuth | ⏳ | ⏳ | ⏳ | ⏳ |
| Seeder Users | ⏳ | ⏳ | ⏳ | ⏳ |

**Legend:**
- ✅ = Passed
- ❌ = Failed
- ⏳ = Not tested yet

---

## 🐛 Troubleshooting

### Issue: email_verified_at is NULL

**Solution:**
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Restart server
php artisan serve
```

### Issue: Changes not reflected

**Solution:**
```bash
# Re-run migrations and seeds
php artisan migrate:fresh --seed
```

### Issue: Self-registration not sending email

**Solution:**
```env
# Check .env
MAIL_MAILER=smtp  # NOT 'log'
```

---

## ✅ Final Validation Checklist

### Code Changes
- [ ] UserManagementController::createUser() has `email_verified_at => now()`
- [ ] UserManagementController::addInstitutionMember() has `email_verified_at => now()`
- [ ] TestUserSeeder has `email_verified_at => now()`
- [ ] UserSeeder has `email_verified_at => now()` (already existed)

### Behavior
- [ ] Admin-created users auto-verified ✅
- [ ] Institution Admin-created users auto-verified ✅
- [ ] Self-registered users still send email 📧
- [ ] Google OAuth users still auto-verified ✅
- [ ] Seeder users auto-verified ✅

### Documentation
- [ ] EMAIL_VERIFICATION.md updated
- [ ] EMAIL_VERIFICATION_QUICKSTART.md updated
- [ ] ADMIN_AUTO_VERIFY_UPDATE.md created
- [ ] README.md updated with links

### Testing
- [ ] All test cases passed
- [ ] No regressions
- [ ] Backward compatible

---

**Testing Date:** _______________  
**Tested By:** _______________  
**Status:** ⏳ Pending / ✅ Passed / ❌ Failed  
**Notes:** _______________________________________________
