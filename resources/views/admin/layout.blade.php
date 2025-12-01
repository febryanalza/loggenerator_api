<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar-item {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            background-color: rgba(79, 70, 229, 0.1);
            border-left: 4px solid #4f46e5;
        }
        
        .sidebar-item.active {
            background-color: rgba(79, 70, 229, 0.15);
            border-left: 4px solid #4f46e5;
            color: #4f46e5;
        }
        
        .stats-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .notification-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .sidebar-toggle {
            display: none;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body class="font-inter bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <button id="sidebarToggle" class="sidebar-toggle fixed top-4 left-4 z-50 bg-indigo-600 text-white p-2 rounded-lg shadow-lg md:hidden">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-64 bg-white shadow-xl z-40">
        <!-- Logo/Header -->
        <div class="flex items-center justify-between p-6 border-b">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-sm"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Admin Panel</h1>
                    <p class="text-xs text-gray-500">{{ config('app.name') }}</p>
                </div>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="mt-6">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="{{ route('admin.user-management') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 {{ request()->routeIs('admin.user-management') ? 'active' : '' }}">
                <i class="fas fa-users w-5 h-5 mr-3"></i>
                <span class="font-medium">Manajemen Pengguna</span>
                <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Dev</span>
            </a>
            
            <a href="{{ route('admin.logbook-management') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 {{ request()->routeIs('admin.logbook-management') ? 'active' : '' }}">
                <i class="fas fa-book w-5 h-5 mr-3"></i>
                <span class="font-medium">Manajemen Logbook</span>
                <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Dev</span>
            </a>
            
            <a href="{{ route('admin.institution-management') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 {{ request()->routeIs('admin.institution-management') ? 'active' : '' }}">
                <i class="fas fa-file-alt w-5 h-5 mr-3"></i>
                <span class="font-medium">Manajemen Institution</span>
                <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Dev</span>
            </a>
            
            <a href="{{ route('admin.role-permission-manager') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 {{ request()->routeIs('admin.role-permission-manager') ? 'active' : '' }}">
                <i class="fas fa-user-shield w-5 h-5 mr-3"></i>
                <span class="font-medium">Role & Permission</span>
                <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">New</span>
            </a>
            
            <a href="{{ route('admin.reports-analytics') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 {{ request()->routeIs('admin.reports-analytics') ? 'active' : '' }}">
                <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                <span class="font-medium">Reports & Analytics</span>
                <span class="ml-auto bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">New</span>
            </a>
        </nav>

        <!-- User Info -->
        <div class="absolute bottom-0 w-full p-6 border-t bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-shield text-white text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">Administrator</p>
                    <p class="text-xs text-gray-500">Super Admin</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Website
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content ml-64 min-h-screen">
        <!-- Top Bar -->
        <div class="bg-white shadow-sm border-b px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                    <p class="text-sm text-gray-600 mt-1">@yield('page-description', 'Selamat datang di panel administrator')</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Refresh Button -->
                    @if(request()->routeIs('admin.dashboard*'))
                    <button id="refresh-dashboard" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors" title="Refresh Dashboard">
                        <i class="fas fa-sync-alt text-lg"></i>
                    </button>
                    @endif
                    
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="text-gray-500 hover:text-gray-700 relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="notification-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </button>
                    </div>
                    
                    <!-- User Profile & Logout -->
                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-600 text-right" id="currentUserInfo">
                            <div class="font-medium">Loading...</div>
                            <div class="text-xs text-gray-400">Administrator</div>
                        </div>
                        <button onclick="handleLogout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </div>
                    
                    <!-- Current Time -->
                    <div class="text-sm text-gray-600">
                        <div id="currentTime"></div>
                        <div class="text-xs text-gray-400">{{ now()->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="p-6">
            @yield('content')
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <script>
        // Load user info from localStorage
        const userData = localStorage.getItem('admin_user');
        if (userData) {
            try {
                const user = JSON.parse(userData);
                const userInfoDiv = document.getElementById('currentUserInfo');
                if (userInfoDiv && user.name) {
                    userInfoDiv.innerHTML = `
                        <div class="font-medium">${user.name}</div>
                        <div class="text-xs text-gray-400">Administrator</div>
                    `;
                }
            } catch (e) {
                console.error('Error parsing user data:', e);
            }
        }

        // Logout handler with API call
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                const token = localStorage.getItem('admin_token');
                
                if (token) {
                    // Call logout API with Bearer token
                    fetch('/api/admin/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).finally(() => {
                        // Clear admin session
                        localStorage.removeItem('admin_token');
                        localStorage.removeItem('admin_user');
                        
                        // Redirect to homepage (welcome page)
                        window.location.href = '/';
                    });
                } else {
                    // Clear any remaining data
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                    
                    // Redirect to homepage (welcome page)
                    window.location.href = '/';
                }
            }
        }
    
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('overlay');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebarFunc() {
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        sidebarToggle?.addEventListener('click', openSidebar);
        closeSidebar?.addEventListener('click', closeSidebarFunc);
        overlay?.addEventListener('click', closeSidebarFunc);

        // Current time update
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        setInterval(updateTime, 1000);
        updateTime(); // Initial call

        // Auto-close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeSidebarFunc();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>