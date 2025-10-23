<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $companyData['name'] }} - {{ $companyData['tagline'] }}</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $companyData['description'] }}">
    <meta name="keywords" content="logbook, digital logbook, mobile app, productivity, data management">
    <meta name="author" content="{{ $companyData['name'] }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
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
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .app-store-btn {
            transition: all 0.3s ease;
        }
        
        .app-store-btn:hover {
            transform: scale(1.05);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-delay-100 {
            animation-delay: 0.1s;
        }
        
        .animate-delay-200 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-300 {
            animation-delay: 0.3s;
        }
    </style>
</head>

<body class="font-inter bg-gray-50 text-gray-900">
    
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-indigo-600">{{ $companyData['name'] }}</h1>
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-gray-900 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors">Home</a>
                    <a href="#features" class="text-gray-900 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors">Features</a>
                    <a href="#download" class="text-gray-900 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors">Download</a>
                    <a href="#contact" class="text-gray-900 hover:text-indigo-600 px-3 py-2 text-sm font-medium transition-colors">Contact</a>
                    
                    <!-- Admin Dashboard Link -->
                    @if(config('company.admin_dashboard_enabled'))
                        <a href="{{ config('company.admin_dashboard_url') }}" 
                           class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                            <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Admin Dashboard
                        </a>
                    @else
                        <button class="bg-gray-300 text-gray-500 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-not-allowed" 
                                title="Dashboard Admin belum tersedia">
                            <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Admin Dashboard
                        </button>
                    @endif
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button type="button" id="mobile-menu-btn" class="text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t">
                <a href="#home" class="text-gray-900 hover:text-indigo-600 block px-3 py-2 text-base font-medium">Home</a>
                <a href="#features" class="text-gray-900 hover:text-indigo-600 block px-3 py-2 text-base font-medium">Features</a>
                <a href="#download" class="text-gray-900 hover:text-indigo-600 block px-3 py-2 text-base font-medium">Download</a>
                <a href="#contact" class="text-gray-900 hover:text-indigo-600 block px-3 py-2 text-base font-medium">Contact</a>
                @if(config('company.admin_dashboard_enabled'))
                    <a href="{{ config('company.admin_dashboard_url') }}" 
                       class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white block px-3 py-2 text-base font-semibold rounded-md mx-3 my-2 text-center">
                        <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Admin Dashboard
                    </a>
                @else
                    <span class="text-gray-400 block px-3 py-2 text-base font-medium cursor-not-allowed">
                        Admin Dashboard (Coming Soon)
                    </span>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg pt-20 pb-20 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 animate-fade-in-up">
                    {{ $companyData['name'] }}
                </h1>
                <p class="text-xl md:text-2xl text-indigo-100 mb-4 animate-fade-in-up animate-delay-100">
                    {{ $companyData['tagline'] }}
                </p>
                <p class="text-lg text-indigo-200 max-w-3xl mx-auto mb-8 animate-fade-in-up animate-delay-200">
                    {{ $companyData['description'] }}
                </p>
                
                <!-- App Store Buttons -->
                <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6 animate-fade-in-up animate-delay-300">
                    <a href="{{ $companyData['app_links']['android'] }}" target="_blank" class="app-store-btn block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                             alt="Get it on Google Play" class="h-14">
                    </a>
                    <a href="{{ $companyData['app_links']['ios'] }}" target="_blank" class="app-store-btn block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" 
                             alt="Download on the App Store" class="h-14">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Real-time Update Indicator -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                    Live Data - Updated: {{ $companyData['real_time_stats']['last_updated'] }}
                </div>
            </div>
            
            <!-- Main Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center mb-12">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-6 rounded-lg">
                    <div class="text-3xl md:text-4xl font-bold text-indigo-600 mb-2 users-stat">{{ $companyData['stats']['users'] }}+</div>
                    <div class="text-gray-700 font-medium">Total Users</div>
                    <div class="text-sm text-indigo-500 mt-1">{{ $companyData['real_time_stats']['active_users_today'] }} active today</div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-lg">
                    <div class="text-3xl md:text-4xl font-bold text-emerald-600 mb-2 logbooks-stat">{{ $companyData['stats']['logbooks'] }}+</div>
                    <div class="text-gray-700 font-medium">Logbook Templates</div>
                    <div class="text-sm text-emerald-500 mt-1">{{ $companyData['real_time_stats']['templates_this_month'] }} this month</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-violet-100 p-6 rounded-lg">
                    <div class="text-3xl md:text-4xl font-bold text-violet-600 mb-2 entries-stat">{{ $companyData['stats']['entries'] }}+</div>
                    <div class="text-gray-700 font-medium">Data Entries</div>
                    <div class="text-sm text-violet-500 mt-1">{{ $companyData['real_time_stats']['entries_this_week'] }} this week</div>
                </div>
                <div class="bg-gradient-to-br from-orange-50 to-amber-100 p-6 rounded-lg">
                    <div class="text-3xl md:text-4xl font-bold text-amber-600 mb-2">{{ $companyData['stats']['uptime'] }}</div>
                    <div class="text-gray-700 font-medium">System Uptime</div>
                    <div class="text-sm text-amber-500 mt-1">Always reliable</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Powerful Features for Modern Teams
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Discover the tools and features that make LogGenerator the perfect choice for your logbook management needs
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($companyData['features'] as $index => $feature)
                <div class="feature-card bg-white p-8 rounded-lg shadow-md">
                    <div class="text-4xl mb-4">{{ $feature['icon'] }}</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Download Section -->
    <section id="download" class="py-20 bg-indigo-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Ready to Get Started?
            </h2>
            <p class="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
                Download our mobile app today and transform the way you manage your logbooks
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-8">
                <a href="{{ $companyData['app_links']['android'] }}" target="_blank" class="app-store-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                         alt="Get it on Google Play" class="h-16">
                </a>
                <a href="{{ $companyData['app_links']['ios'] }}" target="_blank" class="app-store-btn">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" 
                         alt="Download on the App Store" class="h-16">
                </a>
            </div>
        </div>
    </section>

    <!-- Admin CTA Section -->
    @if(config('company.admin_dashboard_enabled'))
    <section class="py-16 bg-gradient-to-r from-purple-900 via-indigo-900 to-blue-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
                <div class="flex justify-center mb-4">
                    <div class="bg-white/20 rounded-full p-4">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-3">
                    Administrator Access
                </h3>
                <p class="text-lg text-indigo-100 mb-6 max-w-2xl mx-auto">
                    Manage your system, monitor activities, and control all aspects of your logbook platform
                </p>
                <a href="{{ config('company.admin_dashboard_url') }}" 
                   class="inline-flex items-center bg-white text-indigo-900 px-8 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition-all duration-200 shadow-xl hover:shadow-2xl transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Enter Admin Dashboard
                </a>
                <p class="text-sm text-indigo-200 mt-4">
                    🔒 Secure login required • Protected access
                </p>
            </div>
        </div>
    </section>
    @endif

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Get In Touch
                </h2>
                <p class="text-xl text-gray-600">
                    Have questions? We'd love to hear from you
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Email</h3>
                    <p class="text-gray-600">{{ $companyData['contact']['email'] }}</p>
                </div>
                
                <div>
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Phone</h3>
                    <p class="text-gray-600">{{ $companyData['contact']['phone'] }}</p>
                </div>
                
                <div>
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Location</h3>
                    <p class="text-gray-600">{{ $companyData['contact']['address'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4">{{ $companyData['name'] }}</h3>
                    <p class="text-gray-400 mb-4 max-w-md">
                        {{ $companyData['description'] }}
                    </p>
                    <div class="flex space-x-4">
                        <a href="{{ $companyData['app_links']['android'] }}" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592c.1087-.1888.0353-.4296-.1535-.5383-.1888-.1087-.4296-.0353-.5383.1535L17.2641 8.958c-.8566-.4239-1.8255-.6575-2.8496-.6575s-1.993.2336-2.8496.6575L9.6166 5.4154c-.1087-.1888-.3495-.2622-.5383-.1535-.1888.1087-.2622.3495-.1535.5383L10.9221 9.32C8.8815 10.7767 7.6454 13.2177 7.6454 15.964H16.3546C16.3546 13.2177 15.1185 10.7767 13.0779 9.32"/>
                            </svg>
                        </a>
                        <a href="{{ $companyData['app_links']['ios'] }}" target="_blank" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#download" class="text-gray-400 hover:text-white transition-colors">Download</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    © {{ date('Y') }} {{ $companyData['name'] }}. All rights reserved. Built with Laravel & ❤️
                </p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        // Close mobile menu if open
                        if (mobileMenu) {
                            mobileMenu.classList.add('hidden');
                        }
                    }
                });
            });

            // Real-time data update function
            async function updateRealTimeData() {
                try {
                    const response = await fetch('/api/website/homepage-data');
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update statistics with animation
                        const stats = data.data.stats;
                        updateStatWithAnimation('.users-stat', stats.users);
                        updateStatWithAnimation('.logbooks-stat', stats.logbooks);
                        updateStatWithAnimation('.entries-stat', stats.entries);
                        
                        // Update last updated time
                        const lastUpdated = new Date(data.data.system_info.last_updated);
                        const formattedTime = lastUpdated.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                        
                        const updateIndicator = document.querySelector('.bg-green-100');
                        if (updateIndicator) {
                            updateIndicator.innerHTML = `
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                Live Data - Updated: ${formattedTime}
                            `;
                        }
                    }
                } catch (error) {
                    console.log('Real-time update temporarily unavailable');
                }
            }

            function updateStatWithAnimation(selector, newValue) {
                const element = document.querySelector(selector);
                if (element && element.textContent !== newValue) {
                    element.style.transform = 'scale(1.1)';
                    element.style.transition = 'transform 0.3s ease';
                    
                    setTimeout(() => {
                        element.textContent = newValue;
                        element.style.transform = 'scale(1)';
                    }, 150);
                }
            }

            // Update data every 30 seconds
            setInterval(updateRealTimeData, 30000);

            // Simple scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe feature cards for scroll animations
            document.querySelectorAll('.feature-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>