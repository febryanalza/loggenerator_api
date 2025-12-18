@extends('admin.layout')

@section('title', __('admin.dashboard.title'))
@section('page-title', __('admin.dashboard.title'))
@section('page-description', __('admin.dashboard.description'))

@section('content')
<!-- Loading Indicator -->
<div id="dashboardLoading" class="text-center py-12">
    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
    <p class="text-gray-600 mt-4">{{ __('admin.dashboard.loading') }}</p>
</div>

<!-- Dashboard Content -->
<div id="dashboardContent" class="hidden">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ __('admin.dashboard.stats.total_users') }}</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="totalUsers">0</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ __('admin.dashboard.stats.logbook_templates') }}</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="totalTemplates">0</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-clipboard-list text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ __('admin.dashboard.stats.logbook_entries') }}</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="totalEntries">0</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-book text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500 stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ __('admin.dashboard.stats.audit_logs') }}</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2" id="totalAuditLogs">0</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 fade-in">
        <!-- User Registration Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                {{ __('admin.dashboard.charts.user_registrations') }}
            </h3>
            <div class="chart-container">
                <canvas id="userChart"></canvas>
            </div>
        </div>

        <!-- Logbook Activity Chart -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                {{ __('admin.dashboard.charts.logbook_activity') }}
            </h3>
            <div class="chart-container">
                <canvas id="logbookChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-md fade-in">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock text-orange-600 mr-2"></i>
                {{ __('admin.dashboard.recent_activity.title') }}
            </h3>
        </div>
        <div class="divide-y divide-gray-200" id="recentActivity">
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                {{ __('admin.dashboard.recent_activity.loading') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-dashboard.js') }}"></script>
<script>
const translations = {!! str_replace(["'"], ["\'"], json_encode(['loadFailed' => __('admin.dashboard.load_failed'), 'refreshSuccess' => __('admin.dashboard.refresh_success')])) !!};

let dashboard;

function checkAuthAndLoadDashboard() {
    const token = localStorage.getItem('admin_token');
    const user = localStorage.getItem('admin_user');
    
    if (!token || !user) {
        window.location.href = '/login';
        return;
    }
    
    try {
        const userData = JSON.parse(user);
        
        // Initialize dashboard with Bearer token
        dashboard = new AdminDashboard();
        dashboard.init().then(() => {
            document.getElementById('dashboardLoading').classList.add('hidden');
            document.getElementById('dashboardContent').classList.remove('hidden');
        }).catch(error => {
            console.error('Dashboard initialization failed:', error);
            alert(translations.loadFailed);
            window.location.href = '/login';
        });
        
    } catch (e) {
        console.error('Invalid user data:', e);
        window.location.href = '/login';
    }
}

// Refresh dashboard button
const refreshBtn = document.getElementById('refresh-dashboard');
if (refreshBtn) {
    refreshBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        
        if (dashboard) {
            dashboard.init().then(() => {
                icon.classList.remove('fa-spin');
                
                // Show success toast
                const toast = document.createElement('div');
                toast.className = 'fixed top-20 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + translations.refreshSuccess;
                document.body.appendChild(toast);
                
                setTimeout(() => toast.remove(), 3000);
            }).catch(() => {
                icon.classList.remove('fa-spin');
            });
        }
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', checkAuthAndLoadDashboard);
</script>
@endpush
