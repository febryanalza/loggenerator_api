# 🔧 PANDUAN KONFIGURASI .ENV PRODUCTION

## 📋 CHECKLIST SEBELUM DEPLOY

### ✅ Step 1: Copy Template Production
```bash
# Backup .env development Anda
Copy-Item .env .env.development.backup

# Copy template production
Copy-Item .env.production .env
```

---

## 🔐 KONFIGURASI WAJIB (CRITICAL)

### 1. Application Key 🔑
```bash
# Generate application key baru untuk production
php artisan key:generate

# Output akan otomatis update APP_KEY di .env
# Contoh: APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**✅ Verifikasi**:
```bash
# Check APP_KEY sudah terisi
php artisan tinker
>>> config('app.key')
# Harus return string panjang, bukan null
```

---

### 2. Application Environment ⚙️

**WAJIB diubah**:
```env
APP_ENV=production          # ✅ Harus production
APP_DEBUG=false             # ✅ Harus false (keamanan)
APP_URL=https://yourdomain.com  # ✅ Ganti dengan domain Anda
```

**Bahaya jika APP_DEBUG=true**:
- ❌ Error message expose kode
- ❌ Database credentials terlihat
- ❌ Path sistem terekspos
- ❌ Vulnerability untuk attacker

---

### 3. Database Configuration 🗄️

**Ganti semua [CHANGE_THIS]**:
```env
DB_CONNECTION=pgsql
DB_HOST=your-db-server.com          # IP atau hostname database
DB_PORT=5432
DB_DATABASE=loggenerator_production # Nama database production
DB_USERNAME=your_db_user            # Username database
DB_PASSWORD=StR0nG_P@ssw0rd_16Ch@rs # Min 16 karakter!
```

**Generate Strong Password**:
```bash
# PowerShell - Generate password 20 karakter
-join ((48..57) + (65..90) + (97..122) + 33,35,36,37,38,42,64 | Get-Random -Count 20 | % {[char]$_})

# Atau gunakan online generator:
# https://passwordsgenerator.net/
# Settings: 20 chars, symbols, numbers, upper, lower
```

**Password Requirements**:
- ✅ Minimum 16 karakter
- ✅ Kombinasi huruf besar + kecil
- ✅ Angka
- ✅ Simbol (!@#$%^&*)
- ✅ Tidak ada kata kamus
- ✅ Unik (tidak dipakai di tempat lain)

**Test Database Connection**:
```bash
php artisan migrate:status

# Jika berhasil, akan show list migrations
# Jika gagal, cek kredensial database
```

---

### 4. Session Security 🔒

**WAJIB untuk HTTPS**:
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120                # 2 jam
SESSION_DOMAIN=.yourdomain.com      # Domain Anda dengan titik di depan
SESSION_SECURE_COOKIE=true          # ✅ Hanya via HTTPS
SESSION_HTTP_ONLY=true              # ✅ Tidak bisa diakses JavaScript
SESSION_SAME_SITE=lax               # ✅ CSRF protection
```

**⚠️ SESSION_SECURE_COOKIE=true hanya work dengan HTTPS!**

---

### 5. CORS & Sanctum 🌐

**Ganti dengan domain production**:
```env
# Sanctum - Allow cookies dari domain ini
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com,api.yourdomain.com

# Frontend URL untuk CORS
FRONTEND_URL=https://yourdomain.com
```

**Multiple Domains** (jika punya banyak):
```env
SANCTUM_STATEFUL_DOMAINS=domain1.com,www.domain1.com,domain2.com,www.domain2.com
```

---

### 6. Mail Configuration 📧

**Contoh untuk Gmail**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_specific_password  # Bukan password email biasa!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Log Generator API"
```

**Setup Gmail App Password**:
1. Buka Google Account → Security
2. Enable "2-Step Verification"
3. Go to "App passwords"
4. Create new app password untuk "Mail"
5. Copy password 16 digit
6. Paste ke MAIL_PASSWORD

**Alternatif Mail Provider**:

**SendGrid**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

**Mailgun**:
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=your_mailgun_secret
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

**Test Email**:
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('your@email.com')->subject('Test'); });
```

---

### 7. Google OAuth (Optional) 🔐

**⚠️ PENTING: Buat OAuth credentials BARU untuk production!**

