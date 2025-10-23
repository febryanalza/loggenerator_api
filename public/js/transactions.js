/**
 * Audit Trail JavaScript with Caching and Real-time Streaming
 * Cache duration: 10 menit
 * Real-time updates via Server-Sent Events (SSE)
 */

class AuditTrail {
    constructor() {
        this.baseApiUrl = '/api/admin/audit-trail';
        this.token = localStorage.getItem('admin_token');
        
        // Cache configuration
        this.CACHE_DURATION = 10 * 60 * 1000; // 10 menit
        this.CACHE_KEYS = {
            STATISTICS: 'audit_trail_statistics_cache',
            LOGS: 'audit_trail_logs_cache',
            ACTION_TYPES: 'audit_trail_action_types_cache'
        };
        
        // State
        this.currentPage = 1;
        this.currentFilters = {
            start_date: this.getDefaultStartDate(),
            end_date: this.getDefaultEndDate(),
            action: '',
            search: ''
        };
        
        // Charts
        this.charts = {};
        
        // Real-time streaming
        this.eventSource = null;
        this.isStreaming = false;
        this.lastLogId = 0;
    }

    // ============================================
    // CACHE HELPER FUNCTIONS
    // ============================================

    isValidCache(cacheKey) {
        const cached = localStorage.getItem(cacheKey);
        if (!cached) return false;

        try {
            const { timestamp, filters } = JSON.parse(cached);
            const now = Date.now();
            const isValid = (now - timestamp) < this.CACHE_DURATION;
            
            // Check if filters match current filters
            const filtersMatch = filters && 
                filters.start_date === this.currentFilters.start_date &&
                filters.end_date === this.currentFilters.end_date &&
                filters.action === this.currentFilters.action;
            
            if (!isValid || !filtersMatch) {
                console.log(`❌ CACHE INVALID: ${cacheKey}`);
                localStorage.removeItem(cacheKey);
                return false;
            }
            
            return true;
        } catch (e) {
            localStorage.removeItem(cacheKey);
            return false;
        }
    }

    getCache(cacheKey) {
        if (!this.isValidCache(cacheKey)) return null;

        try {
            const cached = localStorage.getItem(cacheKey);
            const { data } = JSON.parse(cached);
            console.log(`📦 Using CACHE: ${cacheKey}`);
            return data;
        } catch (e) {
            localStorage.removeItem(cacheKey);
            return null;
        }
    }

    setCache(cacheKey, data) {
        try {
            const cacheData = {
                data: data,
                timestamp: Date.now(),
                filters: { ...this.currentFilters }
            };
            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
            console.log(`💾 Saved to CACHE: ${cacheKey}`);
        } catch (e) {
            console.error('Error saving to cache:', e);
        }
    }

