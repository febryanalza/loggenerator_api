# Email Verification System Documentation

Dokumentasi lengkap implementasi email verification menggunakan Brevo (Sendinblue) SMTP.

## 📋 Table of Contents

1. [Overview](#overview)
2. [Environment Configuration](#environment-configuration)
3. [Required Variables Checklist](#required-variables-checklist)
4. [API Endpoints](#api-endpoints)
5. [Implementation Flow](#implementation-flow)
6. [Testing Guide](#testing-guide)
7. [Troubleshooting](#troubleshooting)
8. [Production Deployment](#production-deployment)

---

## Overview

Sistem email verification memungkinkan:
- ✅ User yang register via email/password menerima email verifikasi
- ✅ Google OAuth users otomatis verified (skip email verification)
- ✅ Resend verification email jika tidak diterima
- ✅ Check verification status
- ✅ Secure signed URLs dengan expiration (60 menit)

### Fitur Keamanan

- **Signed URLs**: Link verifikasi menggunakan Laravel signed routes untuk mencegah tampering
- **Time-Limited**: Link expired setelah 60 menit
- **Hash Verification**: Double-check dengan SHA1 hash dari email address
- **Audit Logging**: Semua aktivitas verifikasi dicatat

---

## Environment Configuration

### 1. Update .env File

Buka file `.env` dan update/tambahkan variabel berikut:

```env
# ===================================
# APPLICATION URL (CRITICAL!)
# ===================================
# Development
APP_URL=http://localhost

# Production
# APP_URL=https://yourdomain.com

# ===================================
# MAIL CONFIGURATION (BREVO)
# ===================================
# MUST be 'smtp' for sending emails
MAIL_MAILER=smtp

# Brevo SMTP Configuration
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_username@smtp-brevo.com
MAIL_PASSWORD=your_brevo_api_key
MAIL_ENCRYPTION=tls

# Sender Information (must be verified in Brevo)
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Dapatkan Brevo SMTP Credentials

#### Step 1: Create Brevo Account
1. Kunjungi https://www.brevo.com
2. Sign up untuk akun gratis
3. Verify email Anda

#### Step 2: Verify Sender Email
1. Login ke Brevo Dashboard
2. Go to **Senders** → **Add a sender**
3. Masukkan email address yang akan digunakan (misalnya: noreply@yourdomain.com)
4. Verify email tersebut (check inbox untuk confirmation link)

#### Step 3: Get SMTP Credentials
1. Go to **SMTP & API** → **SMTP**
2. Copy:
   - **SMTP Server**: `smtp-relay.brevo.com`
   - **Port**: `587`
   - **Login**: Your SMTP username (format: xxxxxxxx@smtp-brevo.com)
   - **Master Password**: Your SMTP API key/password

#### Step 4: Update .env
```env
MAIL_USERNAME=9dee81001@smtp-brevo.com
MAIL_PASSWORD=xkS3bMpL9YvH2Wc8
MAIL_FROM_ADDRESS="noreply@loggenerator.my.id"
```

---

## Required Variables Checklist

### ✅ Critical Variables (Must Have)

| Variable | Value | Purpose | Example |
|----------|-------|---------|---------|
| `APP_URL` | Your domain URL | Generate verification links | `https://api.yourdomain.com` |
| `MAIL_MAILER` | `smtp` | Enable email sending | `smtp` |
| `MAIL_HOST` | Brevo SMTP server | SMTP host | `smtp-relay.brevo.com` |
| `MAIL_PORT` | SMTP port | Connection port | `587` |
| `MAIL_USERNAME` | Your Brevo login | SMTP authentication | `user@smtp-brevo.com` |
| `MAIL_PASSWORD` | Your Brevo password | SMTP authentication | `your_api_key` |
| `MAIL_ENCRYPTION` | Encryption method | Security | `tls` |
| `MAIL_FROM_ADDRESS` | Sender email | Email from field | `noreply@yourdomain.com` |
| `MAIL_FROM_NAME` | Sender name | Display name | `LogGenerator` |

### ⚠️ Common Mistakes

1. ❌ `MAIL_MAILER=log` → ✅ `MAIL_MAILER=smtp`
2. ❌ `noreplay@domain.com` → ✅ `noreply@domain.com` (typo!)
3. ❌ `APP_URL=http://localhost` in production → ✅ `APP_URL=https://yourdomain.com`
4. ❌ Unverified sender email → ✅ Verify email in Brevo dashboard first

---

## API Endpoints

### 1. Register (with Email Verification)

**POST** `/api/register`

Mendaftar user baru dan mengirim email verifikasi.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone_number": "+6281234567890",
  "device_name": "Android Device"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Registration successful. Please check your email to verify your account.",
  "data": {
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "john@example.com",
      "status": "active",
      "email_verified": false
    },
    "token": "1|bearer-token-here",
    "verification_sent": true
  }
}
```

**Email Sent:**
- Subject: "Verify Your Email Address - LogGenerator"
- Contains: Verification button/link
- Expires: 60 minutes

---

### 2. Verify Email

**GET** `/api/email/verify/{id}/{hash}`

Verify email address menggunakan link dari email.

**URL Parameters:**
- `id`: User ID (UUID)
- `hash`: SHA1 hash dari email address
- `signature`: Signed URL signature (auto-generated)
- `expires`: Expiration timestamp (auto-generated)

**Example URL:**
```
https://api.yourdomain.com/api/email/verify/123e4567-e89b-12d3-a456-426614174000/a94a8fe5ccb19ba61c4c0873d391e987982fbbd3?expires=1734567890&signature=xxx
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Email verified successfully",
  "data": {
    "verified_at": "2025-12-15T10:30:00.000000Z"
  }
}
```

**Response (Already Verified):**
```json
{
  "success": true,
  "message": "Email already verified",
  "data": {
    "verified_at": "2025-12-14T10:30:00.000000Z"
  }
}
```

**Response (Invalid Link):**
```json
{
  "success": false,
  "message": "Invalid verification link"
}
```

---

### 3. Resend Verification Email

**POST** `/api/email/verification-notification`

Mengirim ulang email verifikasi.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Verification email has been resent"
}
```

**Response (Already Verified):**
```json
{
  "success": false,
  "message": "Email already verified"
}
```

**Response (Google User):**
```json
{
  "success": false,
  "message": "Google authenticated users do not need email verification"
}
```

---

### 4. Check Verification Status

**GET** `/api/email/verification-status`

Check apakah email sudah verified atau belum.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "email": "john@example.com",
    "email_verified": true,
    "email_verified_at": "2025-12-15T10:30:00.000000Z",
    "auth_provider": "email",
    "google_verified": false
  }
}
```

---

### 5. Google Login (Auto-Verified)

**POST** `/api/auth/google`

Google users otomatis verified, tidak perlu email verification.

**Request Body:**
```json
{
  "id_token": "google_id_token_here",
  "device_name": "Android Device"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Google authentication successful",
  "data": {
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "john@gmail.com",
      "avatar_url": "https://...",
      "status": "active",
      "auth_provider": "google",
      "email_verified": true,
      "google_verified": true
    },
    "token": "1|bearer-token-here"
  }
}
```

---

## Implementation Flow

### User Registration Flow

```
┌─────────────────┐
│ User Registers  │
│ (Email/Pass)    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Create User    │
│  in Database    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Send Verification│
│      Email      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ User Receives   │
│     Email       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ User Clicks     │
│  Verify Link    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Verify Hash    │
│  & Signature    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Set email_      │
│ verified_at     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Email Verified! │
│  ✅ Success     │
└─────────────────┘
```

### Google OAuth Flow (Skip Verification)

```
┌─────────────────┐
│ User Login via  │
│     Google      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Verify Google   │
│    ID Token     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Create/Update   │
│      User       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Auto-set:       │
│ email_verified  │
│ google_verified │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ No Email Needed │
│  ✅ Success     │
└─────────────────┘
```

---

## Testing Guide

### 1. Test Email Configuration

```bash
# Test via Laravel Tinker
php artisan tinker

# Send test email
>>> use Illuminate\Support\Facades\Mail;
>>> Mail::raw('This is a test email', function($message) {
...     $message->to('your-email@example.com')
...             ->subject('Test Email from LogGenerator');
... });

# Check result
# Should return: null (success)
# Check your inbox!
```

### 2. Test Registration & Verification

#### Step 1: Register New User
```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Expected Response:**
- `success: true`
- `verification_sent: true`
- `email_verified: false`

#### Step 2: Check Email
- Open inbox test@example.com
- Look for email from noreply@yourdomain.com
- Subject: "Verify Your Email Address"

#### Step 3: Click Verification Link
- Click "Verify Email Address" button
- Should redirect to API endpoint
- Response: `"message": "Email verified successfully"`

#### Step 4: Verify Status
```bash
curl -X GET http://localhost/api/email/verification-status \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "email_verified": true,
  "email_verified_at": "2025-12-15T..."
}
```

### 3. Test Resend Verification

```bash
# Register new user (unverified)
# Then resend:
curl -X POST http://localhost/api/email/verification-notification \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:**
- New email sent
- Response: `"message": "Verification email has been resent"`

### 4. Test Google Login (Skip Verification)

```bash
curl -X POST http://localhost/api/auth/google \
  -H "Content-Type: application/json" \
  -d '{
    "id_token": "GOOGLE_ID_TOKEN_HERE"
  }'
```

**Expected Response:**
- `email_verified: true` (auto-verified)
- `google_verified: true`
- No email sent

---

## Troubleshooting

### Problem 1: Email Tidak Terkirim

**Symptoms:**
- Registration berhasil tapi tidak ada email masuk
- Log: "Mail sent successfully" tapi inbox kosong

**Solutions:**

1. **Check MAIL_MAILER:**
   ```env
   # ❌ Wrong
   MAIL_MAILER=log
   
   # ✅ Correct
   MAIL_MAILER=smtp
   ```

2. **Check Spam Folder:**
   - Brevo emails kadang masuk spam
   - Check junk/spam folder

3. **Verify Sender Email:**
   - Login ke Brevo Dashboard
   - Go to Senders
   - Pastikan email verified (green checkmark)

4. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Test SMTP Connection:**
   ```bash
   php artisan tinker
   >>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
   ```

---

### Problem 2: Invalid Verification Link

**Symptoms:**
- Click link → "Invalid verification link"
- Error 403 atau 400

**Solutions:**

1. **Check APP_URL:**
   ```env
   # Must match your actual domain
   APP_URL=https://yourdomain.com
   ```

2. **Link Expired:**
   - Links expire after 60 minutes
   - Resend verification email

3. **Cache Issue:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

---

### Problem 3: SMTP Authentication Failed

**Symptoms:**
- Error: "Expected response code 250 but got code 535"
- Log: "Authentication failed"

**Solutions:**

1. **Check Credentials:**
   ```env
   # Verify these are correct from Brevo dashboard
   MAIL_USERNAME=your_username@smtp-brevo.com
   MAIL_PASSWORD=your_actual_password
   ```

2. **Check Brevo Account:**
   - Login to Brevo
   - Verify account is active
   - Check SMTP is enabled

3. **Generate New Password:**
   - Go to Brevo → SMTP & API
   - Generate new SMTP password
   - Update .env

---

### Problem 4: Connection Timeout

**Symptoms:**
- Error: "Connection timeout"
- Email sending takes long time

**Solutions:**

1. **Check Firewall:**
   ```bash
   # Port 587 must be open
   telnet smtp-relay.brevo.com 587
   ```

2. **Try Alternative Port:**
   ```env
   # Try SSL port instead
   MAIL_PORT=465
   MAIL_ENCRYPTION=ssl
   ```

3. **Check Server Outbound:**
   - Some servers block outbound SMTP
   - Contact hosting provider

---

### Problem 5: Email Masuk Spam

**Symptoms:**
- Email terkirim tapi masuk spam folder
- Gmail/Outlook mark as spam

**Solutions:**

1. **Setup SPF Record:**
   ```
   v=spf1 include:spf.brevo.com ~all
   ```

2. **Setup DKIM:**
   - Go to Brevo → Senders → Authentication
   - Copy DKIM records
   - Add to DNS

3. **Use Verified Domain:**
   ```env
   # Better:
   MAIL_FROM_ADDRESS="noreply@yourdomain.com"
   
   # Than:
   MAIL_FROM_ADDRESS="noreply@gmail.com"
   ```

---

## Production Deployment

### Pre-Deployment Checklist

#### ✅ Environment Configuration

```env
# Production Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Mail Settings (Production)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_production_username@smtp-brevo.com
MAIL_PASSWORD=your_production_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="LogGenerator"
```

#### ✅ DNS Configuration

1. **SPF Record:**
   ```
   Type: TXT
   Host: @
   Value: v=spf1 include:spf.brevo.com ~all
   ```

2. **DKIM Records:**
   - Get from Brevo Dashboard
   - Add as TXT records in DNS

3. **DMARC Record (Optional but Recommended):**
   ```
   Type: TXT
   Host: _dmarc
   Value: v=DMARC1; p=none; rua=mailto:admin@yourdomain.com
   ```

#### ✅ SSL Certificate

- Email verification links MUST use HTTPS
- Install SSL certificate (Let's Encrypt)
- Ensure APP_URL uses https://

#### ✅ Email Template Customization

Edit email template (optional):
```bash
php artisan vendor:publish --tag=laravel-notifications
```

Then customize:
```
resources/views/vendor/notifications/email.blade.php
```

#### ✅ Queue Configuration (Recommended)

For better performance, use queues:

```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

Update notification:
```php
// Already implements ShouldQueue
class VerifyEmailNotification extends Notification implements ShouldQueue
```

Run queue worker:
```bash
php artisan queue:work --daemon
```

#### ✅ Rate Limiting

Add to `app/Http/Kernel.php`:
```php
protected $middlewareGroups = [
    'api' => [
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
    ],
];
```

Limit verification resends:
```php
Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
    ->middleware('throttle:3,60'); // 3 attempts per 60 minutes
```

---

### Deployment Steps

1. **Update .env on server:**
   ```bash
   nano /var/www/loggenerator/.env
   # Update all mail settings
   ```

2. **Clear caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   php artisan route:cache
   ```

3. **Test email sending:**
   ```bash
   php artisan tinker
   >>> Mail::raw('Production Test', function($m) { 
         $m->to('admin@yourdomain.com')->subject('Test'); 
     });
   ```

4. **Monitor logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

5. **Test end-to-end:**
   - Register test account
   - Check email delivery
   - Click verification link
   - Verify status updated

---

## Monitoring & Maintenance

### 1. Check Email Delivery Status

Brevo Dashboard → **Statistics**:
- Sent emails
- Delivered rate
- Bounce rate
- Spam rate

### 2. Monitor Laravel Logs

```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep -i mail

# Check for errors
grep -i "mail\|smtp\|verification" storage/logs/laravel.log
```

### 3. Audit Trail

Check verification activities:
```sql
SELECT * FROM audit_logs 
WHERE action IN ('REGISTER', 'EMAIL_VERIFIED', 'EMAIL_VERIFICATION_RESEND')
ORDER BY created_at DESC 
LIMIT 100;
```

### 4. User Verification Stats

```sql
-- Total registered users
SELECT COUNT(*) as total_users FROM users;

-- Verified users
SELECT COUNT(*) as verified_users FROM users WHERE email_verified_at IS NOT NULL;

-- Unverified users
SELECT COUNT(*) as unverified_users FROM users WHERE email_verified_at IS NULL;

-- Verification rate
SELECT 
    ROUND(
        (COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END)::FLOAT / COUNT(*)::FLOAT) * 100, 
        2
    ) as verification_rate_percent