```env
GOOGLE_CLIENT_ID=your-production-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-your-production-secret
GOOGLE_ANDROID_CLIENT_ID=your-android-id.apps.googleusercontent.com
GOOGLE_IOS_CLIENT_ID=your-ios-id.apps.googleusercontent.com
```

**Setup Google OAuth Production**:
1. Buka https://console.cloud.google.com
2. Create new project atau pilih existing
3. APIs & Services → Credentials
4. Create OAuth 2.0 Client ID
5. Authorized redirect URIs:
   - `https://yourdomain.com/auth/google/callback`
6. Copy Client ID & Client Secret
7. Paste ke .env

**⚠️ JANGAN gunakan credentials development di production!**

---

## 🔒 KONFIGURASI KEAMANAN TAMBAHAN

### 8. Logging Configuration 📝

**Production logging**:
```env
LOG_CHANNEL=daily           # Rotasi log harian
LOG_LEVEL=error             # Hanya log error
LOG_DAILY_DAYS=14           # Simpan 14 hari
```

**Log Levels**:
- `emergency`: System unusable
- `alert`: Immediate action required
- `critical`: Critical conditions
- `error`: Runtime errors (✅ Recommended for production)
- `warning`: Warning messages
- `notice`: Normal but significant
- `info`: Informational messages
- `debug`: Debug messages (❌ JANGAN untuk production)

---

### 9. Cache Configuration ⚡

**Untuk production dengan traffic sedang**:
```env
CACHE_STORE=file
QUEUE_CONNECTION=database
```

**Untuk production dengan traffic tinggi** (Recommended):
```env
# Install Redis terlebih dahulu
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
```

**Install Redis** (Ubuntu/Debian):
```bash
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Install PHP Redis extension
sudo apt install php-redis
sudo systemctl restart php8.2-fpm
```

---

### 10. Rate Limiting 🚦

**Tambahkan ke .env** (Custom variables):
```env
THROTTLE_LOGIN=5            # 5 login attempts per minute
THROTTLE_API=60             # 60 API requests per minute
THROTTLE_PUBLIC=30          # 30 public requests per minute
```

---

## 📊 VERIFIKASI KONFIGURASI

### Pre-Deployment Checklist

```bash
# 1. Check environment
php artisan config:show app.env
# Harus: production

# 2. Check debug mode
php artisan config:show app.debug
# Harus: false

# 3. Check database connection
php artisan migrate:status
# Harus berhasil connect

# 4. Check app key
php artisan config:show app.key
# Harus ada value (bukan null)

# 5. Test cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Security audit
composer audit
# Harus tidak ada vulnerabilities

# 7. Check permissions (Linux)
ls -la storage/
ls -la bootstrap/cache/
# Harus writable (755 atau 775)
```

---

## 🔐 SECURITY CHECKLIST

### File Permissions (Linux/Server)

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/loggenerator_api

# Directories
sudo find /var/www/loggenerator_api -type d -exec chmod 755 {} \;

# Files
sudo find /var/www/loggenerator_api -type f -exec chmod 644 {} \;

# Storage & Cache (writable)
sudo chmod -R 775 /var/www/loggenerator_api/storage
sudo chmod -R 775 /var/www/loggenerator_api/bootstrap/cache

# .env (read only by owner)
sudo chmod 600 /var/www/loggenerator_api/.env

# Artisan (executable)
sudo chmod 755 /var/www/loggenerator_api/artisan
```

---

## 🚨 COMMON MISTAKES

### ❌ KESALAHAN UMUM:

1. **APP_DEBUG=true di production**
   - Fix: `APP_DEBUG=false`

2. **Weak database password**
   - Fix: Generate password 16+ karakter dengan symbols

3. **SESSION_SECURE_COOKIE=false dengan HTTPS**
   - Fix: `SESSION_SECURE_COOKIE=true`

4. **Menggunakan localhost di SANCTUM_STATEFUL_DOMAINS**
   - Fix: Ganti dengan domain production

5. **LOG_LEVEL=debug di production**
   - Fix: `LOG_LEVEL=error`

6. **Tidak set SESSION_DOMAIN**
   - Fix: `SESSION_DOMAIN=.yourdomain.com`

7. **Google OAuth development credentials di production**
   - Fix: Buat credentials baru untuk production

8. **APP_KEY tidak di-generate**
   - Fix: `php artisan key:generate`

---

## 📝 TEMPLATE LENGKAP SIAP PAKAI

### Untuk VPS/Cloud Server dengan PostgreSQL

```env
# === APPLICATION ===
APP_NAME="Log Generator API"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://api.yourdomain.com
APP_TIMEZONE=Asia/Jakarta

