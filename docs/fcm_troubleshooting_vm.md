# Troubleshooting FCM Push Notifications di VM Server

## Masalah yang Dilaporkan
- ✅ Notifikasi masuk ke database (terlihat di aplikasi)
- ❌ Push notification TIDAK muncul saat aplikasi ditutup
- ✅ Permission notifikasi sudah diberikan
- 🌐 Server Laravel berjalan di VM (bukan lokal)

---

## Diagnosa Masalah

### 1️⃣ **QUEUE WORKER TIDAK BERJALAN** (Penyebab Paling Umum)

`SendFCMNotification` adalah listener yang berjalan di **queue** (implements `ShouldQueue`). Jika queue worker tidak berjalan, event `NotificationSent` akan masuk ke queue database tapi **TIDAK DIPROSES**.

#### Cara Cek di VM Server:

```bash
# SSH ke VM server Anda
ssh user@your-vm-ip

# Cek apakah queue worker sedang berjalan
ps aux | grep "queue:work"

# Atau menggunakan systemctl jika menggunakan supervisor
sudo systemctl status laravel-worker
```

#### Solusi: Start Queue Worker

**Opsi A: Manual (untuk testing)**
```bash
cd /path/to/loggenerator_api
php artisan queue:work --tries=3 --timeout=60
```

⚠️ **PENTING**: Jangan tutup terminal! Worker akan stop jika terminal ditutup.

**Opsi B: Menggunakan Supervisor (Production - Recommended)**
```bash
# Install supervisor
sudo apt-get install supervisor

# Buat config file
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Isi file config:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/loggenerator_api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-worker.log
stopwaitsecs=3600
```

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Cek status
sudo supervisorctl status
```

#### Verifikasi Queue Worker:
```bash
# Cek jobs yang pending
php artisan queue:work --once

# Atau lihat queue table
php artisan tinker
>>> DB::table('jobs')->count()
```

---

### 2️⃣ **FIREBASE CREDENTIALS BELUM DI-SETUP**

Firebase membutuhkan file `service-account.json` untuk mengirim push notifications.

#### Cara Cek di VM Server:

```bash
cd /path/to/loggenerator_api
ls -la storage/app/firebase/service-account.json
```

Jika file **TIDAK ADA**, maka FCM tidak akan berfungsi.

#### Solusi: Upload service-account.json ke VM

**Step 1: Download dari Firebase Console**
1. Buka https://console.firebase.google.com
2. Pilih project Anda
3. Settings ⚙️ → Project Settings → Service Accounts
4. Klik "Generate new private key"
5. Download file JSON

**Step 2: Upload ke VM Server**
```bash
# Dari komputer lokal
scp service-account.json user@your-vm-ip:/path/to/loggenerator_api/storage/app/firebase/

# Atau gunakan SFTP, FileZilla, WinSCP, dll
```

**Step 3: Set Permissions**
```bash
# Di VM server
chmod 600 /path/to/loggenerator_api/storage/app/firebase/service-account.json
chown www-data:www-data /path/to/loggenerator_api/storage/app/firebase/service-account.json
```

---

### 3️⃣ **CEK LARAVEL LOGS UNTUK ERROR**

#### Di VM Server:

```bash
# Lihat log terbaru
tail -f /path/to/loggenerator_api/storage/logs/laravel.log

# Atau cari error FCM spesifik
grep -i "fcm" /path/to/loggenerator_api/storage/logs/laravel.log | tail -20
grep -i "firebase" /path/to/loggenerator_api/storage/logs/laravel.log | tail -20
grep -i "notification" /path/to/loggenerator_api/storage/logs/laravel.log | tail -20
```

**Error yang Mungkin Muncul:**

| Error | Penyebab | Solusi |
|-------|----------|--------|
| `FCM not configured` | service-account.json tidak ada | Upload service-account.json |
| `No FCM tokens found for user` | User belum login/register FCM token | Pastikan user sudah login di app |
| `Invalid JWT signature` | service-account.json salah/korup | Re-download file dari Firebase Console |
| `Undefined array key "project_id"` | service-account.json format salah | Pastikan file JSON valid |
| `Connection timeout` | VM tidak bisa akses fcm.googleapis.com | Cek firewall/network VM |

---

### 4️⃣ **VERIFIKASI FCM TOKENS DI DATABASE**

User harus login ke aplikasi Flutter agar FCM token tersimpan di database.

#### Cek di VM Server:

```bash
php artisan tinker
```

```php
// Cek total FCM tokens
\App\Models\FcmToken::count()

// Cek token untuk user tertentu (ganti dengan user_id yang Anda kirim notifikasi)
\App\Models\FcmToken::where('user_id', 'USER_ID_HERE')->where('is_active', true)->get()

// Cek user mana saja yang punya token
\App\Models\FcmToken::where('is_active', true)->pluck('user_id')
```

**Jika tidak ada token:**
- User belum login ke aplikasi
- FCMService di Flutter belum berjalan
- AuthService.storeFCMToken() gagal mengirim ke backend

---

### 5️⃣ **TEST MANUAL FCM DARI VM**

#### Test Langsung dari Tinker:

```bash
php artisan tinker
```

```php
// Import service
$fcm = app(\App\Services\FirebaseService::class);

