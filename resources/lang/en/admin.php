<?php

return [
    'sidebar' => [
        'title' => 'Admin Panel',
        'dashboard' => 'Dashboard',
        'user_management' => 'User Management',
        'logbook_management' => 'Logbook Management',
        'institution_management' => 'Institution Management',
        'role_permission' => 'Role & Permission',
        'reports_analytics' => 'Reports & Analytics',
        'back_to_website' => 'Back to Website',
        'badges' => [
            'dev' => 'Dev',
            'new' => 'New',
        ],
    ],
    
    'topbar' => [
        'welcome' => 'Welcome to administrator panel',
        'refresh' => 'Refresh Dashboard',
        'notifications' => 'Notifications',
        'logout' => 'Logout',
        'logout_confirm' => 'Are you sure you want to logout?',
        'administrator' => 'Administrator',
        'loading' => 'Loading...',
    ],
    
    'dashboard' => [
        'title' => 'Dashboard',
        'description' => 'System statistics and monitoring',
        'loading' => 'Loading dashboard data...',
        'stats' => [
            'total_users' => 'Total Users',
            'logbook_templates' => 'Logbook Templates',
            'logbook_entries' => 'Logbook Entries',
            'audit_logs' => 'Audit Logs',
        ],
        'charts' => [
            'user_registrations' => 'User Registrations (Last 30 Days)',
            'logbook_activity' => 'Logbook Activity (Last 30 Days)',
        ],
        'recent_activity' => [
            'title' => 'Recent Activity',
            'loading' => 'Loading recent activity...',
        ],
        'refresh_success' => 'Dashboard refreshed!',
        'load_failed' => 'Failed to load dashboard data. Please try again.',
    ],
    
    'user' => [
        'roles' => [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'user' => 'User',
        ],
    ],
];
