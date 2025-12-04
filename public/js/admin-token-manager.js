/**
 * Admin Token Manager
 * 
 * Handles token expiration checking, auto-refresh, and session management
 * for admin and institution admin dashboards.
 * 
 * @version 1.0.0
 */
const AdminTokenManager = {
    // Configuration
    config: {
        tokenKey: 'admin_token',
        userKey: 'admin_user',
        expiresAtKey: 'admin_token_expires_at',
        refreshThresholdMinutes: 30, // Refresh token 30 minutes before expiration
        checkIntervalMs: 60000, // Check every minute
        loginUrl: '/login',
        refreshEndpoint: '/api/admin/refresh-token',
    },

    // State
    state: {
        checkInterval: null,
        isRefreshing: false,
    },

    /**
     * Initialize token manager
     * Call this on page load
     */
    init() {
        // Check if token exists
        if (!this.getToken()) {
            this.redirectToLogin();
            return;
        }

        // Start periodic token check
        this.startTokenCheck();

        // Setup API interceptor
        this.setupFetchInterceptor();

        console.log('[TokenManager] Initialized');
    },

    /**
     * Get stored token
     */
    getToken() {
        return localStorage.getItem(this.config.tokenKey);
    },

    /**
     * Get stored user data
     */
    getUser() {
        const userData = localStorage.getItem(this.config.userKey);
        return userData ? JSON.parse(userData) : null;
    },

    /**
     * Get token expiration time
     */
    getExpiresAt() {
        const expiresAt = localStorage.getItem(this.config.expiresAtKey);
        return expiresAt ? new Date(expiresAt) : null;
    },

    /**
     * Store token data after login
     */
    setTokenData(token, user, expiresAt) {
        localStorage.setItem(this.config.tokenKey, token);
        localStorage.setItem(this.config.userKey, JSON.stringify(user));
        if (expiresAt) {
            localStorage.setItem(this.config.expiresAtKey, expiresAt);
        }
    },

    /**
     * Clear all token data
     */
    clearTokenData() {
        localStorage.removeItem(this.config.tokenKey);
        localStorage.removeItem(this.config.userKey);
        localStorage.removeItem(this.config.expiresAtKey);
        
        // Clear any cached data
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && (key.includes('_cache') || key.includes('_timestamp'))) {
                keysToRemove.push(key);
            }
        }
        keysToRemove.forEach(key => localStorage.removeItem(key));
    },

    /**
     * Check if token is expired or about to expire
     */
    isTokenExpired() {
        const expiresAt = this.getExpiresAt();
        if (!expiresAt) return false; // No expiration = never expires (mobile token)
        return new Date() >= expiresAt;
    },

    /**
     * Check if token should be refreshed (within threshold)
     */
    shouldRefreshToken() {
        const expiresAt = this.getExpiresAt();
        if (!expiresAt) return false; // No expiration = no need to refresh
        
        const now = new Date();
        const thresholdMs = this.config.refreshThresholdMinutes * 60 * 1000;
        const timeUntilExpiry = expiresAt.getTime() - now.getTime();
        
        return timeUntilExpiry > 0 && timeUntilExpiry <= thresholdMs;
    },

    /**
     * Get remaining time until token expires
     */
    getRemainingTime() {
        const expiresAt = this.getExpiresAt();
        if (!expiresAt) return null;
        
        const now = new Date();
        const diffMs = expiresAt.getTime() - now.getTime();
        
        if (diffMs <= 0) return { expired: true };
        
        const hours = Math.floor(diffMs / (1000 * 60 * 60));
        const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diffMs % (1000 * 60)) / 1000);
        
        return {
            expired: false,
            hours,
            minutes,
            seconds,
            totalMinutes: Math.floor(diffMs / (1000 * 60)),
            formatted: `${hours}h ${minutes}m ${seconds}s`
        };
    },

    /**
     * Start periodic token check
     */
    startTokenCheck() {
        // Clear existing interval if any
        if (this.state.checkInterval) {
            clearInterval(this.state.checkInterval);
        }

        // Initial check
        this.checkToken();

        // Set up periodic check
        this.state.checkInterval = setInterval(() => {
            this.checkToken();
        }, this.config.checkIntervalMs);
    },

    /**
     * Stop periodic token check
     */
    stopTokenCheck() {
        if (this.state.checkInterval) {
            clearInterval(this.state.checkInterval);
            this.state.checkInterval = null;
        }
    },

    /**
     * Check token status and take appropriate action
     */
    async checkToken() {
        // Check if token is expired
        if (this.isTokenExpired()) {
            console.log('[TokenManager] Token expired, redirecting to login');
            this.showExpiredModal();
            return;
        }

        // Check if token should be refreshed
        if (this.shouldRefreshToken() && !this.state.isRefreshing) {
            console.log('[TokenManager] Token near expiry, attempting refresh');
            await this.refreshToken();
        }

        // Update session indicator if it exists
        this.updateSessionIndicator();
    },

    /**
     * Refresh the token
     */
    async refreshToken() {
        if (this.state.isRefreshing) return false;
        
        this.state.isRefreshing = true;
        
        try {
            const response = await fetch(this.config.refreshEndpoint, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Update stored token data
                const user = this.getUser();
                this.setTokenData(data.data.token, user, data.data.expires_at);
                
                console.log('[TokenManager] Token refreshed successfully');
                this.showNotification('Session diperpanjang', 'success');
                return true;
            } else {
                console.error('[TokenManager] Token refresh failed:', data);
                
                if (response.status === 401) {
                    this.showExpiredModal();
                }
                return false;
            }
        } catch (error) {
            console.error('[TokenManager] Token refresh error:', error);
            return false;
        } finally {
            this.state.isRefreshing = false;
        }
    },

    /**
     * Setup fetch interceptor for API calls
     */
    setupFetchInterceptor() {
        const originalFetch = window.fetch;
        const self = this;

        window.fetch = async function(...args) {
            const response = await originalFetch.apply(this, args);
            
            // Check for token expiration response
            if (response.status === 401) {
                const clonedResponse = response.clone();
                try {
                    const data = await clonedResponse.json();
                    
                    if (data.error_code === 'TOKEN_EXPIRED') {
                        console.log('[TokenManager] Received TOKEN_EXPIRED from API');
                        self.showExpiredModal();
                    } else if (data.error_code === 'UNAUTHENTICATED' || data.error_code === 'TOKEN_NOT_FOUND') {
                        console.log('[TokenManager] Received auth error:', data.error_code);
                        self.redirectToLogin();
                    }
                } catch (e) {
                    // Response is not JSON, ignore
                }
            }
            
            return response;
        };
    },

    /**
     * Show session expired modal
     */
    showExpiredModal() {
        this.stopTokenCheck();
        
        // Check if modal already exists
        if (document.getElementById('tokenExpiredModal')) return;

        const modal = document.createElement('div');
        modal.id = 'tokenExpiredModal';
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center';
        modal.innerHTML = `
            <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-clock text-4xl text-red-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Sesi Telah Berakhir</h3>
                    <p class="text-gray-600 mb-6">
                        Sesi Anda telah berakhir karena tidak ada aktivitas. 
                        Silakan login kembali untuk melanjutkan.
                    </p>
                    <button onclick="AdminTokenManager.redirectToLogin()" 
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login Kembali
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    },

    /**
     * Show notification toast
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 z-[9998] px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-y-full opacity-0 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                }"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-y-full', 'opacity-0');
        }, 100);
        
        // Remove after delay
        setTimeout(() => {
            notification.classList.add('translate-y-full', 'opacity-0');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    },

    /**
     * Update session indicator in UI (if exists)
     */
    updateSessionIndicator() {
        const indicator = document.getElementById('sessionTimeRemaining');
        if (!indicator) return;

        const remaining = this.getRemainingTime();
        if (!remaining || remaining.expired) {
            indicator.innerHTML = '<span class="text-red-500">Expired</span>';
            return;
        }

        // Show warning color if less than 30 minutes
        const colorClass = remaining.totalMinutes < 30 ? 'text-yellow-500' : 'text-gray-500';
        indicator.innerHTML = `<span class="${colorClass}">${remaining.formatted}</span>`;
    },

    /**
     * Redirect to login page
     */
    redirectToLogin() {
        this.clearTokenData();
        this.stopTokenCheck();
        window.location.href = this.config.loginUrl;
    },

    /**
     * Handle logout
     */
    async logout() {
        const token = this.getToken();
        
        if (token) {
            try {
                await fetch('/api/admin/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {
                console.error('[TokenManager] Logout API error:', e);
            }
        }
        
        this.redirectToLogin();
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on admin pages (not login page)
    if (!window.location.pathname.includes('/login')) {
        AdminTokenManager.init();
    }
});

// Export for global use
window.AdminTokenManager = AdminTokenManager;
