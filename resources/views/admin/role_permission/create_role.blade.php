<!-- Tab Content: Create Custom Role -->
<div id="tab-create-role" class="tab-content hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">
                    <i class="fas fa-plus-circle text-purple-600 mr-2"></i>
                    Create New Custom Role
                </h3>

                <form id="createRoleForm" onsubmit="CreateRoleManager.submitForm(event)">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="newRoleName" name="name" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="e.g., Content Editor, Report Viewer">
                        <p class="mt-1 text-sm text-gray-500">Choose a descriptive name for the role</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="newRoleDescription" name="description" rows="2"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Brief description of this role's purpose"></textarea>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-4">
                            Assign Permissions
                        </label>
                        
                        <!-- Quick Select -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <button type="button" onclick="CreateRoleManager.selectAll()" 
                                class="px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200">
                                Select All
                            </button>
                            <button type="button" onclick="CreateRoleManager.selectNone()" 
                                class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                Select None
                            </button>
                        </div>

                        <!-- Permissions by Category -->
                        <div id="permissionsContainer" class="space-y-4 max-h-96 overflow-y-auto border rounded-lg p-4">
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin text-purple-600 text-xl"></i>
                                <p class="text-sm text-gray-500 mt-2">Loading permissions...</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="CreateRoleManager.resetForm()" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Reset
                        </button>
                        <button type="submit" 
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Tips Card -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h4 class="font-semibold text-gray-900 mb-4">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Tips
                </h4>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        <span>Use descriptive role names that reflect the user's function</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        <span>Assign only the permissions needed for the role's tasks</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        <span>Review assigned permissions periodically for security</span>
                    </li>
                </ul>
            </div>

            <!-- System Roles Info -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h4 class="font-semibold text-gray-900 mb-4">
                    <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                    System Roles
                </h4>
                <p class="text-sm text-gray-600 mb-4">
                    The following roles are built-in and cannot be deleted:
                </p>
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
                        <span class="text-sm font-medium text-red-800">Super Admin</span>
                        <span class="text-xs text-red-600">Full Access</span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-orange-50 rounded-lg">
                        <span class="text-sm font-medium text-orange-800">Admin</span>
                        <span class="text-xs text-orange-600">Administrative</span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-blue-50 rounded-lg">
                        <span class="text-sm font-medium text-blue-800">Manager</span>
                        <span class="text-xs text-blue-600">Management</span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg">
                        <span class="text-sm font-medium text-green-800">Institution Admin</span>
                        <span class="text-xs text-green-600">Institution</span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-800">User</span>
                        <span class="text-xs text-gray-600">Basic</span>
                    </div>
                </div>
            </div>

            <!-- Selected Count -->
            <div class="bg-purple-50 rounded-xl border border-purple-200 p-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-purple-800">Selected Permissions</span>
                    <span id="selectedPermCount" class="text-2xl font-bold text-purple-600">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Create Role Manager
const CreateRoleManager = {
    permissions: [],
    groupedPermissions: {},

    init() {
        this.loadPermissions();
    },

    async loadPermissions() {
        const container = document.getElementById('permissionsContainer');
        
        try {
            const response = await fetch('/api/roles/permissions', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load permissions');

            const result = await response.json();
            
            if (result.success) {
                this.permissions = result.data.permissions;
                this.groupedPermissions = result.data.grouped;
                this.renderPermissions();
            }
        } catch (error) {
            console.error('Failed to load permissions:', error);
            container.innerHTML = `
                <div class="text-center py-4 text-red-500">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <p class="text-sm mt-2">Failed to load permissions</p>
                </div>
            `;
        }
    },

    renderPermissions() {
        const container = document.getElementById('permissionsContainer');
        
        if (Object.keys(this.groupedPermissions).length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">No permissions available</p>';
            return;
        }

        let html = '';
        for (const [category, permissions] of Object.entries(this.groupedPermissions)) {
            html += `
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-medium text-gray-900 capitalize">
                            <i class="fas fa-folder text-purple-500 mr-2"></i>
                            ${category}
                        </h5>
                        <button type="button" onclick="CreateRoleManager.toggleCategory('${category}')" 
                            class="text-xs text-purple-600 hover:text-purple-800">
                            Toggle All
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        ${permissions.map(perm => `
                            <label class="flex items-center p-2 bg-white rounded border hover:border-purple-300 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="${perm.id}" 
                                    data-category="${category}"
                                    class="permission-checkbox rounded text-purple-600 focus:ring-purple-500"
                                    onchange="CreateRoleManager.updateCount()">
                                <span class="ml-2 text-sm text-gray-700">${perm.name}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    },

    toggleCategory(category) {
        const checkboxes = document.querySelectorAll(`input[data-category="${category}"]`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => cb.checked = !allChecked);
        this.updateCount();
    },

    selectAll() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
        this.updateCount();
    },

    selectNone() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        this.updateCount();
    },

    updateCount() {
        const count = document.querySelectorAll('.permission-checkbox:checked').length;
        document.getElementById('selectedPermCount').textContent = count;
    },

    resetForm() {
        document.getElementById('createRoleForm').reset();
        this.selectNone();
    },

    async submitForm(event) {
        event.preventDefault();

        const name = document.getElementById('newRoleName').value.trim();
        if (!name) {
            RolePermissionManager.showToast('Please enter a role name', 'error');
            return;
        }

        const selectedPermissions = Array.from(document.querySelectorAll('.permission-checkbox:checked'))
            .map(cb => parseInt(cb.value));

        RolePermissionManager.showLoading();

        try {
            const response = await fetch('/api/roles', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    permissions: selectedPermissions
                })
            });

            const result = await response.json();

            if (result.success) {
                RolePermissionManager.showToast(`Role "${name}" created successfully`, 'success');
                this.resetForm();
                
                // Refresh roles list if on that tab
                if (typeof RolesListManager !== 'undefined') {
                    RolesListManager.loadRoles();
                    RolesListManager.loadStatistics();
                }
            } else {
                RolePermissionManager.showToast(result.message || 'Failed to create role', 'error');
            }
        } catch (error) {
            console.error('Failed to create role:', error);
            RolePermissionManager.showToast('Failed to create role', 'error');
        } finally {
            RolePermissionManager.hideLoading();
        }
    }
};
</script>
