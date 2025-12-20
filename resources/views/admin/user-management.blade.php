@extends('admin.layout')

@section('title', 'User Management')
@section('page-title', 'Manajemen Pengguna')
@section('page-description', 'Kelola akun pengguna, role, dan permissions')

@section('content')
<!-- Loading Indicator -->
<div id="pageLoading" class="text-center py-12">
    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
    <p class="text-gray-600 mt-4">Loading user data...</p>
</div>

<!-- Main Content -->
<div id="mainContent" class="hidden">
    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Search users by name or email..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Filter by Role -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Filter Role:</label>
                <select id="roleFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                </select>
            </div>
            
            <!-- Refresh Button -->
            <button onclick="refreshUserData()" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors"
                    title="Refresh data from server">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
            
            <!-- Add User Button -->
            <button onclick="openCreateUserModal()" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
                <i class="fas fa-user-plus"></i>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Super Admin</p>
                    <p class="text-2xl font-bold text-gray-800" id="countSuperAdmin">0</p>
                </div>
                <i class="fas fa-user-shield text-red-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Admin</p>
                    <p class="text-2xl font-bold text-gray-800" id="countAdmin">0</p>
                </div>
                <i class="fas fa-user-tie text-orange-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Manager</p>
                    <p class="text-2xl font-bold text-gray-800" id="countManager">0</p>
                </div>
                <i class="fas fa-user-cog text-blue-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Institution Admin</p>
                    <p class="text-2xl font-bold text-gray-800" id="countInstitutionAdmin">0</p>
                </div>
                <i class="fas fa-building text-green-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">User</p>
                    <p class="text-2xl font-bold text-gray-800" id="countUser">0</p>
                </div>
                <i class="fas fa-user text-purple-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Table rows will be inserted here by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalUsers">0</span> users
                </div>
                <div class="flex gap-2" id="paginationButtons">
                    <!-- Pagination buttons will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Add New User</h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        
        <form id="userForm" class="p-6">
            <input type="hidden" id="userId" name="user_id">
            <input type="hidden" id="formMode" value="create">
            
            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="userName" 
                           name="name"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Enter full name">
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="userEmail" 
                           name="email"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="user@example.com">
                </div>
                
                <!-- Password (only for create) -->
                <div id="passwordField">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="userPassword" 
                           name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Enter password (min 8 characters)">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>
                
                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number
                    </label>
                    <input type="tel" 
                           id="userPhone" 
                           name="phone_number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="+62 812 3456 7890">
                </div>
                
                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="userRole" 
                            name="role"
                            required
                            onchange="handleRoleChange()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select Role</option>
                    </select>
                </div>
                
                <!-- Institution (only show for Institution Admin) -->
                <div id="institutionField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Institution <span class="text-red-500">*</span>
                    </label>
                    <select id="userInstitution" 
                            name="institution_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Select Institution</option>
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
            </div>
            
            <!-- Error Messages -->
            <div id="formError" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600"></p>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" 
                        onclick="closeUserModal()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        id="submitBtn"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span id="submitBtnText">Create User</span>
                </button>
            </div>
        </form>
    </div>
</div>

@include('admin.user-management.components.change-role-modal')
@include('admin.user-management.components.edit-user-modal')

<!-- Delete User Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Konfirmasi Hapus User</h3>
            <p class="text-gray-600 text-center mb-6">
                Apakah Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?
                <br><span class="text-sm text-red-600 mt-2 block">Tindakan ini tidak dapat dibatalkan!</span>
            </p>
            
            <input type="hidden" id="deleteUserId">
            
            <div id="deleteFormError" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600"></p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                    Batal
                </button>
                <button type="button" 
                        onclick="executeDeleteUser()"
                        id="deleteBtn"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                    <span id="deleteBtnText">Hapus</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allUsers = [];
let filteredUsers = [];
let currentPage = 1;
let perPage = 10;
let institutions = [];
let availableRoles = [];
const USERS_CACHE_KEY = 'users_management_cache';
const INSTITUTIONS_CACHE_KEY = 'institutions_cache';
const ROLES_CACHE_KEY = 'roles_cache';
const CACHE_DURATION = 10 * 60 * 1000; // 10 MENIT

