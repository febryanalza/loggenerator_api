@extends('institution_admin.layout')

@section('title', 'Laporan & Statistik')
@section('page-title', 'Laporan & Statistik')
@section('page-description', 'Lihat laporan dan statistik logbook institusi Anda')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Laporan & Statistik</span>
    </div>
</li>
@endsection

@section('content')
<div id="pageLoading" class="flex flex-col items-center justify-center py-20">
    <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
    <p class="mt-4 text-gray-600">Memuat laporan...</p>
</div>

<div id="mainContent" class="hidden">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex gap-2">
            <button onclick="exportReport()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                <i class="fas fa-download mr-2"></i>
                Export Laporan
            </button>
            <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh
            </button>
        </div>
        
        <!-- Date Filter -->
        <div class="flex gap-2">
            <select id="periodFilter" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="7">7 Hari Terakhir</option>
                <option value="30" selected>30 Hari Terakhir</option>
                <option value="90">90 Hari Terakhir</option>
                <option value="365">1 Tahun Terakhir</option>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Entri</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalEntries">0</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i> +12% dari periode sebelumnya
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Logbook Aktif</p>
                    <p class="text-2xl font-bold text-gray-800" id="activeLogbooks">0</p>
                    <p class="text-xs text-blue-600 mt-1">
                        <i class="fas fa-arrow-up"></i> +3 logbook baru
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-book text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Kontributor Aktif</p>
                    <p class="text-2xl font-bold text-gray-800" id="activeContributors">0</p>
                    <p class="text-xs text-purple-600 mt-1">
                        <i class="fas fa-arrow-up"></i> +5 bulan ini
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Rata-rata Entri/Hari</p>
                    <p class="text-2xl font-bold text-gray-800" id="avgEntriesPerDay">0</p>
                    <p class="text-xs text-yellow-600 mt-1">
                        <i class="fas fa-minus"></i> Stabil
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Activity Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Logbook</h3>
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
        
        <!-- Logbook Distribution -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Logbook</h3>
            <div class="chart-container">
                <canvas id="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Contributors -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Contributors Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Top Kontributor</h3>
            </div>
            <div class="divide-y">
                <div id="topContributorsList">
                    <!-- Will be rendered dynamically -->
                </div>
            </div>
        </div>
        
        <!-- Most Active Logbooks -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Logbook Paling Aktif</h3>
            </div>
            <div class="divide-y">
                <div id="topLogbooksList">
                    <!-- Will be rendered dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let activityChart = null;
let distributionChart = null;

function getToken() {
    return localStorage.getItem('admin_token');
}

document.addEventListener('DOMContentLoaded', async function() {
    const token = getToken();
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    await loadReportData();
    document.getElementById('periodFilter').addEventListener('change', loadReportData);
});

async function loadReportData() {
    try {
        document.getElementById('pageLoading').classList.remove('hidden');
        document.getElementById('mainContent').classList.add('hidden');
        
        // TODO: Replace with actual API calls
        // Using mock data for now
        const mockData = {
            totalEntries: 1234,
            activeLogbooks: 12,
            activeContributors: 28,
            avgEntriesPerDay: 15.4,
            activityData: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                data: [45, 52, 38, 65, 58, 32, 24]
            },
            distributionData: {
                labels: ['Harian', 'Mingguan', 'Bulanan', 'Lainnya'],
                data: [35, 25, 30, 10]
            },
            topContributors: [
                { name: 'John Doe', entries: 156, avatar: 'J' },
                { name: 'Jane Smith', entries: 142, avatar: 'J' },
                { name: 'Bob Wilson', entries: 128, avatar: 'B' },
                { name: 'Alice Brown', entries: 115, avatar: 'A' },
                { name: 'Charlie Davis', entries: 98, avatar: 'C' }
            ],
            topLogbooks: [
                { name: 'Logbook Produksi', entries: 456, trend: 'up' },
                { name: 'Laporan Harian', entries: 342, trend: 'up' },
                { name: 'Monitoring Mesin', entries: 289, trend: 'down' },
                { name: 'Absensi Karyawan', entries: 234, trend: 'stable' },
                { name: 'Inventory Check', entries: 198, trend: 'up' }
            ]
        };
        
        // Update stats
        document.getElementById('totalEntries').textContent = mockData.totalEntries.toLocaleString();
        document.getElementById('activeLogbooks').textContent = mockData.activeLogbooks;
        document.getElementById('activeContributors').textContent = mockData.activeContributors;
        document.getElementById('avgEntriesPerDay').textContent = mockData.avgEntriesPerDay.toFixed(1);
        
        // Render charts
        renderActivityChart(mockData.activityData);
        renderDistributionChart(mockData.distributionData);
        
        // Render top lists
        renderTopContributors(mockData.topContributors);
        renderTopLogbooks(mockData.topLogbooks);
        
        document.getElementById('pageLoading').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');
    } catch (error) {
        console.error('Failed to load report data:', error);
        showAlert('error', 'Error', 'Gagal memuat data laporan');
    }
}

function renderActivityChart(data) {
    const ctx = document.getElementById('activityChart').getContext('2d');
    
    if (activityChart) {
        activityChart.destroy();
    }
    
    activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Entri Logbook',
                data: data.data,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function renderDistributionChart(data) {
    const ctx = document.getElementById('distributionChart').getContext('2d');
    
    if (distributionChart) {
        distributionChart.destroy();
    }
    
    distributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: [
                    '#22c55e',
                    '#3b82f6',
                    '#a855f7',
                    '#eab308'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function renderTopContributors(contributors) {
    const container = document.getElementById('topContributorsList');
    container.innerHTML = contributors.map((c, index) => `
        <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center">
                <span class="w-6 h-6 flex items-center justify-center text-sm font-medium ${index < 3 ? 'text-green-600' : 'text-gray-400'}">${index + 1}</span>
                <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white font-medium ml-3">
                    ${c.avatar}
                </div>
                <span class="ml-3 font-medium text-gray-800">${escapeHtml(c.name)}</span>
            </div>
            <span class="text-sm font-semibold text-green-600">${c.entries} entri</span>
        </div>
    `).join('');
}

function renderTopLogbooks(logbooks) {
    const container = document.getElementById('topLogbooksList');
    container.innerHTML = logbooks.map((l, index) => `
        <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center">
                <span class="w-6 h-6 flex items-center justify-center text-sm font-medium ${index < 3 ? 'text-blue-600' : 'text-gray-400'}">${index + 1}</span>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center ml-3">
                    <i class="fas fa-book text-blue-600"></i>
                </div>
                <span class="ml-3 font-medium text-gray-800">${escapeHtml(l.name)}</span>
            </div>
            <div class="flex items-center">
                <span class="text-sm font-semibold text-gray-600">${l.entries} entri</span>
                <i class="fas fa-arrow-${l.trend === 'up' ? 'up text-green-500' : l.trend === 'down' ? 'down text-red-500' : 'right text-gray-400'} ml-2"></i>
            </div>
        </div>
    `).join('');
}

function exportReport() {
    showAlert('info', 'Memproses...', 'Laporan sedang disiapkan untuk di-download');
    // TODO: Implement actual export functionality
    setTimeout(() => {
        showAlert('success', 'Berhasil', 'Laporan berhasil di-export');
    }, 2000);
}

async function refreshData() {
    await loadReportData();
    showAlert('success', 'Berhasil', 'Data berhasil diperbarui');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
