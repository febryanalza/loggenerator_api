# 🚀 .ENV PRODUCTION - QUICK REFERENCE CARD

## ⚠️ WAJIB DIUBAH SEBELUM DEPLOY

```env
# 1. APPLICATION
APP_ENV=production                    # ✅ WAJIB
APP_DEBUG=false                       # ✅ WAJIB
APP_KEY=[RUN: php artisan key:generate]
APP_URL=https://yourdomain.com        # ✅ Ganti domain

# 2. DATABASE
DB_HOST=[your-db-host]               # ✅ Ganti
DB_DATABASE=[db_name]                # ✅ Ganti
DB_USERNAME=[db_user]                # ✅ Ganti
DB_PASSWORD=[min_16_chars]           # ✅ STRONG PASSWORD

# 3. SESSION SECURITY
SESSION_DOMAIN=.yourdomain.com       # ✅ Ganti domain
SESSION_SECURE_COOKIE=true           # ✅ WAJIB
SESSION_HTTP_ONLY=true               # ✅ WAJIB

# 4. SANCTUM & CORS
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com  # ✅ Ganti
FRONTEND_URL=https://yourdomain.com  # ✅ Ganti

# 5. MAIL
MAIL_HOST=smtp.gmail.com             # ✅ Sesuaikan provider
MAIL_USERNAME=[your_email]           # ✅ Ganti
MAIL_PASSWORD=[app_password]         # ✅ App-specific password
MAIL_FROM_ADDRESS=noreply@yourdomain.com  # ✅ Ganti

# 6. GOOGLE OAUTH
GOOGLE_CLIENT_ID=[production_id]     # ✅ Buat baru!
GOOGLE_CLIENT_SECRET=[production_secret]  # ✅ Buat baru!

# 7. LOGGING
LOG_LEVEL=error                      # ✅ WAJIB (bukan debug)
```

---

## 🔐 PASSWORD GENERATOR

**PowerShell**:
```powershell
-join ((48..57) + (65..90) + (97..122) + 33,35,36,37,38,42,64 | Get-Random -Count 20 | % {[char]$_})
```

**Requirements**: Min 16 chars, Upper+Lower+Numbers+Symbols

---

## ✅ VERIFICATION COMMANDS

```bash
# 1. Generate app key
php artisan key:generate

# 2. Test database
php artisan migrate:status

# 3. Clear & cache
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Check config
php artisan config:show app.env      # Must: production
php artisan config:show app.debug    # Must: false

# 5. Security audit
composer audit

# 6. Test email
php artisan tinker
>>> Mail::raw('Test', function($m){$m->to('test@email.com')->subject('Test');});
```

---

## 🚨 CRITICAL SECURITY CHECKLIST

- [ ] APP_DEBUG=false
- [ ] Strong DB_PASSWORD (16+ chars)
- [ ] SESSION_SECURE_COOKIE=true
- [ ] SANCTUM_STATEFUL_DOMAINS updated
- [ ] LOG_LEVEL=error
- [ ] Google OAuth production credentials
- [ ] All test files removed
- [ ] .env file permissions 600

---

## 📊 FILE PERMISSIONS (Linux)

```bash
# Ownership
sudo chown -R www-data:www-data /var/www/loggenerator_api

# Directories
sudo find . -type d -exec chmod 755 {} \;

# Files
sudo find . -type f -exec chmod 644 {} \;

# Storage (writable)
sudo chmod -R 775 storage bootstrap/cache

# .env (secure)
sudo chmod 600 .env
```

---

## 🔥 COMMON ERRORS & FIXES

| Error | Fix |
|-------|-----|
| "419 Page Expired" | SESSION_DOMAIN=.yourdomain.com |
| CORS Error | Check SANCTUM_STATEFUL_DOMAINS |
| Session not persisting | SESSION_DRIVER=database |
| Email not sending | Verify MAIL_PASSWORD (app password) |
| 500 Error | Check APP_KEY generated |

---

## 📞 DEPLOYMENT SEQUENCE

1. ✅ Copy `.env.production` to `.env`
2. ✅ Edit all [CHANGE_THIS] values
3. ✅ Run `php artisan key:generate`
4. ✅ Test database connection
5. ✅ Test email sending
6. ✅ Remove all test files
7. ✅ Clear & cache configs
8. ✅ Run security audit
9. ✅ Deploy to staging
10. ✅ Test all features
11. ✅ Deploy to production

---

**Print this card and keep it safe! 🖨️**
