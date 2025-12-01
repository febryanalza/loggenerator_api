<!-- Tab Content: Roles List -->
<div id="tab-roles" class="tab-content">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Roles</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-total-roles">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-tag text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">System Roles</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-system-roles">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Custom Roles</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-custom-roles">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Permissions</p>
                    <p class="text-3xl font-bold text-gray-800" id="stat-total-permissions">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-key text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Cari Role
                </label>
                <input type="text" id="searchRole" placeholder="Cari berdasarkan nama role..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    onkeyup="RolesListManager.debounceSearch()">
            </div>
            <div>
                <button onclick="RolesListManager.loadRoles()" 
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody" class="divide-y divide-gray-200">
                    <!-- Roles will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span id="rolesShowing">0</span> of <span id="rolesTotal">0</span> roles
            </div>
            <div class="flex items-center space-x-2">
                <button id="rolesPrevBtn" onclick="RolesListManager.prevPage()" 
                    class="px-3 py-1 border rounded text-gray-600 hover:bg-gray-100 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span id="rolesPageInfo" class="text-sm text-gray-600">Page 1 of 1</span>
                <button id="rolesNextBtn" onclick="RolesListManager.nextPage()" 
                    class="px-3 py-1 border rounded text-gray-600 hover:bg-gray-100 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Role Details Drawer -->
    <div id="roleDetailsDrawer" class="hidden fixed inset-y-0 right-0 w-96 bg-white shadow-2xl z-40 transform transition-transform duration-300">
        <div class="h-full flex flex-col">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-600 to-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white" id="drawerRoleName">Role Details</h3>
                    <button onclick="RolesListManager.closeDrawer()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div id="roleDetailsContent">
                    <!-- Role details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Roles List Manager
const RolesListManager = {
    currentPage: 1,
    perPage: 10,
    totalPages: 1,
    searchTimeout: null,

    init() {
        this.loadStatistics();
        this.loadRoles();
    },

    async loadStatistics() {
        try {
            const response = await fetch('/api/roles/statistics', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    document.getElementById('stat-total-roles').textContent = result.data.total_roles || 0;
                    document.getElementById('stat-system-roles').textContent = result.data.system_roles || 0;
                    document.getElementById('stat-custom-roles').textContent = result.data.custom_roles || 0;
                    document.getElementById('stat-total-permissions').textContent = result.data.total_permissions || 0;
                }
            }
        } catch (error) {
            console.error('Failed to load statistics:', error);
        }
    },

    async loadRoles() {
        const search = document.getElementById('searchRole').value;
        const tbody = document.getElementById('rolesTableBody');
        
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center">
                    <i class="fas fa-spinner fa-spin text-purple-600 text-2xl"></i>
                    <p class="mt-2 text-gray-500">Loading roles...</p>
                </td>
            </tr>
        `;

        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...(search && { search })
            });

            const response = await fetch(`/api/roles?${params}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch roles');

            const result = await response.json();
            
            if (result.success) {
                this.renderRoles(result.data);
                this.updatePagination(result.pagination);
            }
        } catch (error) {
            console.error('Failed to load roles:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-red-500">
                        <i class="fas fa-exclamation-circle text-2xl"></i>
                        <p class="mt-2">Failed to load roles</p>
                    </td>
                </tr>
            `;
        }
    },

    renderRoles(roles) {
        const tbody = document.getElementById('rolesTableBody');
        
        if (!roles || roles.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-user-tag text-4xl mb-2"></i>
                        <p>No roles found</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = roles.map(role => `
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ${this.getRoleColor(role.name)}">
                            <i class="fas fa-user-shield text-white"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">${role.name}</p>
                            <p class="text-sm text-gray-500">${role.guard_name}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    ${role.is_system 
                        ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"><i class="fas fa-lock mr-1"></i>System</span>'
                        : '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"><i class="fas fa-user-cog mr-1"></i>Custom</span>'
                    }
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                        ${role.permissions_count} permissions
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    ${new Date(role.created_at).toLocaleDateString('id-ID')}
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick="RolesListManager.viewRole(${role.id})" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="RolePermissionManager.editRole(${role.id})" 
                            class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="RolesListManager.viewUsers(${role.id})" 
                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="View Users">
                            <i class="fas fa-users"></i>
                        </button>
                        ${!role.is_system ? `
                            <button onclick="RolesListManager.deleteRole(${role.id}, '${role.name}')" 
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    },

    getRoleColor(roleName) {
        const colors = {
            'Super Admin': 'bg-red-500',
            'Admin': 'bg-orange-500',
            'Manager': 'bg-blue-500',
            'Institution Admin': 'bg-green-500',
            'User': 'bg-gray-500'
        };
        return colors[roleName] || 'bg-purple-500';
    },

    updatePagination(pagination) {
        this.totalPages = pagination.last_page;
        
        document.getElementById('rolesShowing').textContent = pagination.per_page * pagination.current_page;
        document.getElementById('rolesTotal').textContent = pagination.total;
        document.getElementById('rolesPageInfo').textContent = `Page ${pagination.current_page} of ${pagination.last_page}`;
        
        document.getElementById('rolesPrevBtn').disabled = pagination.current_page === 1;
        document.getElementById('rolesNextBtn').disabled = !pagination.has_more;
    },

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadRoles();
        }
    },

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.loadRoles();
        }
    },

    debounceSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.currentPage = 1;
            this.loadRoles();
        }, 300);
    },

    async viewRole(id) {
        const drawer = document.getElementById('roleDetailsDrawer');
        const content = document.getElementById('roleDetailsContent');
        
        drawer.classList.remove('hidden');
        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i></div>';

        try {
            const response = await fetch(`/api/roles/${id}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch role');

            const result = await response.json();
            
            if (result.success) {
                const role = result.data;
                document.getElementById('drawerRoleName').textContent = role.name;
                
                content.innerHTML = `
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Role Information</h4>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium">${role.name}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Guard:</span>
                                    <span class="font-medium">${role.guard_name}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type:</span>
                                    <span class="font-medium">${role.is_system ? 'System Role' : 'Custom Role'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Users:</span>
                                    <span class="font-medium">${role.users_count || 0}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Permissions (${role.permissions.length})</h4>
                            <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                                ${role.permissions.length > 0 
                                    ? role.permissions.map(p => `
                                        <span class="inline-block px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full m-1">
                                            ${p.name}
                                        </span>
                                    `).join('')
                                    : '<p class="text-gray-500 text-sm">No permissions assigned</p>'
                                }
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Failed to load role details:', error);
            content.innerHTML = '<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-circle text-2xl"></i><p class="mt-2">Failed to load details</p></div>';
        }
    },

    closeDrawer() {
        document.getElementById('roleDetailsDrawer').classList.add('hidden');
    },

    async viewUsers(roleId) {
        RolePermissionManager.showUsersModal(roleId);
    },

    async deleteRole(id, name) {
        if (!confirm(`Are you sure you want to delete the role "${name}"?`)) return;

        try {
            const response = await fetch(`/api/roles/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                RolePermissionManager.showToast('Role deleted successfully', 'success');
                this.loadRoles();
                this.loadStatistics();
            } else {
                RolePermissionManager.showToast(result.message || 'Failed to delete role', 'error');
            }
        } catch (error) {
            console.error('Failed to delete role:', error);
            RolePermissionManager.showToast('Failed to delete role', 'error');
        }
    }
};
</script>
