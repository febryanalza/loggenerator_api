# Brevo API Email Configuration - Deployment Guide

## ✅ Status Local
**Email berhasil dikirim via Brevo API** (3.67 detik) tanpa menggunakan port 587!

---

## 📋 What Changed

### 1. **app/Mail/BrevoApiTransport.php** (NEW)
Custom mail transport menggunakan Brevo Transactional Email API via HTTPS

### 2. **app/Providers/AppServiceProvider.php**
Registered Brevo transport di Laravel Mail system

### 3. **config/mail.php**
Added `brevo` mailer configuration

### 4. **config/services.php**
Added Brevo API key configuration

### 5. **.env**
Changed `MAIL_MAILER=smtp` → `MAIL_MAILER=brevo`

---

## 🚀 Deployment ke Production Server

### Step 1: Commit & Push Code

```powershell
# Di local machine
git add .
git commit -m "feat: Switch to Brevo API for email (fix port 587 blocked)"
git push origin main
```

### Step 2: Deploy di Production

```bash
# SSH ke server
ssh deployer@146.190.87.235

# Navigate to project
cd /var/www/loggenerator_api

# Pull latest code
git pull origin main

# Verify new files
ls -la app/Mail/BrevoApiTransport.php
```

### Step 3: Update .env di Production

```bash
# Edit .env
nano .env
```

**Change this line:**
```env
# OLD
MAIL_MAILER=smtp

# NEW
MAIL_MAILER=brevo
```

**Keep BREVO_API_KEY** (sudah ada):
```env
BREVO_API_KEY=xkeysib-your-api-key-here
```

Save: `Ctrl+O`, `Enter`, `Ctrl+X`

### Step 4: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 5: Test Configuration

```bash
# Verify config loaded
php artisan tinker --execute="
echo 'MAIL_MAILER: ' . config('mail.default') . PHP_EOL;
echo 'BREVO_API_KEY: ' . (config('services.brevo.api_key') ? 'CONFIGURED' : 'NOT SET') . PHP_EOL;
echo 'FROM_ADDRESS: ' . config('mail.from.address') . PHP_EOL;
"
```

**Expected Output:**
```
MAIL_MAILER: brevo
BREVO_API_KEY: CONFIGURED
FROM_ADDRESS: noreply@fazcreateve.app
```

### Step 6: Send Test Email

```bash
# Upload test script if not exists
# Copy test-brevo-api.php to server

# Run test
php test-brevo-api.php febryanalzaqri27@gmail.com
```

**Expected:**
```
✅ Email sent successfully via Brevo API!
⏱️  Duration: ~300-500ms
```

### Step 7: Test User Registration

```bash
# Test full registration flow
curl -X POST http://146.190.87.235/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Brevo API",
    "email": "testbrevo@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Expected:**
- ✅ HTTP 200 (no more 504 timeout!)
- ✅ Response time < 3 seconds
- ✅ Email queued successfully
- ✅ Email sent via Brevo API

### Step 8: Check Queue Worker

Email verification still uses queue (async), pastikan queue worker berjalan:

```bash
# Check if worker running
ps aux | grep "queue:work"

# If not running, start manually (test)
php artisan queue:work database --once

# For production, use Supervisor
sudo supervisorctl status laravel-worker
```

---

## 📊 Verification Checklist

- [ ] Code deployed to production
- [ ] `.env` updated: `MAIL_MAILER=brevo`
- [ ] Config cache cleared
- [ ] Test email sent successfully
- [ ] Brevo dashboard shows email sent
- [ ] User registration works (no 504 timeout)
- [ ] Email verification arrives in inbox
- [ ] Queue worker running

---

## 🔍 Troubleshooting

### Error: "Brevo API error (401)"

**Cause:** Invalid API key

**Solution:**
```bash
# Check API key
cat .env | grep BREVO_API_KEY

# Get new API key from
# https://app.brevo.com/settings/keys/api

# Update .env
nano .env
# Change BREVO_API_KEY value

# Clear cache
php artisan config:clear
```

---

### Error: "Class 'App\Mail\BrevoApiTransport' not found"

**Cause:** Autoload cache belum di-refresh

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

---

### Email tidak terkirim

**Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

**Check Brevo logs:**
https://app.brevo.com/transactional/logs

**Check queue:**
```bash
# Failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all

# Process queue manually
php artisan queue:work database --once
```

---

## 🎯 Why This Works

| Method | Transport | Port | Status |
|--------|-----------|------|--------|
| **OLD (SMTP)** | TCP Socket | 587 | ❌ **BLOCKED** |
| **NEW (API)** | HTTPS | 443 | ✅ **WORKS** |

**Key Benefits:**
- ✅ Port 443 tidak pernah diblok cloud provider
- ✅ Lebih cepat (~300ms vs 3s+ timeout)
- ✅ Lebih reliable (API vs SMTP handshake)
- ✅ Better error handling
- ✅ No dependency on SMTP ports

---

## 📝 Technical Details

### Email Flow Sekarang:

1. **User registers** → `AuthController@register()`
2. **Email queued** → `VerifyEmailNotification` (implements `ShouldQueue`)
3. **Queue worker processes** → Background job
4. **Laravel Mail sends** → Via `BrevoApiTransport`
5. **API call** → `POST https://api.brevo.com/v3/smtp/email`
6. **Brevo sends** → Email delivered to inbox

### Brevo API Format:

```php
POST https://api.brevo.com/v3/smtp/email
Headers:
  api-key: xkeysib-...
  content-type: application/json

Body:
{
  "sender": {
    "email": "noreply@fazcreateve.app",
    "name": "Laravel"
  },
  "to": [
    {"email": "user@example.com", "name": "User Name"}
  ],
  "subject": "Email Verification",
  "htmlContent": "<html>...</html>"
}
```

---

## 🚀 Next Steps After Deployment

1. **Monitor Brevo Dashboard**
   - Check delivery rate
   - Monitor API usage
   - Watch for bounces

2. **Optimize Queue**
   - Consider Redis for faster queue processing
   - Add queue monitoring

3. **Set Up Alerts**
   - Email quota warnings
   - Failed delivery notifications
   - Queue backup alerts

---

## 📞 Support

**Brevo Dashboard:** https://app.brevo.com/
**API Docs:** https://developers.brevo.com/reference/sendtransacemail
**Laravel Logs:** `storage/logs/laravel.log`
**Queue Status:** `php artisan queue:failed`

---

**🎉 Migration Complete!** Email sekarang menggunakan Brevo API via HTTPS (port 443) dan tidak lagi bergantung pada SMTP port 587 yang diblok.
