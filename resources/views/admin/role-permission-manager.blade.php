@extends('admin.layout')

@section('title', 'Role & Permission Manager')
@section('page-title', 'Role & Permission Manager')
@section('page-description', 'Kelola role, permission, dan hak akses pengguna')

@section('content')
    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto" id="roleTabs" aria-label="Tabs">
                <button onclick="RolePermissionManager.switchTab('roles')" id="tab-btn-roles"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-purple-500 text-purple-600 whitespace-nowrap">
                    <i class="fas fa-user-tag mr-2"></i>
                    Roles List
                </button>
                <button onclick="RolePermissionManager.switchTab('create-role')" id="tab-btn-create-role"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Custom Role
                </button>
                <button onclick="RolePermissionManager.switchTab('matrix')" id="tab-btn-matrix"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-th mr-2"></i>
                    Permission Matrix
                </button>
                <button onclick="RolePermissionManager.switchTab('history')" id="tab-btn-history"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-history mr-2"></i>
                    Assignment History
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Contents -->
    <div id="tab-contents">
        @include('admin.role_permission.roles_list')
        @include('admin.role_permission.create_role')
        @include('admin.role_permission.permission_matrix')
        @include('admin.role_permission.assignment_history')
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <i class="fas fa-spinner fa-spin text-purple-600 text-2xl"></i>
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

    <!-- Edit Role Modal -->
    <div id="editRoleModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-600 to-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Role
                    </h3>
                    <button onclick="RolePermissionManager.closeEditModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[70vh]">
                <form id="editRoleForm">
                    <input type="hidden" id="editRoleId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                        <input type="text" id="editRoleName" 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter role name">
                        <p id="editRoleNameNote" class="mt-1 text-sm text-gray-500 hidden">
                            <i class="fas fa-lock mr-1"></i> System role names cannot be changed
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                        <div id="editRolePermissions" class="max-h-64 overflow-y-auto border rounded-lg p-4 space-y-2">
                            <!-- Permissions will be loaded here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
                <button onclick="RolePermissionManager.closeEditModal()" 
                    class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Cancel
                </button>
                <button onclick="RolePermissionManager.saveRoleEdit()" 
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- View Role Users Modal -->
    <div id="roleUsersModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-blue-600 to-indigo-600">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white" id="roleUsersTitle">
                        <i class="fas fa-users mr-2"></i>
                        Users with Role
                    </h3>
                    <button onclick="RolePermissionManager.closeUsersModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div id="roleUsersList">
                    <!-- Users will be loaded here -->
                </div>
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
                <button onclick="RolePermissionManager.closeUsersModal()" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/role-permission-manager.js') }}"></script>
@endpush

@push('styles')
<style>
    /* Custom scrollbar for tabs on mobile */
    #roleTabs::-webkit-scrollbar {
        height: 4px;
    }
    
    #roleTabs::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    /* Permission matrix styles */
    .matrix-cell {
        min-width: 40px;
        text-align: center;
    }

    .matrix-header {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: nowrap;
        padding: 8px 4px;
        font-size: 11px;
    }
</style>
@endpush
