<!-- Change Role Modal Component -->
<div id="roleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Change User Role</h3>
        </div>
        
        <form id="roleForm" class="p-6">
            <input type="hidden" id="roleUserId">
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">User: <span id="roleUserName" class="font-semibold"></span></p>
                <p class="text-sm text-gray-600 mb-4">Current Role: <span id="roleUserCurrentRole" class="font-semibold"></span></p>
                
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    New Role <span class="text-red-500">*</span>
                </label>
                <select id="newRole" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        onchange="handleRoleSelectionChange(this.value)">
                    <option value="">Select New Role</option>
                </select>
            </div>

            <div id="roleInstitutionGroup" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Institution <span class="text-red-500">*</span>
                </label>
                <select id="roleInstitutionSelect" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Pilih Institusi</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Required when assigning Institution Admin.</p>
            </div>
            
            <div id="roleFormError" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600"></p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" 
                        onclick="closeRoleModal()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>