FROM users
WHERE auth_provider = 'email';
```

---

## Best Practices

### 1. Email Content

✅ **DO:**
- Use clear, actionable button text
- Include app branding/logo
- Mention expiration time
- Provide alternative text link
- Include support contact

❌ **DON'T:**
- Use generic "Click here" text
- Forget to mention it expires
- Make link text too long
- Use suspicious-looking URLs

### 2. Security

✅ **DO:**
- Use signed URLs with expiration
- Log all verification attempts
- Rate limit verification resends
- Use HTTPS for all links
- Implement CSRF protection

❌ **DON'T:**
- Send verification tokens in plain text
- Use predictable verification URLs
- Allow unlimited resend attempts
- Store plaintext tokens

### 3. User Experience

✅ **DO:**
- Allow users to login before verifying
- Show clear verification status
- Provide easy resend option
- Auto-verify Google users
- Send confirmation after verification

❌ **DON'T:**
- Block login until verified (unless required)
- Hide verification status
- Make resend process complicated
- Send verification to Google users

---

## FAQ

**Q: Apakah user bisa login sebelum verify email?**
A: Ya, user bisa login dan mendapat token. Tapi Anda bisa restrict certain actions untuk unverified users dengan middleware.

**Q: Berapa lama link verification valid?**
A: 60 menit. Setelah itu user harus resend verification email.

**Q: Bagaimana jika user tidak menerima email?**
A: User bisa menggunakan endpoint `/email/verification-notification` untuk resend email.

**Q: Apakah Google users perlu verify email?**
A: Tidak. Google users otomatis verified karena Google sudah verify email mereka.

**Q: Bisa custom email template?**
A: Ya. Publish notification views dengan `php artisan vendor:publish --tag=laravel-notifications` lalu edit template.

**Q: Berapa limit free Brevo?**
A: 300 emails per day untuk free plan.

**Q: Bisa pakai Gmail SMTP?**
A: Bisa, tapi tidak recommended. Gmail punya limit ketat dan butuh App Password.

---

## Support & Resources

- **Laravel Email Verification Docs**: https://laravel.com/docs/11.x/verification
- **Brevo Documentation**: https://developers.brevo.com/docs
- **Laravel Notifications**: https://laravel.com/docs/11.x/notifications
- **Troubleshooting Guide**: See DEPLOYMENT.md

---

**Last Updated**: December 2025  
**Version**: 1.0.0