# === DATABASE ===
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=loggenerator_prod
DB_USERNAME=loggenerator_user
DB_PASSWORD=YourStrongPassword123!@#

# === SESSION ===
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# === CACHE & QUEUE ===
CACHE_STORE=file
QUEUE_CONNECTION=database

# === MAIL ===
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# === SANCTUM & CORS ===
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com,api.yourdomain.com
FRONTEND_URL=https://yourdomain.com

# === LOGGING ===
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DAILY_DAYS=14

# === GOOGLE OAUTH ===
GOOGLE_CLIENT_ID=your-production-client-id
GOOGLE_CLIENT_SECRET=your-production-secret

# === ADMIN DASHBOARD ===
ADMIN_DASHBOARD_ENABLED=true
ADMIN_DASHBOARD_URL=/admin
```

---

## 🎯 DEPLOYMENT WORKFLOW

### 1. Local Preparation
```bash
# Copy dan edit .env.production
Copy-Item .env.production .env

# Edit semua [CHANGE_THIS] values
notepad .env

# Test locally dengan production config
php artisan config:clear
php artisan serve
```

### 2. Deploy to Server
```bash
# Upload files (EXCLUDE .env)
# .env akan dibuat manual di server

# Di server, create .env
sudo nano /var/www/loggenerator_api/.env
# Paste production configuration

# Generate key
php artisan key:generate

# Set permissions
sudo chmod 600 .env

# Run migrations
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Verification
```bash
# Test endpoints
curl https://yourdomain.com/api/health

# Check logs
tail -f storage/logs/laravel.log

# Monitor
# Setup monitoring tools (optional)
```

---

## 📞 TROUBLESHOOTING

### Issue: "419 Page Expired" Error
**Solution**:
```env
SESSION_DOMAIN=.yourdomain.com  # Tambahkan titik di depan
SESSION_SAME_SITE=lax           # Bukan 'strict'
```

### Issue: CORS Error
**Solution**:
```env
# Check SANCTUM_STATEFUL_DOMAINS
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com

# Check FRONTEND_URL
FRONTEND_URL=https://yourdomain.com
```

### Issue: Session Not Persisting
**Solution**:
```env
SESSION_DRIVER=database         # Bukan 'file'
SESSION_SECURE_COOKIE=true      # Hanya jika HTTPS
```

### Issue: Email Not Sending
**Solution**:
```bash
# Test mail configuration
php artisan tinker
>>> config('mail')

# Check SMTP credentials
# Check firewall allows port 587
```

---

## ✅ FINAL CHECKLIST

Sebelum deploy, pastikan:

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated
- [ ] `APP_URL` set to production domain
- [ ] `DB_PASSWORD` strong (16+ chars)
- [ ] `DB_HOST` pointed to production database
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_DOMAIN` set correctly
- [ ] `SANCTUM_STATEFUL_DOMAINS` set to production domains
- [ ] `FRONTEND_URL` set to production URL
- [ ] `MAIL_*` configured and tested
- [ ] `LOG_LEVEL=error`
- [ ] `GOOGLE_CLIENT_ID` production credentials
- [ ] All test files removed
- [ ] Security headers configured
- [ ] Rate limiting implemented
- [ ] HTTPS certificate installed
- [ ] Firewall configured
- [ ] Database backup setup

---

**🎉 CONFIGURATION COMPLETE!**

Setelah semua checklist di atas selesai, aplikasi Anda siap untuk **Limited Production Trial**.

**Next Steps**:
1. Deploy to staging first
2. Test all features
3. Monitor for 24-48 hours
4. Fix any issues
5. Deploy to production

---

**Need Help?**
- Check Laravel logs: `storage/logs/laravel.log`
- Check web server logs: `/var/log/nginx/error.log`
- Use `php artisan config:show` to debug config values

---

**Last Updated**: October 24, 2025
