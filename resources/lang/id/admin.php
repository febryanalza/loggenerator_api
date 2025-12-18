<?php

return [
    'sidebar' => [
        'title' => 'Panel Admin',
        'dashboard' => 'Dashboard',
        'user_management' => 'Manajemen Pengguna',
        'logbook_management' => 'Manajemen Logbook',
        'institution_management' => 'Manajemen Institusi',
        'role_permission' => 'Role & Permission',
        'reports_analytics' => 'Laporan & Analitik',
        'back_to_website' => 'Kembali ke Website',
        'badges' => [
            'dev' => 'Dev',
            'new' => 'Baru',
        ],
    ],
    
    'topbar' => [
        'welcome' => 'Selamat datang di panel administrator',
        'refresh' => 'Refresh Dashboard',
        'notifications' => 'Notifikasi',
        'logout' => 'Keluar',
        'logout_confirm' => 'Apakah Anda yakin ingin keluar?',
        'administrator' => 'Administrator',
        'loading' => 'Memuat...',
    ],
    
    'dashboard' => [
        'title' => 'Dashboard',
        'description' => 'Statistik dan monitoring sistem',
        'loading' => 'Memuat data dashboard...',
        'stats' => [
            'total_users' => 'Total Pengguna',
            'logbook_templates' => 'Template Logbook',
            'logbook_entries' => 'Entri Logbook',
            'audit_logs' => 'Log Audit',
        ],
        'charts' => [
            'user_registrations' => 'Registrasi Pengguna (30 Hari Terakhir)',
            'logbook_activity' => 'Aktivitas Logbook (30 Hari Terakhir)',
        ],
        'recent_activity' => [
            'title' => 'Aktivitas Terkini',
            'loading' => 'Memuat aktivitas terkini...',
        ],
        'refresh_success' => 'Dashboard berhasil di-refresh!',
        'load_failed' => 'Gagal memuat data dashboard. Silakan coba lagi.',
    ],
    
    'user' => [
        'roles' => [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manajer',
            'user' => 'Pengguna',
        ],
    ],
];