// CACHE FUNCTIONS
function isValidCache(cacheKey) {
    const cached = localStorage.getItem(cacheKey);
    if (!cached) {
        console.log(`❌ Cache ${cacheKey} tidak ada`);
        return false;
    }
    
    try {
        const { timestamp } = JSON.parse(cached);
        const age = Date.now() - timestamp;
        const isValid = age < CACHE_DURATION;
        
        if (isValid) {
            console.log(`✅ CACHE ${cacheKey} VALID - Umur: ${Math.round(age/1000)}s`);
        } else {
            console.log(`❌ CACHE ${cacheKey} EXPIRED - Umur: ${Math.round(age/1000)}s`);
        }
        
        return isValid;
    } catch (e) {
        console.error(`❌ Cache ${cacheKey} corrupt:`, e);
        return false;
    }
}

function getCache(cacheKey) {
    if (!isValidCache(cacheKey)) return null;
    
    try {
        const cached = localStorage.getItem(cacheKey);
        const { data } = JSON.parse(cached);
        console.log(`📦 Menggunakan data dari CACHE ${cacheKey}`);
        return data;
    } catch (e) {
        console.error(`❌ Error reading cache ${cacheKey}:`, e);
        return null;
    }
}

function setCache(cacheKey, data) {
    try {
        const cacheObject = {
            data: data,
            timestamp: Date.now()
        };
        localStorage.setItem(cacheKey, JSON.stringify(cacheObject));
        console.log(`💾 Data disimpan ke CACHE ${cacheKey}, expired dalam ${CACHE_DURATION/1000/60} menit`);
    } catch (e) {
        console.error(`❌ Error saving cache ${cacheKey}:`, e);
    }
}

function clearCacheData(cacheKey) {
    if (cacheKey) {
        localStorage.removeItem(cacheKey);
        console.log(`🗑️ Cache ${cacheKey} dihapus`);
    } else {
        localStorage.removeItem(USERS_CACHE_KEY);
        localStorage.removeItem(INSTITUTIONS_CACHE_KEY);
        localStorage.removeItem(ROLES_CACHE_KEY);
        console.log('🗑️ Semua cache dihapus');
    }
}

// Initialize page
async function initUserManagement() {
    const token = localStorage.getItem('admin_token');
    
    if (!token) {
        console.error('No authentication token found');
        window.location.href = '/login';
        return;
    }
    
    try {
        // Show loading state
        const pageLoading = document.getElementById('pageLoading');
        const mainContent = document.getElementById('mainContent');
        
        if (pageLoading && mainContent) {
            // Load data in sequence to prevent race conditions
            try {
                await loadUsers();
            } catch (userError) {
                console.error('Failed to load users:', userError);
                // Continue execution even if user loading fails
            }
            
            try {
                await loadInstitutions();
            } catch (instError) {
                console.error('Failed to load institutions:', instError);
                // Continue execution even if institution loading fails
            }

            try {
                await loadRoles();
            } catch (roleError) {
                console.error('Failed to load roles:', roleError);
            }
            
            // Hide loading indicator and show main content
            pageLoading.classList.add('hidden');
            mainContent.classList.remove('hidden');
        } else {
            console.error('Missing DOM elements: pageLoading or mainContent');
            alert('Page structure error. Please contact administrator.');
        }
    } catch (error) {
        console.error('Initialization failed:', error);
        alert('Failed to load user management data. Please try again or contact support.');
        // Only redirect if serious error
        window.location.href = '/login';
    }
}

