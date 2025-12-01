<!-- Tab Content: Assignment History -->
<div id="tab-history" class="tab-content hidden">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-filter text-purple-600 mr-2"></i>
            Filter History
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
                <select id="historyActionFilter" onchange="AssignmentHistoryManager.loadHistory()"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">All Actions</option>
                    <option value="CREATE_ROLE">Create Role</option>
                    <option value="UPDATE_ROLE">Update Role</option>
                    <option value="DELETE_ROLE">Delete Role</option>
                    <option value="ASSIGN_PERMISSIONS_TO_ROLE">Assign Permissions</option>
                    <option value="REVOKE_PERMISSIONS_FROM_ROLE">Revoke Permissions</option>
                    <option value="SYNC_ROLE_PERMISSIONS">Sync Permissions</option>
                    <option value="USER_ROLE_CHANGED">User Role Changed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" id="historyStartDate" onchange="AssignmentHistoryManager.loadHistory()"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" id="historyEndDate" onchange="AssignmentHistoryManager.loadHistory()"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div class="flex items-end">
                <button onclick="AssignmentHistoryManager.clearFilters()" 
                    class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history text-purple-600 mr-2"></i>
                Role Assignment History
            </h3>
            <button onclick="AssignmentHistoryManager.loadHistory()" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>

        <div id="historyContainer">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-purple-600 text-2xl"></i>
                <p class="mt-2 text-gray-500">Loading history...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between border-t pt-4">
            <div class="text-sm text-gray-600">
                Showing <span id="historyShowing">0</span> of <span id="historyTotal">0</span> records
            </div>
            <div class="flex items-center space-x-2">
                <button id="historyPrevBtn" onclick="AssignmentHistoryManager.prevPage()" 
                    class="px-3 py-1 border rounded text-gray-600 hover:bg-gray-100 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span id="historyPageInfo" class="text-sm text-gray-600">Page 1 of 1</span>
                <button id="historyNextBtn" onclick="AssignmentHistoryManager.nextPage()" 
                    class="px-3 py-1 border rounded text-gray-600 hover:bg-gray-100 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Assignment History Manager
