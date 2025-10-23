<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard Access - {{ config('app.name') }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-700 min-h-screen flex items-center justify-center">
    
    <div class="w-full max-w-md">
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 border border-white/20 text-center">
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                    <i class="fas fa-key text-2xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Admin Access Required</h1>
                <p class="text-white/80">Bearer Token Authentication Needed</p>
            </div>

            <div id="tokenStatus" class="mb-6"></div>

            <div class="space-y-4">
                <button onclick="checkToken()" 
                        class="w-full bg-white/20 text-white border border-white/30 py-3 px-4 rounded-lg font-semibold hover:bg-white/30 transition duration-200">
                    <i class="fas fa-search mr-2"></i>Check Token Status
                </button>
                
                <button onclick="goToLogin()" 
                        class="w-full bg-white text-indigo-600 py-3 px-4 rounded-lg font-semibold hover:bg-opacity-90 transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Go to Admin Login
                </button>
                
                <button onclick="goToDashboard()" id="dashboardBtn" 
                        class="w-full bg-green-500 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-600 transition duration-200 hidden">
                    <i class="fas fa-tachometer-alt mr-2"></i>Enter Dashboard
                </button>
            </div>

            <div class="mt-6 p-4 bg-white/5 rounded-lg">
                <div class="text-xs text-white/70 text-center">
                    This dashboard requires Bearer Token authentication.<br>
                    Please login first to get your access token.
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkToken() {
            const token = localStorage.getItem('admin_token');
            const user = localStorage.getItem('admin_user');
            const statusDiv = document.getElementById('tokenStatus');
            const dashboardBtn = document.getElementById('dashboardBtn');
            
            if (token && user) {
                try {
                    const userData = JSON.parse(user);
                    statusDiv.innerHTML = `
                        <div class="bg-green-500/20 border border-green-500/30 text-green-100 px-4 py-3 rounded-lg">
                            <div class="flex items-center space-x-2 mb-2">
                                <i class="fas fa-check-circle"></i>
                                <span class="font-semibold">Token Found</span>
                            </div>
                            <div class="text-sm space-y-1">
                                <div><strong>User:</strong> ${userData.name}</div>
                                <div><strong>Email:</strong> ${userData.email}</div>
                                <div><strong>Roles:</strong> ${userData.roles ? userData.roles.join(', ') : 'N/A'}</div>
                                <div><strong>Token:</strong> ${token.substring(0, 20)}...</div>
                            </div>
                        </div>
                    `;
                    dashboardBtn.classList.remove('hidden');
                } catch (e) {
                    statusDiv.innerHTML = `
                        <div class="bg-red-500/20 border border-red-500/30 text-red-100 px-4 py-3 rounded-lg">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Invalid token data. Please login again.
                        </div>
                    `;
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                }
            } else {
                statusDiv.innerHTML = `
                    <div class="bg-yellow-500/20 border border-yellow-500/30 text-yellow-100 px-4 py-3 rounded-lg">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No Bearer token found. Please login first.
                    </div>
                `;
                dashboardBtn.classList.add('hidden');
            }
        }

        function goToLogin() {
            window.location.href = '/admin/login-bearer';
        }

        function goToDashboard() {
            const token = localStorage.getItem('admin_token');
            if (token) {
                // Redirect with token in URL hash for the dashboard to pick up
                window.location.href = '/admin/dashboard-app';
            } else {
                alert('No token found. Please login first.');
                goToLogin();
            }
        }

        // Auto-check token on page load
        window.addEventListener('DOMContentLoaded', checkToken);
    </script>
</body>
</html>