    clearAllCache() {
        Object.values(this.CACHE_KEYS).forEach(key => {
            localStorage.removeItem(key);
        });
        console.log('🗑️ All cache cleared');
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    async init() {
        if (!this.token) {
            this.showToast('No token found', 'error');
            return;
        }

        // Set default dates
        document.getElementById('startDate').value = this.currentFilters.start_date;
        document.getElementById('endDate').value = this.currentFilters.end_date;

        // Load action types for filter
        await this.loadActionTypes();

        // Load data
        await this.loadStatistics();
        await this.loadLogs();

        // Setup event listeners
        this.setupEventListeners();
    }

    getDefaultStartDate() {
        const date = new Date();
        date.setDate(date.getDate() - 7);
        return date.toISOString().split('T')[0];
    }

    getDefaultEndDate() {
        return new Date().toISOString().split('T')[0];
    }

    getHeaders() {
        return {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
    }

    // ============================================
    // DATA LOADING WITH CACHE
    // ============================================

    async loadStatistics(forceRefresh = false) {
        try {
            // Check cache
            if (!forceRefresh) {
                const cachedData = this.getCache(this.CACHE_KEYS.STATISTICS);
                if (cachedData !== null) {
                    this.updateStatisticsDisplay(cachedData);
                    this.renderCharts(cachedData);
                    return;
                }
            }

            this.showLoading(true);

            // Fetch from API
            console.log('🌐 Fetching statistics from API...');
            const params = new URLSearchParams(this.currentFilters);
            const response = await fetch(`${this.baseApiUrl}/statistics?${params}`, {
                headers: this.getHeaders()
            });

            if (!response.ok) throw new Error('Failed to load statistics');

            const result = await response.json();
            const data = result.data;

            // Save to cache
            this.setCache(this.CACHE_KEYS.STATISTICS, data);

            // Update UI
            this.updateStatisticsDisplay(data);
            this.renderCharts(data);

        } catch (error) {
            console.error('Error loading statistics:', error);
            this.showToast('Failed to load statistics', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async loadLogs(forceRefresh = false, page = 1) {
        try {
            const cacheKey = `${this.CACHE_KEYS.LOGS}_page_${page}`;

            // Check cache
            if (!forceRefresh && page === this.currentPage) {
                const cachedData = this.getCache(cacheKey);
                if (cachedData !== null) {
                    this.updateLogsTable(cachedData);
                    return;
                }
            }

            this.showLoading(true);

            // Fetch from API
            console.log(`🌐 Fetching logs page ${page} from API...`);
            const params = new URLSearchParams({
                ...this.currentFilters,
                page: page,
                per_page: 20
            });
            
            const response = await fetch(`${this.baseApiUrl}/logs?${params}`, {
                headers: this.getHeaders()
            });

            if (!response.ok) throw new Error('Failed to load logs');

            const result = await response.json();
            const data = result.data;

            // Save to cache
            this.setCache(cacheKey, data);

            // Update UI
            this.currentPage = page;
            this.updateLogsTable(data);

        } catch (error) {
            console.error('Error loading logs:', error);
            this.showToast('Failed to load logs', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async loadActionTypes() {
        try {
            // Check cache
            const cachedData = this.getCache(this.CACHE_KEYS.ACTION_TYPES);
            if (cachedData !== null) {
                this.populateActionFilter(cachedData);
                return;
            }

            // Fetch from API
            const response = await fetch(`${this.baseApiUrl}/action-types`, {
                headers: this.getHeaders()
            });

            if (!response.ok) throw new Error('Failed to load action types');

            const result = await response.json();
            const data = result.data;

            // Save to cache (this rarely changes)
            this.setCache(this.CACHE_KEYS.ACTION_TYPES, data);

            // Update UI
            this.populateActionFilter(data);

        } catch (error) {
            console.error('Error loading action types:', error);
        }
    }

    // ============================================
    // UI UPDATE FUNCTIONS
    // ============================================

    updateStatisticsDisplay(data) {
        document.getElementById('totalActivities').textContent = 
            data.summary.total_activities.toLocaleString();
        document.getElementById('uniqueUsers').textContent = 
            data.summary.unique_users.toLocaleString();
        document.getElementById('actionTypes').textContent = 
            data.summary.action_types.toLocaleString();
    }

    updateLogsTable(data) {
        const tbody = document.getElementById('auditLogsTableBody');
        
        if (!data.logs || data.logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Tidak ada log ditemukan</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.logs.map(log => `
            <tr class="hover:bg-gray-50 transition duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${this.formatDate(log.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full ${this.getActionBadgeClass(log.action)}">
                        ${log.action}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="font-medium text-gray-900">${log.user ? log.user.name : 'System'}</div>
                    <div class="text-gray-500">${log.user ? log.user.email : 'system@app.com'}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    ${log.description || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${log.ip_address || 'N/A'}
                </td>
            </tr>
        `).join('');

        // Update pagination
        this.updatePagination(data.pagination);

        // Store last log ID for streaming
        if (data.logs.length > 0) {
            this.lastLogId = data.logs[0].id;
        }
    }

    updatePagination(pagination) {
        document.getElementById('showingFrom').textContent = pagination.from || 0;
        document.getElementById('showingTo').textContent = pagination.to || 0;
        document.getElementById('totalLogs').textContent = pagination.total.toLocaleString();

        const paginationDiv = document.getElementById('pagination');
        const currentPage = pagination.current_page;
        const lastPage = pagination.last_page;

        let html = '';

        // Previous button
        if (currentPage > 1) {
            html += `<button onclick="auditTrail.loadLogs(false, ${currentPage - 1})" class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                <i class="fas fa-chevron-left"></i>
            </button>`;
        }

        // Page numbers
        for (let i = Math.max(1, currentPage - 2); i <= Math.min(lastPage, currentPage + 2); i++) {
            const activeClass = i === currentPage ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
            html += `<button onclick="auditTrail.loadLogs(false, ${i})" class="px-3 py-1 border border-gray-300 rounded-lg ${activeClass} text-sm">${i}</button>`;
        }

        // Next button
        if (currentPage < lastPage) {
            html += `<button onclick="auditTrail.loadLogs(false, ${currentPage + 1})" class="px-3 py-1 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                <i class="fas fa-chevron-right"></i>
            </button>`;
        }

        paginationDiv.innerHTML = html;
    }

    populateActionFilter(actionTypes) {
        const select = document.getElementById('actionFilter');
        const currentValue = select.value;

        select.innerHTML = '<option value="">Semua Aktivitas</option>' +
            actionTypes.map(type => `
                <option value="${type.action}">${type.action} (${type.count})</option>
            `).join('');

        select.value = currentValue;
    }

    // ============================================
    // CHARTS RENDERING
    // ============================================

    renderCharts(data) {
        this.renderActivityTypeChart(data.activity_by_type);
        this.renderDailyTrendChart(data.daily_trend);
        this.renderTopUsersChart(data.top_users);
    }

    renderActivityTypeChart(data) {
        const ctx = document.getElementById('activityTypeChart');
        if (!ctx) return;

        if (this.charts.activityTypeChart) {
            this.charts.activityTypeChart.destroy();
        }

        const colors = this.generateColors(data.length);

        this.charts.activityTypeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.action),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    renderDailyTrendChart(data) {
        const ctx = document.getElementById('dailyTrendChart');
        if (!ctx) return;

        if (this.charts.dailyTrendChart) {
            this.charts.dailyTrendChart.destroy();
        }

        this.charts.dailyTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => this.formatDateShort(item.date)),
                datasets: [{
                    label: 'Aktivitas',
                    data: data.map(item => item.count),
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(79, 70, 229)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
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
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    renderTopUsersChart(data) {
        const ctx = document.getElementById('topUsersChart');
        if (!ctx) return;

        if (this.charts.topUsersChart) {
            this.charts.topUsersChart.destroy();
        }

        this.charts.topUsersChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.user_name),
                datasets: [{
                    label: 'Jumlah Aktivitas',
                    data: data.map(item => item.count),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                return data[context.dataIndex].user_email;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // ============================================
    // REAL-TIME STREAMING
    // ============================================

    toggleStream() {
        if (this.isStreaming) {
            this.stopStream();
        } else {
            this.startStream();
        }
    }

    startStream() {
        if (this.eventSource) {
            this.eventSource.close();
        }

        console.log('🔴 Starting real-time stream...');

        const params = new URLSearchParams({
            last_id: this.lastLogId,
            action: this.currentFilters.action
        });

        this.eventSource = new EventSource(
            `${this.baseApiUrl}/stream?${params}`,
            { withCredentials: false }
        );

        this.eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                
                if (data.type === 'new_logs') {
                    console.log('📨 Received new logs:', data.logs.length);
                    this.prependNewLogs(data.logs);
                    this.updateStreamStatus('live', data.logs.length);
                } else if (data.type === 'heartbeat') {
                    console.log('💓 Heartbeat received');
                }
            } catch (error) {
                console.error('Error parsing SSE data:', error);
            }
        };

        this.eventSource.onerror = (error) => {
            console.error('❌ SSE connection error:', error);
            this.stopStream();
            this.showToast('Stream connection lost', 'error');
        };

        this.isStreaming = true;
        this.updateStreamButton(true);
        this.updateStreamStatus('connecting');
    }

    stopStream() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }

        console.log('⏹️ Stream stopped');
        this.isStreaming = false;
        this.updateStreamButton(false);
        this.updateStreamStatus('stopped');
    }

    prependNewLogs(newLogs) {
        const tbody = document.getElementById('auditLogsTableBody');
        
        // Get current rows
        const currentRows = Array.from(tbody.querySelectorAll('tr'));
        
        // Create new rows
        const newRowsHTML = newLogs.reverse().map(log => `
            <tr class="hover:bg-gray-50 transition duration-150 bg-green-50 animate-pulse">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${this.formatDate(log.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full ${this.getActionBadgeClass(log.action)}">
                        ${log.action}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="font-medium text-gray-900">${log.user_name}</div>
                    <div class="text-gray-500">${log.user_email}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    ${log.description || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${log.ip_address || 'N/A'}
                </td>
            </tr>
        `).join('');

        // Prepend new rows
        tbody.innerHTML = newRowsHTML + tbody.innerHTML;

        // Remove animation after 2 seconds
        setTimeout(() => {
            tbody.querySelectorAll('.animate-pulse').forEach(row => {
                row.classList.remove('animate-pulse', 'bg-green-50');
            });
        }, 2000);

        // Update last log ID
        if (newLogs.length > 0) {
            this.lastLogId = Math.max(...newLogs.map(log => parseInt(log.id)));
        }

        // Keep only 50 rows max
        const allRows = tbody.querySelectorAll('tr');
        if (allRows.length > 50) {
            for (let i = 50; i < allRows.length; i++) {
                allRows[i].remove();
            }
        }
    }

    updateStreamButton(isStreaming) {
        const btn = document.getElementById('toggleStreamBtn');
        const icon = btn.querySelector('i');
        const text = document.getElementById('streamBtnText');

        if (isStreaming) {
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-red-600', 'hover:bg-red-700');
            icon.classList.remove('fa-play');
            icon.classList.add('fa-stop');
            text.textContent = 'Stop Real-time';
        } else {
            btn.classList.remove('bg-red-600', 'hover:bg-red-700');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');
            icon.classList.remove('fa-stop');
            icon.classList.add('fa-play');
            text.textContent = 'Start Real-time';
        }
    }

    updateStreamStatus(status, newLogsCount = 0) {
        const statusEl = document.getElementById('streamStatus');
        
        if (status === 'live') {
            statusEl.innerHTML = `
                <span class="text-green-600">
                    <i class="fas fa-circle mr-1 animate-pulse"></i>
                    Live (+${newLogsCount})
                </span>
            `;
        } else if (status === 'connecting') {
            statusEl.innerHTML = `
                <span class="text-yellow-600">
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Connecting...
                </span>
            `;
        } else {
            statusEl.innerHTML = `
                <span class="text-gray-500">
                    <i class="fas fa-stop-circle mr-1"></i>
                    Stopped
                </span>
            `;
        }
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================

    setupEventListeners() {
        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.handleRefresh();
        });

        // Apply filter button
        document.getElementById('applyFilterBtn').addEventListener('click', () => {
            this.handleApplyFilter();
        });

        // Export button
        document.getElementById('exportBtn').addEventListener('click', () => {
            this.handleExport();
        });

        // Stream toggle button
        document.getElementById('toggleStreamBtn').addEventListener('click', () => {
            this.toggleStream();
        });

        // Search input with debounce
        let searchTimeout;
        document.getElementById('searchLog').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.currentFilters.search = e.target.value;
                this.loadLogs(true);
            }, 500);
        });
    }

    async handleRefresh() {
        const btn = document.getElementById('refreshBtn');
        const icon = btn.querySelector('i');
        
        icon.classList.add('fa-spin');
        btn.disabled = true;

        try {
            this.clearAllCache();
            await this.loadStatistics(true);
            await this.loadLogs(true);
            this.showToast('Data berhasil diperbarui', 'success');
        } catch (error) {
            this.showToast('Gagal memperbarui data', 'error');
        } finally {
            icon.classList.remove('fa-spin');
            btn.disabled = false;
        }
    }

    async handleApplyFilter() {
        this.currentFilters.start_date = document.getElementById('startDate').value;
        this.currentFilters.end_date = document.getElementById('endDate').value;
        this.currentFilters.action = document.getElementById('actionFilter').value;

        // Clear cache when filters change
        this.clearAllCache();

        await this.loadStatistics(true);
        await this.loadLogs(true);

        this.showToast('Filter diterapkan', 'success');
    }

    handleExport() {
        const params = new URLSearchParams(this.currentFilters);
        const url = `${this.baseApiUrl}/export?${params}`;
        
        window.location.href = url;
        this.showToast('Export dimulai...', 'success');
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatDateShort(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            month: 'short',
            day: 'numeric'
        });
    }

    getActionBadgeClass(action) {
        const colorMap = {
            'LOGIN': 'bg-green-100 text-green-800',
            'LOGOUT': 'bg-gray-100 text-gray-800',
            'CREATE': 'bg-blue-100 text-blue-800',
            'UPDATE': 'bg-yellow-100 text-yellow-800',
            'DELETE': 'bg-red-100 text-red-800',
            'VIEW': 'bg-purple-100 text-purple-800'
        };

        // Find matching key
        for (const [key, value] of Object.entries(colorMap)) {
            if (action.includes(key)) {
                return value;
            }
        }

        return 'bg-gray-100 text-gray-800';
    }

    generateColors(count) {
        const colors = [
            'rgba(79, 70, 229, 0.8)',   // Indigo
            'rgba(34, 197, 94, 0.8)',   // Green
            'rgba(234, 179, 8, 0.8)',   // Yellow
            'rgba(239, 68, 68, 0.8)',   // Red
            'rgba(168, 85, 247, 0.8)',  // Purple
            'rgba(59, 130, 246, 0.8)',  // Blue
            'rgba(236, 72, 153, 0.8)',  // Pink
            'rgba(20, 184, 166, 0.8)',  // Teal
            'rgba(249, 115, 22, 0.8)',  // Orange
            'rgba(107, 114, 128, 0.8)'  // Gray
        ];

        return Array(count).fill().map((_, i) => colors[i % colors.length]);
    }

    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    }

    showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        const messageEl = document.getElementById('toastMessage');

        const icons = {
            success: 'fas fa-check-circle text-green-500',
            error: 'fas fa-exclamation-circle text-red-500',
            info: 'fas fa-info-circle text-blue-500'
        };

        icon.className = icons[type] || icons.info;
        messageEl.textContent = message;

        toast.classList.remove('hidden');

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // Cleanup on page unload
    destroy() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Initialize
let auditTrail;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Transactions & Activities Page with Tabs...');
    
    // Tab Switching Logic
    initializeTabs();
    
    // Initialize Audit Trail (Tab 1)
    auditTrail = new AuditTrail();
    auditTrail.init().catch(error => {
        console.error('Failed to initialize audit trail:', error);
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (auditTrail) {
            auditTrail.destroy();
        }
    });

    console.log('💡 Transactions page initialized with:');
    console.log('   - Tab navigation (4 tabs)');
    console.log('   - Audit Trail with 10-minute caching');
    console.log('   - Real-time streaming (SSE)');
    console.log('   - Interactive charts');
});

/**
 * Tab Switching Functionality
 */
function initializeTabs() {
    const tabs = {
        tabAuditTrail: 'contentAuditTrail',
        tabTransactions: 'contentTransactions',
        tabAnalytics: 'contentAnalytics',
        tabSecurity: 'contentSecurity'
    };

    // Add click event to each tab button
    Object.keys(tabs).forEach(tabId => {
        const tabBtn = document.getElementById(tabId);
        const contentId = tabs[tabId];

        if (tabBtn) {
            tabBtn.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });

                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                    content.classList.add('hidden');
                });

                // Activate clicked tab
                tabBtn.classList.add('active', 'border-indigo-600', 'text-indigo-600');
                tabBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');

                // Show corresponding content
                const content = document.getElementById(contentId);
                if (content) {
                    content.classList.remove('hidden');
                    content.classList.add('active');
                }

                console.log(`📑 Switched to tab: ${tabId}`);
            });
        }
    });

    console.log('✅ Tab navigation initialized');
}

