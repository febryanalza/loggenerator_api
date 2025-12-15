# 📧 Email Verification - Quick Setup Guide

## ⚡ Quick Start (5 Minutes)

### 1. Update .env File

```env
# Change these 3 critical settings:
MAIL_MAILER=smtp                                    # ❌ was: log
MAIL_ENCRYPTION=tls                                 # ➕ ADD this
MAIL_FROM_ADDRESS="noreply@loggenerator.my.id"     # ✏️ Fix typo

# Your existing Brevo settings (already correct):
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=9dee81001@smtp-brevo.com
MAIL_PASSWORD=0I7RbJpvsjZSyC4q
MAIL_FROM_NAME="${APP_NAME}"

# Make sure APP_URL is correct:
APP_URL=http://localhost                           # Development
# APP_URL=https://yourdomain.com                   # Production
```

### 2. Test Email Sending

```bash
php artisan tinker

# Paste this:
Mail::raw('Test Email', function($m) { 
    $m->to('your-email@example.com')->subject('Test from LogGenerator'); 
});

# Check your inbox!
```

### 3. Test Registration

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

**Expected:** Email sent ✅

---

## 🎯 What Was Implemented

### ✅ New Files Created

1. **`app/Notifications/VerifyEmailNotification.php`**
   - Custom email notification for verification
   - Beautiful branded email template
   - 60-minute expiration link

2. **`EMAIL_VERIFICATION.md`**
   - Complete documentation (50+ pages)
   - API endpoints reference
   - Troubleshooting guide

3. **`.env.example.email-verification`**
   - All required environment variables
   - Comments and explanations

### ✅ Modified Files

1. **`app/Models/User.php`**
   - Implements `MustVerifyEmail` interface
   - Custom `sendEmailVerificationNotification()` method

2. **`app/Http/Controllers/Api/AuthController.php`**
   - `register()`: Sends verification email
   - `verifyEmail()`: Verifies email from link
   - `resendVerification()`: Resends verification email
   - `verificationStatus()`: Check verification status
   - `googleLogin()`: Auto-verify Google users

3. **`routes/api.php`**
   - `/email/verify/{id}/{hash}` - Public verification endpoint
   - `/email/verification-notification` - Resend (authenticated)
   - `/email/verification-status` - Check status (authenticated)

---

## 📋 Missing Variables Checklist

### ✅ Already Have (from your .env):
- ✅ MAIL_HOST
- ✅ MAIL_PORT
- ✅ MAIL_USERNAME
- ✅ MAIL_PASSWORD
- ✅ MAIL_FROM_NAME
- ✅ APP_URL

### ⚠️ Need to Add/Fix:
- ❌ `MAIL_MAILER=smtp` (change from `log`)
- ❌ `MAIL_ENCRYPTION=tls` (add this line)
- ❌ `MAIL_FROM_ADDRESS="noreply@loggenerator.my.id"` (fix typo: noreplay → noreply)

---

## 🚀 New API Endpoints

### 1. Register (Modified)
```
POST /api/register
```
**Changes:**
- Now sends verification email
- Response includes `verification_sent: true`
- Response includes `email_verified: false`

### 2. Verify Email (New)
```
GET /api/email/verify/{id}/{hash}?expires=xxx&signature=xxx
```
**Purpose:** Verify email from link in email

### 3. Resend Verification (New)
```
POST /api/email/verification-notification
Authorization: Bearer {token}
```
**Purpose:** Resend verification email if not received

### 4. Check Verification Status (New)
```
GET /api/email/verification-status
Authorization: Bearer {token}
```
**Purpose:** Check if email is verified

### 5. Google Login (Modified)
```
POST /api/auth/google
```
**Changes:**
- Auto-sets `email_verified_at` for Google users
- No verification email sent

---

## 🔄 How It Works

### Email Registration Flow:
```
User Register → Email Sent → User Clicks Link → Email Verified ✅
```

### Google Login Flow:
```
Google Login → Auto Verified ✅ (no email needed)
```

---

## ✅ Testing Checklist

### Test 1: Send Email
```bash
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```
**Expected:** ✅ Email received

### Test 2: Register User
```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```
**Expected:** 
- ✅ `verification_sent: true`
- ✅ Email received with verification link

### Test 3: Click Verification Link
Click link from email

**Expected:** ✅ `"Email verified successfully"`

### Test 4: Check Status
```bash
curl -X GET http://localhost/api/email/verification-status \
  -H "Authorization: Bearer YOUR_TOKEN"
```
**Expected:** ✅ `"email_verified": true`

### Test 5: Google Login
```bash
curl -X POST http://localhost/api/auth/google \
  -H "Content-Type: application/json" \
  -d '{"id_token":"GOOGLE_TOKEN"}'
```
**Expected:** 
- ✅ `"email_verified": true`
- ✅ No email sent (auto-verified)

---

## 🔧 Troubleshooting

### ❌ Email Not Sending

**Problem:** Registration success but no email received

**Solution:**
```env
# Check .env:
MAIL_MAILER=smtp  # NOT 'log'
```

```bash
# Clear cache:
php artisan config:clear
php artisan cache:clear
```

### ❌ Invalid Verification Link

**Problem:** Click link → "Invalid verification link"

**Solution:**
```env
# Check APP_URL matches your domain:
APP_URL=http://localhost  # Development
```

```bash
# Clear config cache:
php artisan config:cache
```

### ❌ SMTP Authentication Failed

**Problem:** Error 535 - Authentication failed

**Solution:**
1. Check Brevo dashboard → SMTP settings
2. Verify credentials are correct
3. Try generating new SMTP password

---

## 📚 Full Documentation

For complete guide, see:
- 📖 **[EMAIL_VERIFICATION.md](EMAIL_VERIFICATION.md)** - Full documentation (50+ pages)
  - Complete API reference
  - Testing guide
  - Troubleshooting
  - Production checklist
  - Best practices

---

## 🎉 You're Done!

Your email verification system is now ready. Users who register will receive verification emails, while Google users are automatically verified.

**Next Steps:**
1. Update your .env with the 3 required variables
2. Test email sending with tinker
3. Test registration flow
4. Read full documentation for production deployment

---

**Need Help?**
- Check logs: `tail -f storage/logs/laravel.log`
- See full docs: [EMAIL_VERIFICATION.md](EMAIL_VERIFICATION.md)
- Brevo dashboard: https://app.brevo.com

**Last Updated:** December 2025
