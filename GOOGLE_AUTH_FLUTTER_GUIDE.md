# Google Authentication Implementation Guide untuk Flutter

Panduan lengkap implementasi Google Authentication di aplikasi Flutter (Android & iOS) yang terintegrasi dengan backend Laravel.

## 📋 Daftar Isi
1. [Persiapan Backend](#persiapan-backend)
2. [Setup Google Console](#setup-google-console)
3. [Konfigurasi Android](#konfigurasi-android)
4. [Konfigurasi iOS](#konfigurasi-ios)
5. [Implementasi Flutter](#implementasi-flutter)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)

## ✅ Persiapan Backend

Backend Laravel sudah disiapkan dengan:
- Google API Client dependency (`google/apiclient`)
- Database migration untuk field Google auth
- Google authentication service (`GoogleAuthService`)
- API endpoints untuk Google login
- Model User yang mendukung Google auth

### API Endpoints yang Tersedia:
- `POST /api/auth/google` - Login dengan Google ID Token
- `POST /api/auth/google/unlink` - Unlink akun Google (protected)

## 🔧 Setup Google Console

### 1. Buat Project di Google Cloud Console
1. Kunjungi [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project existing
3. Enable Google+ API dan Google Sign-In API

### 2. Buat OAuth 2.0 Credentials
1. Masuk ke **APIs & Services** > **Credentials**
2. Klik **Create Credentials** > **OAuth client ID**
3. Buat 3 client ID:
   - **Android** (untuk aplikasi Android)
   - **iOS** (untuk aplikasi iOS)  
   - **Web** (untuk backend Laravel)

### 3. Konfigurasi Web Client (Backend)
```
Application type: Web application
Name: LogGenerator Backend
Authorized redirect URIs: (kosongkan untuk server-side verification)
```

### 4. Download dan Simpan Credentials
- Download file JSON untuk setiap platform
- Simpan semua client ID ke file `.env` Laravel:
```env
GOOGLE_CLIENT_ID=269022547585-vp32h6jtndjauqjpbgnmej5a026er5b7.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_ANDROID_CLIENT_ID=269022547585-hr6c0tkp89804m196nt5m90kheraf7so.apps.googleusercontent.com
GOOGLE_IOS_CLIENT_ID=269022547585-enh5sub2f0lq6f0cgldpe44da1939t3r.apps.googleusercontent.com
```

**✅ SUDAH DIKONFIGURASI** - Backend Laravel sudah mendukung multi-platform authentication.

## 📱 Konfigurasi Android

### 1. Tambahkan Dependencies
Tambahkan ke `android/app/build.gradle`:
```gradle
dependencies {
    implementation 'com.google.android.gms:play-services-auth:20.7.0'
}
```

### 2. Tambahkan Google Services
1. Download `google-services.json` dari Google Console untuk Android client ID:
   **Client ID:** `269022547585-hr6c0tkp89804m196nt5m90kheraf7so.apps.googleusercontent.com`
2. Letakkan di `android/app/google-services.json`
3. Edit `android/build.gradle`:
```gradle
buildscript {
    dependencies {
        classpath 'com.google.gms:google-services:4.3.15'
    }
}
```

4. Edit `android/app/build.gradle`:
```gradle
apply plugin: 'com.google.gms.google-services'
```

### 3. Konfigurasi SHA-1 Fingerprint
```bash
# Debug keystore
keytool -list -v -alias androiddebugkey -keystore ~/.android/debug.keystore

# Release keystore (jika ada)
keytool -list -v -alias your-alias -keystore /path/to/your/keystore.jks
```

Tambahkan SHA-1 fingerprint ke Google Console OAuth client Android.

## 🍎 Konfigurasi iOS

### 1. Tambahkan Google Service Info
1. Download `GoogleService-Info.plist` dari Google Console untuk iOS client ID:
   **Client ID:** `269022547585-enh5sub2f0lq6f0cgldpe44da1939t3r.apps.googleusercontent.com`
   **Reversed Client ID:** `com.googleusercontent.apps.269022547585-enh5sub2f0lq6f0cgldpe44da1939t3r`
2. Drag ke `ios/Runner/` di Xcode
3. Pastikan target membership benar

### 2. Konfigurasi URL Scheme
Edit `ios/Runner/Info.plist`:
```xml
<key>CFBundleURLTypes</key>
<array>
    <dict>
        <key>CFBundleURLName</key>
        <string>REVERSED_CLIENT_ID</string>
        <key>CFBundleURLSchemes</key>
        <array>
            <string>YOUR_REVERSED_CLIENT_ID</string>
        </array>
    </dict>
</array>
```

Ganti `YOUR_REVERSED_CLIENT_ID` dengan nilai dari `GoogleService-Info.plist`.

## 🎯 Implementasi Flutter

### 1. Tambahkan Dependencies
```yaml
dependencies:
  flutter:
    sdk: flutter
  google_sign_in: ^6.1.5
  http: ^1.1.0
  shared_preferences: ^2.2.2
```

### 2. Buat Google Auth Service
```dart
// lib/services/google_auth_service.dart
import 'dart:convert';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class GoogleAuthService {
  static const String baseUrl = 'https://your-domain.com/api';
  
  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: [
      'email',
      'profile',
    ],
  );

  // Login dengan Google
  Future<Map<String, dynamic>?> signInWithGoogle() async {
    try {
      // Sign in dengan Google
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      
      if (googleUser == null) {
        return null; // User cancelled
      }

      // Get authentication details
      final GoogleSignInAuthentication googleAuth = 
          await googleUser.authentication;

      if (googleAuth.idToken == null) {
        throw Exception('Failed to get ID token');
      }

      // Send ID token to backend
      return await _authenticateWithBackend(googleAuth.idToken!);
      
    } catch (error) {
      print('Google sign in error: $error');
      rethrow;
    }
  }

  // Kirim ID token ke backend
  Future<Map<String, dynamic>> _authenticateWithBackend(String idToken) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/google'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'id_token': idToken,
        'device_name': 'Flutter App',
      }),
    );

    final data = json.decode(response.body);

    if (response.statusCode == 200 && data['success'] == true) {
      // Simpan token
      await _saveAuthToken(data['data']['token']);
      return data;
    } else {
      throw Exception(data['message'] ?? 'Authentication failed');
    }
  }

  // Simpan auth token
  Future<void> _saveAuthToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  // Get auth token
  Future<String?> getAuthToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  // Logout
  Future<void> signOut() async {
    try {
      // Logout dari Google
      await _googleSignIn.signOut();
      
      // Hapus token dari storage
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
      
      // Optional: Panggil backend logout endpoint
      final token = await getAuthToken();
      if (token != null) {
        await http.post(
          Uri.parse('$baseUrl/logout'),
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        );
      }
    } catch (error) {
      print('Sign out error: $error');
    }
  }

  // Check login status
  Future<bool> isSignedIn() async {
    final token = await getAuthToken();
    return token != null && await _googleSignIn.isSignedIn();
  }
}
```

### 3. Buat Login Screen
```dart
// lib/screens/login_screen.dart
import 'package:flutter/material.dart';
import '../services/google_auth_service.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final GoogleAuthService _authService = GoogleAuthService();
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Login'),
      ),
      body: Center(
        child: Padding(
          padding: EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                'Welcome to LogGenerator',
                style: Theme.of(context).textTheme.headlineMedium,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: 48),
              
              if (_isLoading)
                CircularProgressIndicator()
              else
                ElevatedButton.icon(
                  onPressed: _handleGoogleSignIn,
                  icon: Image.asset(
                    'assets/images/google_logo.png', // Add Google logo
                    height: 24,
                    width: 24,
                  ),
                  label: Text('Sign in with Google'),
                  style: ElevatedButton.styleFrom(
                    minimumSize: Size(double.infinity, 50),
                    backgroundColor: Colors.white,
                    foregroundColor: Colors.black87,
                    side: BorderSide(color: Colors.grey.shade300),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _handleGoogleSignIn() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final result = await _authService.signInWithGoogle();
      
      if (result != null) {
        // Login berhasil, navigasi ke home
        Navigator.pushReplacementNamed(context, '/home');
      }
    } catch (error) {
      // Tampilkan error
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Login failed: $error'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
}
```

### 4. Buat Auth State Management
```dart
// lib/providers/auth_provider.dart
import 'package:flutter/foundation.dart';
import '../services/google_auth_service.dart';

class AuthProvider with ChangeNotifier {
  final GoogleAuthService _authService = GoogleAuthService();
  
  bool _isAuthenticated = false;
  Map<String, dynamic>? _user;
  bool _isLoading = true;

  bool get isAuthenticated => _isAuthenticated;
  Map<String, dynamic>? get user => _user;
  bool get isLoading => _isLoading;

  AuthProvider() {
    _checkAuthStatus();
  }

  Future<void> _checkAuthStatus() async {
    try {
      _isAuthenticated = await _authService.isSignedIn();
      if (_isAuthenticated) {
        // Load user data jika perlu
      }
    } catch (error) {
      print('Auth check error: $error');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> signIn() async {
    try {
      final result = await _authService.signInWithGoogle();
      if (result != null) {
        _isAuthenticated = true;
        _user = result['data']['user'];
        notifyListeners();
        return true;
      }
      return false;
    } catch (error) {
      print('Sign in error: $error');
      return false;
    }
  }

  Future<void> signOut() async {
    await _authService.signOut();
    _isAuthenticated = false;
    _user = null;
    notifyListeners();
  }
}
```

### 5. Setup Main App
```dart
// lib/main.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (context) => AuthProvider(),
      child: MaterialApp(
        title: 'LogGenerator',
        theme: ThemeData(
          primarySwatch: Colors.blue,
        ),
        home: AuthWrapper(),
        routes: {
          '/login': (context) => LoginScreen(),
          '/home': (context) => HomeScreen(),
        },
      ),
    );
  }
}

class AuthWrapper extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        if (authProvider.isLoading) {
          return Scaffold(
            body: Center(
              child: CircularProgressIndicator(),
            ),
          );
        }
        
        return authProvider.isAuthenticated 
            ? HomeScreen() 
            : LoginScreen();
      },
    );
  }
}
```

## 🧪 Testing

### 1. Test di Development
1. Akses `http://localhost/test_google_auth.html`
2. Dapatkan ID token dari mobile app
3. Test authentication melalui web interface

### 2. Test di Mobile
1. Build dan jalankan aplikasi Flutter
2. Tap tombol "Sign in with Google"
3. Pilih akun Google
4. Verifikasi login berhasil

### 3. Test API Langsung
```bash
curl -X POST "https://your-domain.com/api/auth/google" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{
       "id_token": "YOUR_GOOGLE_ID_TOKEN",
       "device_name": "Test Device"
     }'
```

## 🐛 Troubleshooting

### Error: "Invalid ID token"
- **Penyebab**: ID token expired atau invalid
- **Solusi**: Pastikan menggunakan ID token yang fresh dari Google Sign-In

### Error: "SHA1 fingerprint mismatch"
- **Penyebab**: SHA1 fingerprint tidak match dengan Google Console
- **Solusi**: 
  1. Generate SHA1 fingerprint yang benar
  2. Update di Google Console OAuth client
  3. Tunggu beberapa menit untuk propagasi

### Error: "Client ID mismatch"
- **Penyebab**: Client ID tidak sesuai platform
- **Solusi**: Pastikan menggunakan client ID yang tepat untuk setiap platform

### Error: "REVERSE_CLIENT_ID not found" (iOS)
- **Penyebab**: URL scheme tidak dikonfigurasi dengan benar
- **Solusi**: Periksa `Info.plist` dan pastikan REVERSED_CLIENT_ID benar

### Error: "API not enabled"
- **Penyebab**: Google+ API atau Google Sign-In API belum diaktifkan
- **Solusi**: Aktifkan API yang diperlukan di Google Cloud Console

## 📚 Resource Tambahan

- [Google Sign-In Flutter Documentation](https://pub.dev/packages/google_sign_in)
- [Google Identity Documentation](https://developers.google.com/identity)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

## 🔒 Security Notes

1. **Jangan** hardcode Client ID/Secret di source code
2. Gunakan environment variables untuk sensitive data
3. Validasi ID token di server-side (sudah diimplementasi)
4. Implement proper token refresh mechanism
5. Log authentication attempts untuk monitoring

---

**Catatan**: Pastikan semua konfigurasi sudah sesuai sebelum deployment ke production. Test thoroughly di development environment terlebih dahulu.