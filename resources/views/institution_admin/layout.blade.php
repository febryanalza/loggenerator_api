<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Institution Admin') - {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin Token Manager -->
    <script src="{{ asset('js/admin-token-manager.js') }}"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'institution': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
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
            background-color: rgba(22, 163, 74, 0.1);
            border-left: 4px solid #16a34a;
        }
        
        .sidebar-item.active {
            background-color: rgba(22, 163, 74, 0.15);
            border-left: 4px solid #16a34a;
            color: #16a34a;
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
    <button id="sidebarToggle" class="sidebar-toggle fixed top-4 left-4 z-50 bg-green-600 text-white p-2 rounded-lg shadow-lg md:hidden">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full w-64 bg-white shadow-xl z-40">
        <!-- Logo/Header -->
        <div class="flex items-center justify-between p-6 border-b">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-white text-sm"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Institution Admin</h1>
                    <p class="text-xs text-gray-500">{{ config('app.name') }}</p>
                </div>
            </div>
            <button id="closeSidebar" class="md:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="mt-6">
            <a href="{{ route('institution-admin.dashboard') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 {{ request()->routeIs('institution-admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="{{ route('institution-admin.logbooks') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 {{ request()->routeIs('institution-admin.logbooks*') ? 'active' : '' }}">
                <i class="fas fa-book w-5 h-5 mr-3"></i>
                <span class="font-medium">Kelola Logbook</span>
            </a>
            
            <a href="{{ route('institution-admin.members') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 {{ request()->routeIs('institution-admin.members*') ? 'active' : '' }}">
                <i class="fas fa-users w-5 h-5 mr-3"></i>
                <span class="font-medium">Anggota Institusi</span>
            </a>
            
            <a href="{{ route('institution-admin.reports') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 {{ request()->routeIs('institution-admin.reports*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                <span class="font-medium">Laporan & Statistik</span>
            </a>
            
            <a href="{{ route('institution-admin.settings') }}" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-green-50 {{ request()->routeIs('institution-admin.settings*') ? 'active' : '' }}">
                <i class="fas fa-cog w-5 h-5 mr-3"></i>
                <span class="font-medium">Pengaturan</span>
            </a>
        </nav>

        <!-- Institution Info -->
        <div class="absolute bottom-0 w-full p-6 border-t bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-tie text-white text-sm"></i>
                </div>
                <div class="flex-1" id="sidebarUserInfo">
                    <p class="text-sm font-medium text-gray-800">Loading...</p>
                    <p class="text-xs text-gray-500">Institution Admin</p>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-sm text-green-600 hover:text-green-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <button onclick="handleLogout()" class="text-sm text-red-500 hover:text-red-700 flex items-center">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Logout
                </button>
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
                    <p class="text-sm text-gray-600 mt-1">@yield('page-description', 'Selamat datang di panel Institution Admin')</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Refresh Button -->
                    <button id="refresh-page" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors" title="Refresh">
                        <i class="fas fa-sync-alt text-lg"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationBtn" class="text-gray-500 hover:text-gray-700 relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span id="notificationBadge" class="notification-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </button>
                    </div>
                    
                    <!-- User Profile -->
                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-600 text-right" id="currentUserInfo">
                            <div class="font-medium">Loading...</div>
                            <div class="text-xs text-gray-400">Institution Admin</div>
                        </div>
                    </div>
                    
                    <!-- Current Time -->
                    <div class="text-sm text-gray-600">
                        <div id="currentTime"></div>
                        <div class="text-xs text-gray-400" id="sessionTimeRemaining">{{ now()->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <div class="bg-gray-100 px-6 py-2 border-b">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('institution-admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <!-- Page Content -->
        <div class="p-6">
            @yield('content')
        </div>
    </div>

    <!-- Overlay for mobile -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <!-- Alert Modal -->
    <div id="alertModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4">
            <div class="text-center">
                <div id="alertIcon" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h3 id="alertTitle" class="text-xl font-bold text-gray-800 mb-2">Alert</h3>
                <p id="alertMessage" class="text-gray-600 mb-6">Message</p>
                <button onclick="closeAlert()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        // Load user info from localStorage
        function loadUserInfo() {
            const userData = localStorage.getItem('admin_user');
            if (userData) {
                try {
                    const user = JSON.parse(userData);
                    
                    // Update top bar user info
                    const userInfoDiv = document.getElementById('currentUserInfo');
                    if (userInfoDiv && user.name) {
                        userInfoDiv.innerHTML = `
                            <div class="font-medium">${user.name}</div>
                            <div class="text-xs text-gray-400">Institution Admin</div>
                        `;
                    }
                    
                    // Update sidebar user info
                    const sidebarUserInfo = document.getElementById('sidebarUserInfo');
                    if (sidebarUserInfo && user.name) {
                        const institutionName = user.institution?.name || 'Institusi';
                        sidebarUserInfo.innerHTML = `
                            <p class="text-sm font-medium text-gray-800">${user.name}</p>
                            <p class="text-xs text-gray-500">${institutionName}</p>
                        `;
                    }
                } catch (e) {
                    console.error('Error parsing user data:', e);
                }
            }
        }
        
        loadUserInfo();

        // Logout handler using AdminTokenManager
        function handleLogout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                AdminTokenManager.logout();
            }
        }
        
        // Legacy function kept for compatibility
        function clearSessionAndRedirect() {
            AdminTokenManager.redirectToLogin();
        }

        // Alert functions
        function showAlert(type, title, message) {
            const modal = document.getElementById('alertModal');
            const iconDiv = document.getElementById('alertIcon');
            const titleEl = document.getElementById('alertTitle');
            const messageEl = document.getElementById('alertMessage');
            
            // Set icon and colors based on type
            const configs = {
                success: { bg: 'bg-green-100', text: 'text-green-500', icon: 'fa-check' },
                error: { bg: 'bg-red-100', text: 'text-red-500', icon: 'fa-times' },
                warning: { bg: 'bg-yellow-100', text: 'text-yellow-500', icon: 'fa-exclamation-triangle' },
                info: { bg: 'bg-blue-100', text: 'text-blue-500', icon: 'fa-info-circle' }
            };
            
            const config = configs[type] || configs.info;
            
            iconDiv.className = `w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4 ${config.bg}`;
            iconDiv.innerHTML = `<i class="fas ${config.icon} text-3xl ${config.text}"></i>`;
            titleEl.textContent = title;
            messageEl.textContent = message;
            
            modal.classList.remove('hidden');
        }
        
        function closeAlert() {
            document.getElementById('alertModal').classList.add('hidden');
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
        
        // Refresh button handler
        document.getElementById('refresh-page')?.addEventListener('click', function() {
            this.classList.add('animate-spin');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        // Auto-close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeSidebarFunc();
            }
        });
        
        // Token check is handled by AdminTokenManager automatically
    </script>

    @stack('scripts')
</body>
</html>