const AssignmentHistoryManager = {
    currentPage: 1,
    perPage: 20,
    totalPages: 1,

    init() {
        // Set default dates (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        
        document.getElementById('historyEndDate').value = today.toISOString().split('T')[0];
        document.getElementById('historyStartDate').value = thirtyDaysAgo.toISOString().split('T')[0];
        
        this.loadHistory();
    },

    async loadHistory() {
        const container = document.getElementById('historyContainer');
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-purple-600 text-2xl"></i>
                <p class="mt-2 text-gray-500">Loading history...</p>
            </div>
        `;

        try {
            const actionType = document.getElementById('historyActionFilter').value;
            const startDate = document.getElementById('historyStartDate').value;
            const endDate = document.getElementById('historyEndDate').value;

            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...(actionType && { action_type: actionType }),
                ...(startDate && { start_date: startDate }),
                ...(endDate && { end_date: endDate })
            });

            const response = await fetch(`/api/roles/history?${params}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load history');

            const result = await response.json();
            
            if (result.success) {
                this.renderHistory(result.data);
                this.updatePagination(result.pagination);
            }
        } catch (error) {
            console.error('Failed to load history:', error);
            container.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <p class="mt-2">Failed to load history</p>
                </div>
            `;
        }
    },

    renderHistory(logs) {
        const container = document.getElementById('historyContainer');

        if (!logs || logs.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-history text-4xl mb-2"></i>
                    <p>No history records found</p>
                </div>
            `;
            return;
        }

        let html = '<div class="space-y-4">';

        logs.forEach(log => {
            const actionInfo = this.getActionInfo(log.action);
            const date = new Date(log.created_at);
            
            html += `
                <div class="flex items-start p-4 border rounded-lg hover:bg-gray-50 transition">
                    <div class="w-10 h-10 rounded-full ${actionInfo.bgColor} flex items-center justify-center flex-shrink-0">
                        <i class="fas ${actionInfo.icon} ${actionInfo.textColor}"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="px-2 py-1 text-xs rounded-full ${actionInfo.badgeColor}">${actionInfo.label}</span>
                            </div>
                            <span class="text-sm text-gray-500">${this.formatDate(date)}</span>
                        </div>
                        <p class="mt-2 text-gray-900">${log.description || 'No description'}</p>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <i class="fas fa-user mr-1"></i>
                            <span>${log.user ? log.user.name : 'System'}</span>
                            ${log.ip_address ? `
                                <span class="mx-2">•</span>
                                <i class="fas fa-globe mr-1"></i>
                                <span>${log.ip_address}</span>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    },

    getActionInfo(action) {
        const actions = {
            'CREATE_ROLE': {
                icon: 'fa-plus',
                label: 'Create Role',
                bgColor: 'bg-green-100',
                textColor: 'text-green-600',
                badgeColor: 'bg-green-100 text-green-800'
            },
            'UPDATE_ROLE': {
                icon: 'fa-edit',
                label: 'Update Role',
                bgColor: 'bg-yellow-100',
                textColor: 'text-yellow-600',
                badgeColor: 'bg-yellow-100 text-yellow-800'
            },
            'DELETE_ROLE': {
                icon: 'fa-trash',
                label: 'Delete Role',
                bgColor: 'bg-red-100',
                textColor: 'text-red-600',
                badgeColor: 'bg-red-100 text-red-800'
            },
            'ASSIGN_PERMISSIONS_TO_ROLE': {
                icon: 'fa-key',
                label: 'Assign Permissions',
                bgColor: 'bg-blue-100',
                textColor: 'text-blue-600',
                badgeColor: 'bg-blue-100 text-blue-800'
            },
            'REVOKE_PERMISSIONS_FROM_ROLE': {
                icon: 'fa-ban',
                label: 'Revoke Permissions',
                bgColor: 'bg-orange-100',
                textColor: 'text-orange-600',
                badgeColor: 'bg-orange-100 text-orange-800'
            },
            'SYNC_ROLE_PERMISSIONS': {
                icon: 'fa-sync',
                label: 'Sync Permissions',
                bgColor: 'bg-purple-100',
                textColor: 'text-purple-600',
                badgeColor: 'bg-purple-100 text-purple-800'
            },
            'USER_ROLE_CHANGED': {
                icon: 'fa-user-cog',
                label: 'User Role Changed',
                bgColor: 'bg-indigo-100',
                textColor: 'text-indigo-600',
                badgeColor: 'bg-indigo-100 text-indigo-800'
            },
            'ASSIGN_USER_ROLE': {
                icon: 'fa-user-plus',
                label: 'Assign User Role',
                bgColor: 'bg-teal-100',
                textColor: 'text-teal-600',
                badgeColor: 'bg-teal-100 text-teal-800'
            }
        };

        return actions[action] || {
            icon: 'fa-info',
            label: action,
            bgColor: 'bg-gray-100',
            textColor: 'text-gray-600',
            badgeColor: 'bg-gray-100 text-gray-800'
        };
    },

    formatDate(date) {
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    updatePagination(pagination) {
        this.totalPages = pagination.last_page;
        
        document.getElementById('historyShowing').textContent = 
            Math.min(pagination.per_page * pagination.current_page, pagination.total);
        document.getElementById('historyTotal').textContent = pagination.total;
        document.getElementById('historyPageInfo').textContent = 
            `Page ${pagination.current_page} of ${pagination.last_page}`;
        
        document.getElementById('historyPrevBtn').disabled = pagination.current_page === 1;
        document.getElementById('historyNextBtn').disabled = !pagination.has_more;
    },

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadHistory();
        }
    },

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.loadHistory();
        }
    },

    clearFilters() {
        document.getElementById('historyActionFilter').value = '';
        document.getElementById('historyStartDate').value = '';
        document.getElementById('historyEndDate').value = '';
        this.currentPage = 1;
        this.loadHistory();
    }
};
</script>
