@extends('institution_admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat datang di panel Institution Admin')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Logbook</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-total-logbooks">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Entri</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-total-entries">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Template Aktif</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-active-templates">0</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-layer-group text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Pengguna</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-total-users">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Selamat Datang, <span id="welcome-name">Institution Admin</span>!</h2>
                <p class="text-indigo-100">Kelola logbook dan template untuk institusi Anda</p>
                <p class="text-indigo-200 text-sm mt-2">
                    <i class="fas fa-building mr-2"></i>
                    <span id="institution-name">Loading...</span>
                </p>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-building text-6xl text-white opacity-20"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('institution-admin.templates') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition duration-200 group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition duration-200">
                    <i class="fas fa-layer-group text-indigo-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Kelola Template</h3>
                    <p class="text-sm text-gray-500">Buat dan kelola template logbook</p>
                </div>
            </div>
        </a>

        <a href="{{ route('institution-admin.logbooks') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition duration-200 group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition duration-200">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Lihat Logbook</h3>
                    <p class="text-sm text-gray-500">Pantau aktivitas logbook</p>
                </div>
            </div>
        </a>

        <a href="{{ route('institution-admin.members') }}" class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition duration-200 group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition duration-200">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="font-semibold text-gray-800">Anggota Institusi</h3>
                    <p class="text-sm text-gray-500">Kelola anggota di institusi Anda</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock mr-2 text-gray-400"></i>
                Aktivitas Terbaru
            </h3>
        </div>
        <div class="p-6">
            <div id="recent-activity" class="space-y-4">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Memuat aktivitas...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('admin_token');
    const user = localStorage.getItem('admin_user');
    
    if (!token || !user) {
        window.location.href = '/login';
        return;
    }

    const userData = JSON.parse(user);
    
    // Set welcome name
    document.getElementById('welcome-name').textContent = userData.name || 'Institution Admin';
    document.getElementById('institution-name').textContent = userData.institution?.name || 'Institusi Anda';

    // Load dashboard stats
    loadDashboardStats();
    loadRecentActivity();
});

async function loadDashboardStats() {
    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch('/api/dashboard/stats', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success && result.data) {
            document.getElementById('stat-total-logbooks').textContent = result.data.total_logbooks || 0;
            document.getElementById('stat-total-entries').textContent = result.data.total_entries || 0;
            document.getElementById('stat-active-templates').textContent = result.data.active_templates || 0;
            document.getElementById('stat-total-users').textContent = result.data.total_users || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadRecentActivity() {
    const container = document.getElementById('recent-activity');
    
    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch('/api/audit-logs?limit=5', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success && result.data && result.data.length > 0) {
            container.innerHTML = result.data.map(activity => `
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-history text-indigo-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-800">${escapeHtml(activity.description || activity.action)}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-clock mr-1"></i>
                            ${formatDate(activity.created_at)}
                        </p>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                    <p>Belum ada aktivitas terbaru</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading activity:', error);
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                <p>Belum ada aktivitas terbaru</p>
            </div>
        `;
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}
</script>
@endpush