// Load users from API
async function loadUsers(forceRefresh = false) {
    const token = localStorage.getItem('admin_token');
    
    try {
        // CEK CACHE DULU
        if (!forceRefresh) {
            const cachedData = getCache(USERS_CACHE_KEY);
            if (cachedData !== null) {
                allUsers = cachedData;
                filteredUsers = [...allUsers];
                updateStats();
                renderUsersTable();
                return; // STOP - TIDAK PANGGIL API!
            }
        }
        
        // PANGGIL API
        console.log('🌐 Memanggil API users...');
        const response = await fetch('/api/admin/users?per_page=all', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to load users');
        
        const data = await response.json();
        allUsers = data.data.users || [];
        filteredUsers = [...allUsers];
        
        // SIMPAN KE CACHE
        setCache(USERS_CACHE_KEY, allUsers);
        
        updateStats();
        renderUsersTable();
        
    } catch (error) {
        console.error('Load users error:', error);
        throw error;
    }
}

// Load institutions for dropdown
async function loadInstitutions(forceRefresh = false) {
    const token = localStorage.getItem('admin_token');
    
    try {
        // CEK CACHE DULU
        if (!forceRefresh) {
            const cachedData = getCache(INSTITUTIONS_CACHE_KEY);
            if (cachedData !== null) {
                institutions = cachedData;
                populateInstitutionDropdown();
                return; // STOP - TIDAK PANGGIL API!
            }
        }
        
        // PANGGIL API
        console.log('🌐 Memanggil API institutions...');
        const response = await fetch('/api/institutions', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to load institutions');
        
        const data = await response.json();
        institutions = data.data || [];
        
        // SIMPAN KE CACHE
        setCache(INSTITUTIONS_CACHE_KEY, institutions);
        
        populateAllInstitutionDropdowns();
        
    } catch (error) {
        console.error('Load institutions error:', error);
        institutions = [];
    }
}

async function loadRoles(forceRefresh = false) {
    const token = localStorage.getItem('admin_token');

    if (!token) {
        console.warn('Tidak ada token untuk memuat roles');
        return;
    }

    if (!forceRefresh) {
        const cachedRoles = getCache(ROLES_CACHE_KEY);
        if (cachedRoles !== null) {
            availableRoles = cachedRoles;
            console.log('📦 Menggunakan data role dari cache');
            populateRoleFilter();
            populateRoleDropdowns();
            return;
        }
    }

    const endpoints = [
        '/api/institution/all-roles',
        '/api/roles?per_page=100'
    ];

    for (const url of endpoints) {
        try {
            console.log(`🌐 Memanggil API roles: ${url}`);
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                console.warn(`⚠️ Tidak dapat memuat roles dari ${url} - Status: ${response.status}`);
                continue;
            }

            const payload = await response.json();
            const rawRoles = Array.isArray(payload?.data) ? payload.data : [];

            if (!rawRoles.length) {
                console.warn(`⚠️ Response roles dari ${url} kosong`);
                continue;
            }

            availableRoles = normalizeRoleOptions(rawRoles);
            setCache(ROLES_CACHE_KEY, availableRoles);
            populateRoleFilter();
            populateRoleDropdowns();
            return;
        } catch (error) {
            console.error(`❌ Error saat memuat roles dari ${url}:`, error);
        }
    }

    console.warn('⚠️ Roles tidak dapat dimuat, menggunakan daftar kosong');
    availableRoles = [];
    populateRoleFilter();
    populateRoleDropdowns();
}

function normalizeRoleOptions(rawRoles) {
    const map = new Map();

    rawRoles.forEach(role => {
        if (!role || !role.name) {
            return;
        }

        const name = role.name;
        const label = name;

        if (!map.has(name)) {
            map.set(name, {
                id: role.id ?? null,
                name: name,
                label: label
            });
        }
    });

    return Array.from(map.values()).sort((a, b) => a.label.localeCompare(b.label));
}

function populateRoleFilter() {
    const filterSelect = document.getElementById('roleFilter');
    if (!filterSelect) {
        return;
    }

    const currentValue = filterSelect.value;
    let optionsHtml = '<option value="">All Roles</option>';

    availableRoles.forEach(role => {
        optionsHtml += `<option value="${escapeHtml(role.name)}">${escapeHtml(role.label)}</option>`;
    });

    filterSelect.innerHTML = optionsHtml;

    if (currentValue && availableRoles.some(role => role.name === currentValue)) {
        filterSelect.value = currentValue;
    }
}

function populateRoleDropdowns() {
    const createSelect = document.getElementById('userRole');
    if (createSelect) {
        const currentValue = createSelect.value;
        let optionsHtml = '<option value="">Select Role</option>';

        availableRoles.forEach(role => {
            const disabledAttr = role.name === 'Super Admin' ? ' disabled' : '';
            optionsHtml += `<option value="${escapeHtml(role.name)}"${disabledAttr}>${escapeHtml(role.label)}</option>`;
        });

        createSelect.innerHTML = optionsHtml;

        if (currentValue && availableRoles.some(role => role.name === currentValue)) {
            createSelect.value = currentValue;
        }
    }

    const updateSelect = document.getElementById('newRole');
    if (updateSelect) {
        const currentValue = updateSelect.value;
        let optionsHtml = '<option value="">Select New Role</option>';

        availableRoles.forEach(role => {
            const disabledAttr = role.name === 'Super Admin' ? ' disabled' : '';
            optionsHtml += `<option value="${escapeHtml(role.name)}"${disabledAttr}>${escapeHtml(role.label)}</option>`;
        });

        updateSelect.innerHTML = optionsHtml;

        if (currentValue && availableRoles.some(role => role.name === currentValue)) {
            updateSelect.value = currentValue;
        }
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function populateInstitutionDropdown(selectId = 'userInstitution', selectedValue = '') {
    const select = document.getElementById(selectId);
    if (!select) return;

    const currentValue = selectedValue || select.value;
    select.innerHTML = '<option value="">Pilih Institusi</option>';
    institutions.forEach(inst => {
        select.innerHTML += `<option value="${inst.id}">${escapeHtml(inst.name)}</option>`;
    });

    if (currentValue) {
        select.value = currentValue;
    }

    console.log(`Total institutions in dropdown (${selectId}):`, institutions.length);
}

function populateAllInstitutionDropdowns() {
    ['userInstitution', 'editUserInstitution', 'roleInstitutionSelect']
        .forEach(selectId => populateInstitutionDropdown(selectId));
}

function handleRoleSelectionChange(selectedRole) {
    const group = document.getElementById('roleInstitutionGroup');
    const select = document.getElementById('roleInstitutionSelect');
    if (!group || !select) return;

    if (selectedRole === 'Institution Admin') {
        group.classList.remove('hidden');
        select.required = true;
    } else {
        group.classList.add('hidden');
        select.required = false;
        select.value = '';
    }
}

// Update stats cards
function updateStats() {
    const roleCounts = {
        'Super Admin': 0,
        'Admin': 0,
        'Manager': 0,
        'Institution Admin': 0,
        'User': 0
    };
    
    allUsers.forEach(user => {
        if (user.roles && Array.isArray(user.roles)) {
            user.roles.forEach(role => {
                if (roleCounts.hasOwnProperty(role)) {
                    roleCounts[role]++;
                }
            });
        } else if (user.roles && typeof user.roles === 'string') {
            // Handle case where roles might be a comma-separated string
            const roleArray = user.roles.split(',').map(r => r.trim());
            roleArray.forEach(role => {
                if (roleCounts.hasOwnProperty(role)) {
                    roleCounts[role]++;
                }
            });
        }
    });
    
    // Safely update DOM elements
    const updateElement = (id, value) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    };
    
    updateElement('countSuperAdmin', roleCounts['Super Admin']);
    updateElement('countAdmin', roleCounts['Admin']);
    updateElement('countManager', roleCounts['Manager']);
    updateElement('countInstitutionAdmin', roleCounts['Institution Admin']);
    updateElement('countUser', roleCounts['User']);
}

// Render users table
function renderUsersTable() {
    const tbody = document.getElementById('usersTableBody');
    const startIndex = (currentPage - 1) * perPage;
    const endIndex = startIndex + perPage;
    const pageUsers = filteredUsers.slice(startIndex, endIndex);
    
    if (pageUsers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-2"></i>
                    <p>No users found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = pageUsers.map(user => {
        // Handle roles data which might come in different formats
        let roles = '-';
        if (user.roles) {
            if (Array.isArray(user.roles)) {
                roles = user.roles.join(', ');
            } else if (typeof user.roles === 'string') {
                roles = user.roles;
            }
        }
        
        const institution = user.institution ? user.institution.name : '-';
        const lastLogin = user.last_login ? new Date(user.last_login).toLocaleString('id-ID') : 'Never';
        const statusBadge = user.status === 'active' 
            ? `<button onclick="toggleUserStatus('${user.id}')" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition">Active</button>`
            : `<button onclick="toggleUserStatus('${user.id}')" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition">Inactive</button>`;
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-indigo-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">${user.name}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${user.email}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${user.phone_number || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${roles}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${institution}</td>
                <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${lastLogin}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="openEditUserModal('${user.id}')" 
                            class="text-green-600 hover:text-green-900 mr-3" 
                            title="Edit User">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="openChangeRoleModal('${user.id}')" 
                            class="text-indigo-600 hover:text-indigo-900 mr-3" 
                            title="Change Role">
                        <i class="fas fa-user-tag"></i>
                    </button>
                    <button onclick="viewUserDetails('${user.id}')" 
                            class="text-blue-600 hover:text-blue-900 mr-3" 
                            title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="confirmDeleteUser('${user.id}', '${user.name}')" 
                            class="text-red-600 hover:text-red-900" 
                            title="Delete User">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    updatePagination();
}

// Update pagination
function updatePagination() {
    const totalPages = Math.ceil(filteredUsers.length / perPage);
    const startIndex = (currentPage - 1) * perPage + 1;
    const endIndex = Math.min(currentPage * perPage, filteredUsers.length);
    
    document.getElementById('showingFrom').textContent = filteredUsers.length > 0 ? startIndex : 0;
    document.getElementById('showingTo').textContent = endIndex;
    document.getElementById('totalUsers').textContent = filteredUsers.length;
    
    const paginationButtons = document.getElementById('paginationButtons');
    let buttonsHTML = '';
    
    // Previous button
    buttonsHTML += `
        <button onclick="changePage(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded-lg ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            buttonsHTML += `
                <button onclick="changePage(${i})" 
                        class="px-3 py-1 border ${i === currentPage ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'} rounded-lg">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            buttonsHTML += '<span class="px-2">...</span>';
        }
    }
    
    // Next button
    buttonsHTML += `
        <button onclick="changePage(${currentPage + 1})" 
                ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}
                class="px-3 py-1 border border-gray-300 rounded-lg ${currentPage === totalPages || totalPages === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    paginationButtons.innerHTML = buttonsHTML;
}

// Change page
function changePage(page) {
    const totalPages = Math.ceil(filteredUsers.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderUsersTable();
}

// Search users
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    
    filteredUsers = allUsers.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(searchTerm) || 
                            user.email.toLowerCase().includes(searchTerm);
        const matchesRole = !roleFilter || user.roles.includes(roleFilter);
        return matchesSearch && matchesRole;
    });
    
    currentPage = 1;
    renderUsersTable();
});

// Filter by role
document.getElementById('roleFilter').addEventListener('change', function(e) {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = e.target.value;
    
    filteredUsers = allUsers.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(searchTerm) || 
                            user.email.toLowerCase().includes(searchTerm);
        const matchesRole = !roleFilter || user.roles.includes(roleFilter);
        return matchesSearch && matchesRole;
    });
    
    currentPage = 1;
    renderUsersTable();
});

// Open create user modal
function openCreateUserModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('formMode').value = 'create';
    document.getElementById('userId').value = '';
    document.getElementById('userForm').reset();
    document.getElementById('passwordField').classList.remove('hidden');
    document.getElementById('userPassword').required = true;
    document.getElementById('submitBtnText').textContent = 'Create User';
    document.getElementById('formError').classList.add('hidden');
    document.getElementById('institutionField').classList.add('hidden');
    populateRoleDropdowns();
    
    const modal = document.getElementById('userModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close user modal
function closeUserModal() {
    const modal = document.getElementById('userModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('userForm').reset();
}

// Handle role change
function handleRoleChange() {
    const role = document.getElementById('userRole').value;
    const institutionField = document.getElementById('institutionField');
    const institutionSelect = document.getElementById('userInstitution');
    
    if (role === 'Institution Admin') {
        institutionField.classList.remove('hidden');
        institutionSelect.required = true;
    } else {
        institutionField.classList.add('hidden');
        institutionSelect.required = false;
        institutionSelect.value = '';
    }
}

// Submit user form
document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const mode = document.getElementById('formMode').value;
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = document.getElementById('submitBtnText').textContent;
    
    // Disable button
    submitBtn.disabled = true;
    document.getElementById('submitBtnText').textContent = 'Processing...';
    
    const formData = {
        name: document.getElementById('userName').value,
        email: document.getElementById('userEmail').value,
        phone_number: document.getElementById('userPhone').value,
        role: document.getElementById('userRole').value
    };
    
    if (mode === 'create') {
        formData.password = document.getElementById('userPassword').value;
    }
    
    if (formData.role === 'Institution Admin') {
        formData.institution_id = document.getElementById('userInstitution').value;
        console.log('Institution Admin - institution_id:', formData.institution_id);
    }
    
    console.log('Form data being sent:', formData);
    
    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch('/api/admin/users', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to create user');
        }
        
        // Show success message
        showToast('User created successfully!', 'success');
        closeUserModal();
        
        // Clear cache and reload
        clearCacheData(USERS_CACHE_KEY);
        await loadUsers(true);
        
    } catch (error) {
        console.error('Create user error:', error);
        const errorDiv = document.getElementById('formError');
        errorDiv.querySelector('p').textContent = error.message;
        errorDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        document.getElementById('submitBtnText').textContent = originalBtnText;
    }
});

// Open change role modal
function openChangeRoleModal(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    populateRoleDropdowns();
    populateAllInstitutionDropdowns();
    
    let roleText = '-';
    if (user.roles) {
        if (Array.isArray(user.roles)) {
            roleText = user.roles.join(', ');
        } else if (typeof user.roles === 'string') {
            roleText = user.roles;
        }
    }
    
    document.getElementById('roleUserId').value = userId;
    document.getElementById('roleUserName').textContent = user.name;
    document.getElementById('roleUserCurrentRole').textContent = roleText;
    document.getElementById('newRole').value = '';
    document.getElementById('roleFormError').classList.add('hidden');

    // Preselect institution if user already has one
    const roleInstitutionSelect = document.getElementById('roleInstitutionSelect');
    if (roleInstitutionSelect) {
        roleInstitutionSelect.value = user.institution?.id || '';
    }

    handleRoleSelectionChange('');
    
    const modal = document.getElementById('roleModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close role modal
function closeRoleModal() {
    const modal = document.getElementById('roleModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Submit role form
document.getElementById('roleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const userId = document.getElementById('roleUserId').value;
    const newRole = document.getElementById('newRole').value;
    const institutionId = document.getElementById('roleInstitutionSelect')?.value;
    
    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`/api/admin/users/${userId}/role`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                role: newRole,
                institution_id: newRole === 'Institution Admin' ? institutionId : null
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to update role');
        }
        
        showToast('User role updated successfully!', 'success');
        closeRoleModal();
        
        // Clear cache and reload
        clearCacheData(USERS_CACHE_KEY);
        await loadUsers(true);
        
    } catch (error) {
        console.error('Update role error:', error);
        const errorDiv = document.getElementById('roleFormError');
        errorDiv.querySelector('p').textContent = error.message;
        errorDiv.classList.remove('hidden');
    }
});

// Open edit user modal
function openEditUserModal(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;

    populateAllInstitutionDropdowns();

    document.getElementById('editUserId').value = userId;
    document.getElementById('editUserName').value = user.name || '';
    document.getElementById('editUserPhone').value = user.phone_number || '';
    document.getElementById('editUserStatus').value = user.status || 'active';
    document.getElementById('editUserInstitution').value = user.institution?.id || '';
    document.getElementById('editUserFormError').classList.add('hidden');

    const modal = document.getElementById('editUserModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditUserModal() {
    const modal = document.getElementById('editUserModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('editUserForm').reset();
}

const editUserForm = document.getElementById('editUserForm');
if (editUserForm) {
    editUserForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('editUserSubmitBtn');
        const submitText = document.getElementById('editUserSubmitText');
        const originalText = submitText.textContent;

        const userId = document.getElementById('editUserId').value;
        const payload = {
            name: document.getElementById('editUserName').value.trim(),
            phone_number: document.getElementById('editUserPhone').value.trim() || null,
            status: document.getElementById('editUserStatus').value,
            institution_id: document.getElementById('editUserInstitution').value || null,
        };

        submitBtn.disabled = true;
        submitText.textContent = 'Menyimpan...';

        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch(`/api/admin/users/${userId}`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal memperbarui user');
            }

            showToast('User berhasil diperbarui', 'success');
            closeEditUserModal();
            clearCacheData(USERS_CACHE_KEY);
            await loadUsers(true);

        } catch (error) {
            console.error('Edit user error:', error);
            const errorDiv = document.getElementById('editUserFormError');
            errorDiv.querySelector('p').textContent = error.message;
            errorDiv.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = originalText;
        }
    });
}

async function toggleUserStatus(userId) {
    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`/api/admin/users/${userId}/status`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Gagal mengubah status user');
        }

        showToast('Status user diperbarui', 'success');
        clearCacheData(USERS_CACHE_KEY);
        await loadUsers(true);

    } catch (error) {
        console.error('Toggle status error:', error);
        showToast(error.message || 'Gagal mengubah status user', 'error');
    }
}

// View user details
function viewUserDetails(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    
    let roleText = '-';
    if (user.roles) {
        if (Array.isArray(user.roles)) {
            roleText = user.roles.join(', ');
        } else if (typeof user.roles === 'string') {
            roleText = user.roles;
        }
    }
    
    alert(`User Details:\n\nName: ${user.name}\nEmail: ${user.email}\nRole: ${roleText}\nStatus: ${user.status || 'Unknown'}\nLast Login: ${user.last_login || 'Never'}`);
}

// Open delete confirmation modal
function confirmDeleteUser(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteFormError').classList.add('hidden');
    
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close delete modal
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Execute delete user
async function executeDeleteUser() {
    const userId = document.getElementById('deleteUserId').value;
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteBtnText = document.getElementById('deleteBtnText');
    const originalText = deleteBtnText.textContent;
    
    try {
        // Disable button and show loading
        deleteBtn.disabled = true;
        deleteBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
        
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to delete user');
        }
        
        showToast('User berhasil dihapus!', 'success');
        closeDeleteModal();
        
        // Clear cache and reload
        clearCacheData(USERS_CACHE_KEY);
        await loadUsers(true);
        
    } catch (error) {
        console.error('Delete user error:', error);
        const errorDiv = document.getElementById('deleteFormError');
        errorDiv.querySelector('p').textContent = error.message;
        errorDiv.classList.remove('hidden');
    } finally {
        // Re-enable button
        deleteBtn.disabled = false;
        deleteBtnText.textContent = originalText;
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-20 right-6 px-6 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Refresh user data (force refresh from API)
async function refreshUserData() {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    
    icon.classList.add('fa-spin');
    btn.disabled = true;
    
    try {
        clearCacheData();
        await loadUsers(true);
        await loadInstitutions(true);
        await loadRoles(true);
        showToast('Data berhasil diperbarui', 'success');
    } catch (error) {
        showToast('Gagal memperbarui data', 'error');
    } finally {
        icon.classList.remove('fa-spin');
        btn.disabled = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initUserManagement);
</script>
@endpush
