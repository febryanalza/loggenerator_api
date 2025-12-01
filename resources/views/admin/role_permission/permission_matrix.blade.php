<!-- Tab Content: Permission Matrix -->
<div id="tab-matrix" class="tab-content hidden">
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-th text-purple-600 mr-2"></i>
                    Permission Matrix
                </h3>
                <p class="text-sm text-gray-500 mt-1">Visual overview of role vs permission assignments</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <button onclick="PermissionMatrixManager.loadMatrix()" 
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center space-x-6 mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
                <div class="w-6 h-6 bg-green-500 rounded flex items-center justify-center">
                    <i class="fas fa-check text-white text-xs"></i>
                </div>
                <span class="ml-2 text-sm text-gray-600">Has Permission</span>
            </div>
            <div class="flex items-center">
                <div class="w-6 h-6 bg-gray-200 rounded flex items-center justify-center">
                    <i class="fas fa-times text-gray-400 text-xs"></i>
                </div>
                <span class="ml-2 text-sm text-gray-600">No Permission</span>
            </div>
            <div class="flex items-center">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">System Role</span>
                <span class="ml-2 text-sm text-gray-600">Built-in role</span>
            </div>
        </div>

        <!-- Matrix Container -->
        <div id="matrixContainer" class="overflow-x-auto">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-purple-600 text-2xl"></i>
                <p class="mt-2 text-gray-500">Loading permission matrix...</p>
            </div>
        </div>
    </div>

    <!-- Quick Edit Panel -->
    <div id="quickEditPanel" class="hidden bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-900">
                <i class="fas fa-edit text-yellow-500 mr-2"></i>
                Quick Edit: <span id="quickEditRoleName" class="text-purple-600"></span>
            </h4>
            <button onclick="PermissionMatrixManager.closeQuickEdit()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="quickEditContent" class="max-h-64 overflow-y-auto">
            <!-- Permission toggles will be loaded here -->
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="PermissionMatrixManager.saveQuickEdit()" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
// Permission Matrix Manager
const PermissionMatrixManager = {
    matrixData: null,
    currentEditRole: null,

    init() {
        this.loadMatrix();
    },

    async loadMatrix() {
        const container = document.getElementById('matrixContainer');
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-purple-600 text-2xl"></i>
                <p class="mt-2 text-gray-500">Loading permission matrix...</p>
            </div>
        `;

        try {
            const response = await fetch('/api/roles/matrix', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load matrix');

            const result = await response.json();
            
            if (result.success) {
                this.matrixData = result.data;
                this.renderMatrix();
            }
        } catch (error) {
            console.error('Failed to load matrix:', error);
            container.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <p class="mt-2">Failed to load permission matrix</p>
                </div>
            `;
        }
    },

    renderMatrix() {
        const container = document.getElementById('matrixContainer');
        const { roles, permissions, matrix } = this.matrixData;

        if (!roles.length || !permissions.length) {
            container.innerHTML = '<p class="text-center py-8 text-gray-500">No data available</p>';
            return;
        }

        // Group permissions by category for better display
        const groupedPerms = {};
        permissions.forEach(p => {
            const cat = p.category || 'other';
            if (!groupedPerms[cat]) groupedPerms[cat] = [];
            groupedPerms[cat].push(p);
        });

        let html = `
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="sticky left-0 bg-gray-100 px-4 py-3 text-left text-sm font-semibold text-gray-700 border z-10">
                            Role / Permission
                        </th>
        `;

        // Permission headers grouped by category
        for (const [category, perms] of Object.entries(groupedPerms)) {
            perms.forEach(perm => {
                html += `
                    <th class="matrix-cell px-2 py-2 border bg-gray-50" title="${perm.name}">
                        <div class="matrix-header text-gray-600">${this.formatPermName(perm.name)}</div>
                    </th>
                `;
            });
        }

        html += `</tr></thead><tbody>`;

        // Role rows
        matrix.forEach(row => {
            const roleClass = row.is_system ? 'bg-blue-50' : 'bg-white';
            html += `
                <tr class="${roleClass} hover:bg-gray-50">
                    <td class="sticky left-0 ${roleClass} px-4 py-3 border font-medium text-gray-900 z-10">
                        <div class="flex items-center">
                            <span>${row.role_name}</span>
                            ${row.is_system ? '<span class="ml-2 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">System</span>' : ''}
                        </div>
                    </td>
            `;

            // Permission cells
            for (const [category, perms] of Object.entries(groupedPerms)) {
                perms.forEach(perm => {
                    const permData = row.permissions.find(p => p.permission_id === perm.id);
                    const hasPermission = permData ? permData.has_permission : false;
                    
                    html += `
                        <td class="matrix-cell px-2 py-2 border text-center">
                            <button onclick="PermissionMatrixManager.togglePermission(${row.role_id}, ${perm.id}, ${hasPermission})"
                                class="w-6 h-6 rounded ${hasPermission ? 'bg-green-500' : 'bg-gray-200'} flex items-center justify-center mx-auto hover:opacity-80 transition"
                                title="${hasPermission ? 'Click to revoke' : 'Click to grant'}">
                                <i class="fas ${hasPermission ? 'fa-check text-white' : 'fa-times text-gray-400'} text-xs"></i>
                            </button>
                        </td>
                    `;
                });
            }

            html += `</tr>`;
        });

        html += `</tbody></table>`;
        container.innerHTML = html;
    },

    formatPermName(name) {
        // Remove category prefix and format nicely
        const parts = name.split('.');
        if (parts.length > 1) {
            return parts.slice(1).join(' ');
        }
        return name;
    },

    async togglePermission(roleId, permissionId, currentState) {
        try {
            const endpoint = currentState ? '/api/roles/revoke-permissions' : '/api/roles/assign-permissions';
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    role_id: roleId,
                    permission_ids: [permissionId]
                })
            });

            const result = await response.json();
            
            if (result.success) {
                RolePermissionManager.showToast(
                    currentState ? 'Permission revoked' : 'Permission granted', 
                    'success'
                );
                this.loadMatrix(); // Refresh the matrix
            } else {
                RolePermissionManager.showToast(result.message || 'Failed to update permission', 'error');
            }
        } catch (error) {
            console.error('Failed to toggle permission:', error);
            RolePermissionManager.showToast('Failed to update permission', 'error');
        }
    },

    openQuickEdit(roleId, roleName) {
        this.currentEditRole = roleId;
        document.getElementById('quickEditRoleName').textContent = roleName;
        document.getElementById('quickEditPanel').classList.remove('hidden');
        
        // Load current permissions for the role
        const roleData = this.matrixData.matrix.find(r => r.role_id === roleId);
        if (roleData) {
            const content = document.getElementById('quickEditContent');
            let html = '<div class="space-y-2">';
            
            this.matrixData.permissions.forEach(perm => {
                const permData = roleData.permissions.find(p => p.permission_id === perm.id);
                const hasPermission = permData ? permData.has_permission : false;
                
                html += `
                    <label class="flex items-center p-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" value="${perm.id}" ${hasPermission ? 'checked' : ''}
                            class="quick-edit-perm rounded text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">${perm.name}</span>
                    </label>
                `;
            });
            
            html += '</div>';
            content.innerHTML = html;
        }
    },

    closeQuickEdit() {
        document.getElementById('quickEditPanel').classList.add('hidden');
        this.currentEditRole = null;
    },

    async saveQuickEdit() {
        if (!this.currentEditRole) return;

        const selectedPermissions = Array.from(document.querySelectorAll('.quick-edit-perm:checked'))
            .map(cb => parseInt(cb.value));

        RolePermissionManager.showLoading();

        try {
            const response = await fetch('/api/roles/sync-permissions', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    role_id: this.currentEditRole,
                    permission_ids: selectedPermissions
                })
            });

            const result = await response.json();
            
            if (result.success) {
                RolePermissionManager.showToast('Permissions updated successfully', 'success');
                this.closeQuickEdit();
                this.loadMatrix();
            } else {
                RolePermissionManager.showToast(result.message || 'Failed to update permissions', 'error');
            }
        } catch (error) {
            console.error('Failed to save permissions:', error);
            RolePermissionManager.showToast('Failed to update permissions', 'error');
        } finally {
            RolePermissionManager.hideLoading();
        }
    }
};
</script>
