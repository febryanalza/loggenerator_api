@extends('admin.layout')

@section('title', 'Transaksi & Aktivitas')
@section('page-title', 'Transaksi & Aktivitas')
@section('page-description', 'Monitor transaksi dan aktivitas sistem')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-5">
        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button id="tabAuditTrail" class="tab-btn active group inline-flex items-center py-4 px-6 border-b-2 font-medium text-sm">
                        <i class="fas fa-history mr-2"></i>
                        Audit Trail
                    </button>
                    <button id="tabTransactions" class="tab-btn group inline-flex items-center py-4 px-6 border-b-2 font-medium text-sm">
                        <i class="fas fa-receipt mr-2"></i>
                        Transaksi
                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Soon</span>
                    </button>
                    <button id="tabAnalytics" class="tab-btn group inline-flex items-center py-4 px-6 border-b-2 font-medium text-sm">
                        <i class="fas fa-chart-line mr-2"></i>
                        Analytics
                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Soon</span>
                    </button>
                    <button id="tabSecurity" class="tab-btn group inline-flex items-center py-4 px-6 border-b-2 font-medium text-sm">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Security
                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Soon</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content: Audit Trail -->
        <div id="contentAuditTrail" class="tab-content active">
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mb-6">
                <button id="refreshBtn" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
                <button id="exportBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                    <i class="fas fa-download mr-2"></i>
                    Export CSV
                </button>
                <button id="toggleStreamBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-play mr-2"></i>
                    <span id="streamBtnText">Start Real-time</span>
                </button>
            </div>

            <!-- Date Range Filter -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Mulai
                        </label>
                        <input type="date" id="startDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Akhir
                        </label>
                        <input type="date" id="endDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="actionFilter" class="block text-sm font-medium text-gray-700 mb-2">
                            Filter Aktivitas
                        </label>
                        <select id="actionFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Semua Aktivitas</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button id="applyFilterBtn" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                            <i class="fas fa-filter mr-2"></i>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Aktivitas</p>
                            <p class="text-2xl font-bold text-gray-900" id="totalActivities">0</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pengguna Aktif</p>
                            <p class="text-2xl font-bold text-gray-900" id="uniqueUsers">0</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Jenis Aktivitas</p>
                            <p class="text-2xl font-bold text-gray-900" id="actionTypes">0</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-tags text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Status Stream</p>
                            <p class="text-sm font-bold" id="streamStatus">
                                <span class="text-gray-500">
                                    <i class="fas fa-stop-circle mr-1"></i>
                                    Stopped
                                </span>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-broadcast-tower text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Activity by Type Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>
                        Aktivitas Berdasarkan Jenis
                    </h3>
                    <div class="h-80">
                        <canvas id="activityTypeChart"></canvas>
                    </div>
                </div>
                
                <!-- Daily Trend Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-area mr-2 text-indigo-600"></i>
                        Tren Aktivitas Harian
                    </h3>
                    <div class="h-80">
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Users Chart -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-user-chart mr-2 text-indigo-600"></i>
                    Top 10 Pengguna Paling Aktif
                </h3>
                <div class="h-80">
                    <canvas id="topUsersChart"></canvas>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-list mr-2 text-indigo-600"></i>
                            Daftar Audit Log
                        </h3>
                        <div class="flex items-center space-x-3">
                            <input type="text" id="searchLog" placeholder="Cari aktivitas..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aktivitas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pelaku
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deskripsi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP Address
                                </th>
                            </tr>
                        </thead>
                        <tbody id="auditLogsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Logs will be inserted here -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span id="showingFrom">0</span> - <span id="showingTo">0</span> dari <span id="totalLogs">0</span> log
                        </div>
                        <div id="pagination" class="flex space-x-2">
                            <!-- Pagination buttons will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tab Content: Transactions (Coming Soon) -->
        <div id="contentTransactions" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Transaksi</h3>
                <p class="text-gray-600">Fitur dalam pengembangan</p>
            </div>
        </div>

        <!-- Tab Content: Analytics (Coming Soon) -->
        <div id="contentAnalytics" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-chart-line text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Analytics</h3>
                <p class="text-gray-600">Fitur dalam pengembangan</p>
            </div>
        </div>

        <!-- Tab Content: Security (Coming Soon) -->
        <div id="contentSecurity" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-shield-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Security Monitoring</h3>
                <p class="text-gray-600">Fitur dalam pengembangan</p>
            </div>
        </div>

    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center space-x-3">
            <i class="fas fa-spinner fa-spin text-indigo-600 text-2xl"></i>
            <span class="text-gray-900 font-medium">Loading...</span>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 bg-white rounded-lg shadow-lg p-4 z-50 max-w-sm">
    <div class="flex items-center space-x-3">
        <i id="toastIcon" class="text-2xl"></i>
        <div>
            <p id="toastMessage" class="font-medium text-gray-900"></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/transactions.js') }}"></script>
@endpush

@push('styles')
<style>
    /* Tab Styling */
    .tab-btn {
        border-bottom: 2px solid transparent;
        color: #6b7280;
        transition: all 0.3s ease;
    }
    
    .tab-btn:hover {
        color: #374151;
        border-bottom-color: #d1d5db;
    }
    
    .tab-btn.active {
        color: #4f46e5;
        border-bottom-color: #4f46e5;
    }
    
    /* Tab Content */
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease-in;
    }
    
    .tab-content.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush