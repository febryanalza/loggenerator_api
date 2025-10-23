/**
 * Admin Dashboard JavaScript with Caching System
 * Menggunakan localStorage cache untuk mengurangi API calls
 * Cache duration: 10 menit
 */

class AdminDashboard {
    constructor() {
        this.baseApiUrl = '/api/admin';
        this.charts = {};
        this.token = localStorage.getItem('admin_token');
        
        // Cache configuration
        this.CACHE_DURATION = 10 * 60 * 1000; // 10 menit (600,000ms)
        this.CACHE_KEYS = {
            STATS: 'dashboard_stats_cache',
            USER_CHART: 'dashboard_user_chart_cache',
            LOGBOOK_CHART: 'dashboard_logbook_chart_cache',
            RECENT_ACTIVITY: 'dashboard_activity_cache'
        };
        
        this.refreshInterval = null;
    }

    // ============================================
    // CACHE HELPER FUNCTIONS
    // ============================================

    /**
     * Check if cache is valid (not expired)
     */
    isValidCache(cacheKey) {
        const cached = localStorage.getItem(cacheKey);
        if (!cached) return false;

        try {
            const { timestamp } = JSON.parse(cached);
            const now = Date.now();
            const isValid = (now - timestamp) < this.CACHE_DURATION;
            
            if (!isValid) {
                console.log(`❌ CACHE EXPIRED: ${cacheKey}`);
                localStorage.removeItem(cacheKey);
            }
            
            return isValid;
        } catch (e) {
            localStorage.removeItem(cacheKey);
            return false;
        }
    }

    /**
     * Get data from cache
     */
    getCache(cacheKey) {
        if (!this.isValidCache(cacheKey)) return null;

        try {
            const cached = localStorage.getItem(cacheKey);
            const { data } = JSON.parse(cached);
            console.log(`📦 Menggunakan data dari CACHE: ${cacheKey}`);
            return data;
        } catch (e) {
            localStorage.removeItem(cacheKey);
            return null;
        }
    }

    /**
     * Save data to cache
     */
    setCache(cacheKey, data) {
        try {
            const cacheData = {
                data: data,
                timestamp: Date.now()
            };
            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
            console.log(`💾 Data disimpan ke CACHE: ${cacheKey}`);
        } catch (e) {
            console.error('Error saving to cache:', e);
        }
    }

    /**
     * Clear all dashboard caches
     */
    clearAllCache() {
        Object.values(this.CACHE_KEYS).forEach(key => {
            localStorage.removeItem(key);
        });
        console.log('🗑️ Semua cache dashboard dihapus');
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    /**
     * Initialize dashboard
     */
    async init(forceRefresh = false) {
        if (!this.token) {
            throw new Error('No Bearer token found');
        }

        if (forceRefresh) {
            this.clearAllCache();
        }
        
        try {
            await this.loadDashboardStats(forceRefresh);
            await this.loadChartsData(forceRefresh);
            await this.loadRecentActivity(forceRefresh);
            
            this.setupEventListeners();
        } catch (error) {
            console.error('Dashboard initialization failed:', error);
            throw error;
        }
    }

    /**
     * Get request headers with Bearer token
     */
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

    /**
     * Load dashboard statistics
     */
    async loadDashboardStats(forceRefresh = false) {
        try {
            // CEK CACHE DULU
            if (!forceRefresh) {
                const cachedData = this.getCache(this.CACHE_KEYS.STATS);
                if (cachedData !== null) {
                    this.updateStatsDisplay(cachedData);
                    return;
                }
            }

            // PANGGIL API
            console.log('🌐 Memanggil API stats...');
            const response = await fetch(`${this.baseApiUrl}/stats`, {
                method: 'GET',
                headers: this.getHeaders()
            });
            
            if (!response.ok) {
                throw new Error(`Stats API error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // SIMPAN KE CACHE
            this.setCache(this.CACHE_KEYS.STATS, data);
            
            this.updateStatsDisplay(data);
            
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            throw error;
        }
    }

    /**
     * Load charts data
     */
    async loadChartsData(forceRefresh = false) {
        try {
            // Load user chart data
            await this.loadUserChart(forceRefresh);
            
            // Load logbook chart data
            await this.loadLogbookChart(forceRefresh);
            
        } catch (error) {
            console.error('Error loading charts data:', error);
        }
    }

    /**
     * Load user registration chart
     */
    async loadUserChart(forceRefresh = false) {
        try {
            // CEK CACHE DULU
            if (!forceRefresh) {
                const cachedData = this.getCache(this.CACHE_KEYS.USER_CHART);
                if (cachedData !== null) {
                    this.renderUserChart(cachedData);
                    return;
                }
            }

            // PANGGIL API
            console.log('🌐 Memanggil API user-registrations...');
            const response = await fetch(`${this.baseApiUrl}/user-registrations`, {
                method: 'GET',
                headers: this.getHeaders()
            });
            
            if (!response.ok) {
                console.error('User registrations API error:', response.status);
                return;
            }

            const userData = await response.json();
            
            // SIMPAN KE CACHE
            this.setCache(this.CACHE_KEYS.USER_CHART, userData);
            
            this.renderUserChart(userData);
            
        } catch (error) {
            console.error('Error loading user chart:', error);
        }
    }

    /**
     * Load logbook activity chart
     */
    async loadLogbookChart(forceRefresh = false) {
        try {
            // CEK CACHE DULU
            if (!forceRefresh) {
                const cachedData = this.getCache(this.CACHE_KEYS.LOGBOOK_CHART);
                if (cachedData !== null) {
                    this.renderLogbookChart(cachedData);
                    return;
                }
            }

            // PANGGIL API
            console.log('🌐 Memanggil API logbook-activity...');
            const response = await fetch(`${this.baseApiUrl}/logbook-activity`, {
                method: 'GET',
                headers: this.getHeaders()
            });
            
            if (!response.ok) {
                console.error('Logbook activity API error:', response.status);
                return;
            }

            const logbookData = await response.json();
            
            // SIMPAN KE CACHE
            this.setCache(this.CACHE_KEYS.LOGBOOK_CHART, logbookData);
            
            this.renderLogbookChart(logbookData);
            
        } catch (error) {
            console.error('Error loading logbook chart:', error);
        }
    }

    /**
     * Load recent activity
     */
    async loadRecentActivity(forceRefresh = false) {
        try {
            // CEK CACHE DULU
            if (!forceRefresh) {
                const cachedData = this.getCache(this.CACHE_KEYS.RECENT_ACTIVITY);
                if (cachedData !== null) {
                    this.updateRecentActivity(cachedData.activities);
                    return;
                }
            }

            // PANGGIL API
            console.log('🌐 Memanggil API recent-activity...');
            const response = await fetch(`${this.baseApiUrl}/recent-activity`, {
                method: 'GET',
                headers: this.getHeaders()
            });
            
            if (!response.ok) {
                throw new Error(`Recent activity API error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // SIMPAN KE CACHE
            this.setCache(this.CACHE_KEYS.RECENT_ACTIVITY, data);
            
            this.updateRecentActivity(data.activities);
            
        } catch (error) {
            console.error('Error loading recent activity:', error);
        }
    }

    // ============================================
    // DISPLAY UPDATE FUNCTIONS
    // ============================================

    /**
     * Update statistics display
     */
    updateStatsDisplay(data) {
        const elements = {
            'totalUsers': data.totalUsers || 0,
            'totalTemplates': data.totalTemplates || 0,
            'totalEntries': data.totalEntries || 0,
            'totalAuditLogs': data.totalAuditLogs || 0
        };

        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                // Animate number change
                this.animateValue(element, parseInt(element.textContent) || 0, value, 500);
            }
        }
    }

    /**
     * Animate number value change
     */
    animateValue(element, start, end, duration) {
        if (start === end) {
            element.textContent = end;
            return;
        }

        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                element.textContent = end;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }

    /**
     * Render user registration chart
     */
    renderUserChart(data) {
        const ctx = document.getElementById('userChart');
        if (!ctx) return;

        const chartCtx = ctx.getContext('2d');
        
        if (this.charts.userChart) {
            this.charts.userChart.destroy();
        }
        
        this.charts.userChart = new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'New Users',
                    data: data.data,
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
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

    /**
     * Render logbook activity chart
     */
    renderLogbookChart(data) {
        const ctx = document.getElementById('logbookChart');
        if (!ctx) return;

        const chartCtx = ctx.getContext('2d');
        
        if (this.charts.logbookChart) {
            this.charts.logbookChart.destroy();
        }
        
        this.charts.logbookChart = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Logbook Entries',
                    data: data.data,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
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

    /**
     * Update recent activity display
     */
    updateRecentActivity(activities) {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        if (!activities || activities.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-info-circle mr-2"></i>
                    No recent activity found
                </div>
            `;
            return;
        }
        
        const html = activities.map(activity => `
            <div class="p-4 hover:bg-gray-50 transition duration-200">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">
                                ${activity.user_name}
                            </p>
                            <p class="text-xs text-gray-500">
                                ${activity.created_at}
                            </p>
                        </div>
                        <p class="text-sm text-gray-600">
                            ${activity.action} on ${activity.model_type}
                        </p>
                        <p class="text-xs text-gray-500">
                            ${activity.user_email}
                        </p>
                    </div>
                </div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleRefreshClick(e.target);
            });
        }
    }

    /**
     * Handle refresh button click
     */
    async handleRefreshClick(button) {
        const icon = button.querySelector('i');
        if (!icon) return;

        icon.classList.add('fa-spin');
        button.disabled = true;

        try {
            await this.init(true); // Force refresh
            
            // Show success message
            this.showToast('Dashboard berhasil diperbarui', 'success');
        } catch (error) {
            this.showToast('Gagal memperbarui dashboard', 'error');
        } finally {
            icon.classList.remove('fa-spin');
            button.disabled = false;
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        } text-white`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.style.transform = 'translateY(0)', 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateY(-100px)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /**
     * Refresh all dashboard data (manual refresh)
     */
    async refreshAll() {
        try {
            await this.init(true); // Force refresh
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
        }
    }

    /**
     * Get cache info for debugging
     */
    getCacheInfo() {
        const info = {};
        Object.entries(this.CACHE_KEYS).forEach(([name, key]) => {
            const cached = localStorage.getItem(key);
            if (cached) {
                try {
                    const { timestamp } = JSON.parse(cached);
                    const age = Date.now() - timestamp;
                    const remaining = this.CACHE_DURATION - age;
                    info[name] = {
                        age: Math.floor(age / 1000) + 's',
                        remaining: Math.floor(remaining / 1000) + 's',
                        valid: remaining > 0
                    };
                } catch (e) {
                    info[name] = { error: 'Invalid cache data' };
                }
            } else {
                info[name] = { status: 'No cache' };
            }
        });
        return info;
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Admin Dashboard with Caching...');
    
    if (typeof window.dashboard === 'undefined') {
        window.dashboard = new AdminDashboard();
        
        // Initialize dashboard
        window.dashboard.init().catch(error => {
            console.error('Failed to initialize dashboard:', error);
        });
        
        // Expose cache info to console for debugging
        window.dashboardCacheInfo = () => {
            console.table(window.dashboard.getCacheInfo());
        };
        
        console.log('💡 Tip: Run dashboardCacheInfo() to see cache status');
    }
});