// Cek apakah configured
$fcm->isConfigured() // Harus return true

// Test kirim ke device token langsung
$token = \App\Models\FcmToken::where('is_active', true)->first();
if ($token) {
    $result = $fcm->sendToDevice(
        $token->token,
        'Test Notification',
        'Ini adalah test dari tinker',
        ['test' => true]
    );
    var_dump($result);
}

// Atau kirim ke user
$userId = 'USER_ID_HERE';
$result = $fcm->sendToUser(
    $userId,
    'Test dari Tinker',
    'Apakah notifikasi ini muncul?',
    ['source' => 'manual_test']
);
var_dump($result);
```

---

### 6️⃣ **CEK QUEUE CONNECTION CONFIG**

Pastikan Laravel dikonfigurasi untuk menggunakan queue yang tepat.

#### Di VM Server:

```bash
# Cek .env
cat /path/to/loggenerator_api/.env | grep QUEUE
```

Seharusnya:
```env
QUEUE_CONNECTION=database
# atau
QUEUE_CONNECTION=redis
```

**JANGAN gunakan:**
```env
QUEUE_CONNECTION=sync  # Ini tidak menggunakan queue worker!
```

Jika menggunakan `sync`, ubah ke `database`:
```bash
nano /path/to/loggenerator_api/.env
# Ubah QUEUE_CONNECTION=database

# Restart queue worker (jika menggunakan supervisor)
sudo supervisorctl restart laravel-worker:*
```

---

## Checklist Debugging (Jalankan Berurutan)

### ✅ Di VM Server:

```bash
# 1. Cek queue worker
ps aux | grep "queue:work"
# Jika tidak ada, start dengan: php artisan queue:work

# 2. Cek Firebase credentials
ls -la storage/app/firebase/service-account.json
# Jika tidak ada, upload file dari Firebase Console

# 3. Cek queue config
cat .env | grep QUEUE_CONNECTION
# Harus: database atau redis, BUKAN sync

# 4. Cek FCM tokens di database
php artisan tinker --execute="echo 'FCM Tokens: ' . \App\Models\FcmToken::where('is_active', true)->count();"

# 5. Cek logs
tail -20 storage/logs/laravel.log | grep -i "fcm\|firebase\|notification"

# 6. Test manual FCM
php artisan tinker
>>> $fcm = app(\App\Services\FirebaseService::class);
>>> $fcm->isConfigured()
>>> exit

# 7. Monitor queue processing (buka di terminal terpisah)
php artisan queue:work --verbose
```

---

## Alur Kerja FCM (Untuk Pemahaman)

```
Admin kirim notifikasi (web panel)
    ↓
NotificationController::send()
    ↓
NotificationFacade::send($users, $notification)  ← Simpan ke database
    ↓
event(new NotificationSent(...))  ← Trigger event
    ↓
SendFCMNotification::handle()  ← Masuk ke QUEUE (butuh worker!)
    ↓
[QUEUE WORKER PROCESS]  ← ⚠️ HARUS BERJALAN!
    ↓
FirebaseService::sendToUser()
    ↓
FCM API (fcm.googleapis.com)  ← Butuh service-account.json
    ↓
Android/iOS Device  ← Push notification muncul!
```

**Titik Kegagalan Umum:**
1. ❌ Queue worker tidak berjalan → Event tidak diproses
2. ❌ service-account.json tidak ada → FCM API gagal
3. ❌ FCM token tidak ada di database → Tidak ada device target
4. ❌ QUEUE_CONNECTION=sync → Queue tidak digunakan
5. ❌ Firewall VM blokir fcm.googleapis.com → Connection timeout

---

## Quick Fix Commands (Copy-Paste)

### Jika Queue Worker Belum Berjalan:
```bash
cd /path/to/loggenerator_api
nohup php artisan queue:work --tries=3 --timeout=60 > storage/logs/queue.log 2>&1 &
```

### Jika service-account.json Belum Ada:
```bash
# Download dari Firebase Console dulu, lalu:
mkdir -p storage/app/firebase
# Upload file dengan scp/sftp, lalu:
chmod 600 storage/app/firebase/service-account.json
chown www-data:www-data storage/app/firebase/service-account.json
```

### Monitor Real-Time:
```bash
# Terminal 1: Monitor logs
tail -f storage/logs/laravel.log

# Terminal 2: Monitor queue worker
php artisan queue:work --verbose

# Terminal 3: Kirim test notification dari admin panel
```

---

## Kesimpulan

**3 Hal yang WAJIB Ada:**
1. ✅ Queue worker berjalan di VM (`php artisan queue:work`)
2. ✅ service-account.json di `storage/app/firebase/`
3. ✅ User punya FCM token aktif di database

**Cara Validasi Cepat:**
```bash
# Di VM server
ps aux | grep queue:work  # Harus ada output
ls storage/app/firebase/service-account.json  # File harus exist
php artisan tinker --execute="\App\Models\FcmToken::count()"  # Harus > 0
```

Jika ketiga hal ini sudah terpenuhi, push notification **PASTI** akan berfungsi! 🚀
