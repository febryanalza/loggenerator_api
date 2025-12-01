/**
 * Role & Permission Manager JavaScript Module
 * Main controller for role and permission management
 */

// ========================================
// GLOBAL STATE & CONFIG
// ========================================
const API_BASE = '/api';
let authToken = localStorage.getItem('admin_token');
let allPermissions = [];

// ========================================
// MAIN ROLE PERMISSION MANAGER
// ========================================
const RolePermissionManager = {
    currentTab: 'roles',
    initialized: {
        roles: false,
        'create-role': false,
        matrix: false,
        history: false
    },

    init() {
        // Check auth
        if (!authToken) {
            window.location.href = '/login';
            return;
        }

        // Initialize first tab
        this.switchTab('roles');
    },

    switchTab(tabName) {
        this.currentTab = tabName;
        
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Show selected tab content
        const selectedContent = document.getElementById(`tab-${tabName}`);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }
        
        // Update tab buttons styling
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-purple-500', 'text-purple-600');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });
        
        const selectedBtn = document.getElementById(`tab-btn-${tabName}`);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            selectedBtn.classList.add('border-purple-500', 'text-purple-600');
        }

        // Initialize tab if not done
        if (!this.initialized[tabName]) {
            this.initializeTab(tabName);
            this.initialized[tabName] = true;
        }
    },

    initializeTab(tabName) {
        switch(tabName) {
            case 'roles':
                if (typeof RolesListManager !== 'undefined') {
                    RolesListManager.init();
                }
                break;
            case 'create-role':
                if (typeof CreateRoleManager !== 'undefined') {
                    CreateRoleManager.init();
                }
                break;
            case 'matrix':
                if (typeof PermissionMatrixManager !== 'undefined') {
                    PermissionMatrixManager.init();
                }
                break;
            case 'history':
                if (typeof AssignmentHistoryManager !== 'undefined') {
                    AssignmentHistoryManager.init();
                }
                break;
        }
    },

    // ========================================
    // LOADING & TOAST
    // ========================================
    showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    },

    hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    },

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        
        if (type === 'success') {
            toastIcon.className = 'fas fa-check-circle text-2xl text-green-500';
        } else if (type === 'error') {
            toastIcon.className = 'fas fa-times-circle text-2xl text-red-500';
        } else {
            toastIcon.className = 'fas fa-info-circle text-2xl text-blue-500';
        }
        
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    },

    // ========================================
    // EDIT ROLE MODAL
    // ========================================
    async editRole(roleId) {
        this.showLoading();
        
        try {
            // Fetch role details
            const roleResponse = await fetch(`${API_BASE}/roles/${roleId}`, {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                }
            });

            if (!roleResponse.ok) throw new Error('Failed to fetch role');
            const roleResult = await roleResponse.json();

            // Fetch all permissions if not loaded
            if (allPermissions.length === 0) {
                const permResponse = await fetch(`${API_BASE}/roles/permissions`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (permResponse.ok) {
                    const permResult = await permResponse.json();
                    allPermissions = permResult.data.permissions || [];
                }
            }

            const role = roleResult.data;
            const rolePermIds = role.permissions.map(p => p.id);

            // Populate modal
            document.getElementById('editRoleId').value = role.id;
            document.getElementById('editRoleName').value = role.name;
            
            // Handle system role name editing
            const nameInput = document.getElementById('editRoleName');
            const nameNote = document.getElementById('editRoleNameNote');
            if (role.is_system) {
                nameInput.disabled = true;
                nameInput.classList.add('bg-gray-100');
                nameNote.classList.remove('hidden');
            } else {
                nameInput.disabled = false;
                nameInput.classList.remove('bg-gray-100');
                nameNote.classList.add('hidden');
            }

            // Render permissions
            const permContainer = document.getElementById('editRolePermissions');
            if (allPermissions.length > 0) {
                // Group permissions
                const grouped = {};
                allPermissions.forEach(p => {
                    const cat = p.name.split('.')[0] || 'other';
                    if (!grouped[cat]) grouped[cat] = [];
                    grouped[cat].push(p);
                });

                let html = '';
                for (const [category, perms] of Object.entries(grouped)) {
                    html += `
                        <div class="mb-4">
                            <h5 class="font-medium text-gray-700 capitalize mb-2">${category}</h5>
                            <div class="grid grid-cols-2 gap-2">
                                ${perms.map(perm => `
                                    <label class="flex items-center p-2 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer">
                                        <input type="checkbox" value="${perm.id}" 
                                            ${rolePermIds.includes(perm.id) ? 'checked' : ''}
                                            class="edit-perm-checkbox rounded text-purple-600 focus:ring-purple-500">
                                        <span class="ml-2 text-sm text-gray-700">${perm.name}</span>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                permContainer.innerHTML = html;
            } else {
                permContainer.innerHTML = '<p class="text-gray-500">No permissions available</p>';
            }

            // Show modal
            document.getElementById('editRoleModal').classList.remove('hidden');
            
        } catch (error) {
            console.error('Failed to load role for editing:', error);
            this.showToast('Failed to load role details', 'error');
        } finally {
            this.hideLoading();
        }
    },

    closeEditModal() {
        document.getElementById('editRoleModal').classList.add('hidden');
    },

    async saveRoleEdit() {
        const roleId = document.getElementById('editRoleId').value;
        const roleName = document.getElementById('editRoleName').value.trim();
        const selectedPermissions = Array.from(document.querySelectorAll('.edit-perm-checkbox:checked'))
            .map(cb => parseInt(cb.value));

        if (!roleName) {
            this.showToast('Role name is required', 'error');
            return;
        }

        this.showLoading();

        try {
            const response = await fetch(`${API_BASE}/roles/${roleId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: roleName,
                    permissions: selectedPermissions
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showToast('Role updated successfully', 'success');
                this.closeEditModal();
                
                // Refresh current tab
                if (typeof RolesListManager !== 'undefined') {
                    RolesListManager.loadRoles();
                }
                if (typeof PermissionMatrixManager !== 'undefined' && this.currentTab === 'matrix') {
                    PermissionMatrixManager.loadMatrix();
                }
            } else {
                this.showToast(result.message || 'Failed to update role', 'error');
            }
        } catch (error) {
            console.error('Failed to update role:', error);
            this.showToast('Failed to update role', 'error');
        } finally {
            this.hideLoading();
        }
    },

    // ========================================
    // ROLE USERS MODAL
    // ========================================
    async showUsersModal(roleId) {
        const modal = document.getElementById('roleUsersModal');
        const list = document.getElementById('roleUsersList');
        
        modal.classList.remove('hidden');
        list.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i></div>';

        try {
            const response = await fetch(`${API_BASE}/roles/${roleId}/users`, {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch users');
            const result = await response.json();

            if (result.success) {
                document.getElementById('roleUsersTitle').innerHTML = `
                    <i class="fas fa-users mr-2"></i>
                    Users with "${result.data.role}" Role (${result.data.users_count})
                `;

                if (result.data.users.length === 0) {
                    list.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-user-slash text-4xl mb-2"></i>
                            <p>No users assigned to this role</p>
                        </div>
                    `;
                } else {
                    list.innerHTML = `
                        <div class="divide-y">
                            ${result.data.users.map(user => `
                                <div class="py-3 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                            <i class="fas fa-user text-purple-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-medium text-gray-900">${user.name}</p>
                                            <p class="text-sm text-gray-500">${user.email}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full ${user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${user.status}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Failed to load users:', error);
            list.innerHTML = `
                <div class="text-center py-4 text-red-500">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <p class="mt-2">Failed to load users</p>
                </div>
            `;
        }
    },

    closeUsersModal() {
        document.getElementById('roleUsersModal').classList.add('hidden');
    }
};

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    RolePermissionManager.init();
});
