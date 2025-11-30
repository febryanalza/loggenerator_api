<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    
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
    
    <style>
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .floating-animation {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .pulse-slow {
            animation: pulse 4s ease-in-out infinite;
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="font-inter login-bg min-h-screen flex items-center justify-center p-4">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-32 w-80 h-80 bg-white opacity-10 rounded-full floating-animation"></div>
        <div class="absolute -bottom-32 -left-40 w-96 h-96 bg-white opacity-5 rounded-full pulse-slow"></div>
        <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-white opacity-5 rounded-full floating-animation" style="animation-delay: -1s;"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8 fade-in">
            <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                <i class="fas fa-shield-alt text-3xl text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Admin Dashboard</h1>
            <p class="text-indigo-200">Log Generator API Management</p>
        </div>

        <!-- Login Form -->
        <div class="glass-effect rounded-2xl shadow-2xl p-8 fade-in">
            <form id="adminLoginForm" class="space-y-6">
                <!-- Alert Container -->
                <div id="alert-container" class="hidden"></div>
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        value="superadmin@loggenerator.com"
                        required
                        class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-indigo-200 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent transition duration-200"
                        placeholder="admin@loggenerator.com"
                        autocomplete="email"
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            value="password"
                            required
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-indigo-200 focus:outline-none focus:ring-2 focus:ring-white focus:border-transparent transition duration-200 pr-12"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-indigo-200 hover:text-white transition duration-200"
                        >
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox"
                            class="h-4 w-4 text-indigo-600 focus:ring-white border-white border-opacity-30 rounded bg-white bg-opacity-20"
                        >
                        <label for="remember" class="ml-2 block text-sm text-indigo-200">
                            Remember me
                        </label>
                    </div>
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    id="loginButton"
                    class="w-full bg-white text-indigo-600 py-3 px-4 rounded-lg font-semibold hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent transition duration-200 transform hover:scale-105"
                >
                    <span id="loginButtonText">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Dashboard
                    </span>
                    <span id="loginButtonLoading" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Signing In...
                    </span>
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-6 text-center">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white border-opacity-30"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-transparent text-indigo-200">Authorized Personnel Only</span>
                    </div>
                </div>
            </div>

            <!-- Role Information -->
            <div class="mt-6 text-center text-xs text-indigo-200">
                <p class="mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Access restricted to administrators only
                </p>
                <div class="flex justify-center space-x-2 text-xs">
                    <span class="px-2 py-1 bg-white bg-opacity-10 rounded-full">Admin</span>
                    <span class="px-2 py-1 bg-white bg-opacity-10 rounded-full">Super Admin</span>
                    <span class="px-2 py-1 bg-white bg-opacity-10 rounded-full">Manager</span>
                    <span class="px-2 py-1 bg-white bg-opacity-10 rounded-full">Institution Admin</span>
                </div>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-indigo-200 hover:text-white transition duration-200 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Back to Homepage
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('adminLoginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');
            const loginButton = document.getElementById('loginButton');
            const loginButtonText = document.getElementById('loginButtonText');
            const loginButtonLoading = document.getElementById('loginButtonLoading');
            const alertContainer = document.getElementById('alert-container');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                const password = passwordInput.value;
                const remember = document.getElementById('remember').checked;
                
                if (!email || !password) {
                    showAlert('Please fill in all fields', 'error');
                    return;
                }

                // Show loading state
                setLoadingState(true);
                
                try {
                    console.log('Sending login request to /api/admin/login...');
                    
                    const response = await fetch('/api/admin/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            email: email,
                            password: password,
                            device_name: 'Admin Dashboard Web'
                        })
                    });

                    const data = await response.json();
                    
                    console.log('Login response:', {
                        status: response.status,
                        data: data
                    });

                    if (response.status === 401) {
                        showAlert(data.message || 'Invalid credentials', 'error');
                        setLoadingState(false);
                        return;
                    }

                    if (response.status === 403) {
                        showAlert(data.message || 'Access denied. Admin privileges required.', 'error');
                        setLoadingState(false);
                        return;
                    }

                    if (data.success && data.data && data.data.token) {
                        // Store Bearer Token in localStorage
                        localStorage.setItem('admin_token', data.data.token);
                        localStorage.setItem('admin_user', JSON.stringify(data.data.user));
                        
                        console.log('Bearer Token stored successfully');
                        
                        // Check user role and redirect accordingly
                        const userRoles = data.data.user.roles || [];
                        const roleNames = userRoles.map(r => r.name || r);
                        
                        showAlert('Login successful! Redirecting...', 'success');
                        
                        setTimeout(() => {
                            // Redirect based on role
                            if (roleNames.includes('Institution Admin') && 
                                !roleNames.includes('Super Admin') && 
                                !roleNames.includes('Admin') && 
                                !roleNames.includes('Manager')) {
                                // Institution Admin goes to institution admin dashboard
                                window.location.href = '/institution-admin';
                            } else {
                                // Super Admin, Admin, Manager goes to admin dashboard
                                window.location.href = '/admin';
                            }
                        }, 1000);
                    } else {
                        showAlert(data.message || 'Login failed. Please try again.', 'error');
                        setLoadingState(false);
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                    setLoadingState(false);
                }
            });

            function setLoadingState(loading) {
                loginButton.disabled = loading;
                
                if (loading) {
                    loginButtonText.classList.add('hidden');
                    loginButtonLoading.classList.remove('hidden');
                    loginButton.classList.add('opacity-75');
                } else {
                    loginButtonText.classList.remove('hidden');
                    loginButtonLoading.classList.add('hidden');
                    loginButton.classList.remove('opacity-75');
                }
            }

            function showAlert(message, type) {
                const alertClass = type === 'error' 
                    ? 'bg-red-100 border border-red-400 text-red-700' 
                    : 'bg-green-100 border border-green-400 text-green-700';
                
                const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
                
                alertContainer.innerHTML = `
                    <div class="${alertClass} px-4 py-3 rounded-lg mb-4">
                        <div class="flex items-center">
                            <i class="fas ${icon} mr-2"></i>
                            <span>${message}</span>
                        </div>
                    </div>
                `;
                
                alertContainer.classList.remove('hidden');
                
                // Auto hide after 5 seconds for success messages
                if (type === 'success') {
                    setTimeout(() => {
                        alertContainer.classList.add('hidden');
                    }, 5000);
                }
            }

            // Focus on first input
            emailInput.focus();
        });
    </script>
</body>
</html>
