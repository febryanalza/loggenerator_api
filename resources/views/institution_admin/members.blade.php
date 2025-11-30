@extends('institution_admin.layout')

@section('title', 'Anggota Institusi')
@section('page-title', 'Anggota Institusi')
@section('page-description', 'Kelola anggota dan tim dalam institusi Anda')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Anggota Institusi</span>
    </div>
</li>
@endsection

@section('content')
<div id="pageLoading" class="flex flex-col items-center justify-center py-20">
    <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
    <p class="mt-4 text-gray-600">Memuat data anggota...</p>
</div>

<div id="mainContent" class="hidden">
    <!-- Header Actions -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex gap-2">
            <button onclick="addMember()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                <i class="fas fa-user-plus mr-2"></i>
                Tambah Anggota
            </button>
            <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh
            </button>
        </div>
        
        <!-- Search & Filter -->
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative flex-1 md:w-64">
                <input type="text" id="searchInput" placeholder="Cari anggota..." 
                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select id="roleFilter" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Semua Role</option>
                <!-- Role options will be populated dynamically from API -->
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Anggota</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalMembers">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Admin</p>
                    <p class="text-2xl font-bold text-gray-800" id="adminCount">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-shield text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Anggota Aktif</p>
                    <p class="text-2xl font-bold text-gray-800" id="activeMembers">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-check text-purple-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Non-aktif</p>
                    <p class="text-2xl font-bold text-gray-800" id="pendingInvites">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Anggota
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bergabung
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="membersTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
        </div>
        
        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-users text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Anggota</h3>
            <p class="text-gray-500 mb-4">Tambahkan anggota tim Anda ke institusi</p>
            <button onclick="addMember()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-user-plus mr-2"></i>
                Tambah Anggota
            </button>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl sticky top-0">
                <h3 class="text-lg font-semibold text-gray-800">Tambah Anggota Baru</h3>
                <button onclick="closeAddMemberModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="addMemberForm" class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" id="memberName" name="name" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Masukkan nama lengkap">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" id="memberEmail" name="email" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="email@example.com">
                    <p class="text-xs text-gray-500 mt-1">Jika email sudah terdaftar, user akan ditambahkan ke institusi Anda</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <div class="relative">
                        <input type="password" id="memberPassword" name="password" required minlength="8"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Minimal 8 karakter">
                        <button type="button" onclick="togglePassword('memberPassword')" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="memberPasswordIcon"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Password harus minimal 8 karakter, mengandung huruf besar, kecil, angka dan simbol</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password *</label>
                    <div class="relative">
                        <input type="password" id="memberPasswordConfirm" name="password_confirmation" required minlength="8"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Ketik ulang password">
                        <button type="button" onclick="togglePassword('memberPasswordConfirm')" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="memberPasswordConfirmIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="tel" id="memberPhone" name="phone_number"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="08xxxxxxxxxx">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <select id="memberRole" name="role" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Role</option>
                        <!-- Role options will be populated dynamically from API -->
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Admin Institusi dapat mengelola logbook dan anggota</p>
                </div>
                
                <!-- Alert Area -->
                <div id="addMemberAlert" class="hidden mb-4 p-4 rounded-lg"></div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeAddMemberModal()" 
                        class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="addMemberSubmitBtn"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Members Manager Module
const MembersManager = {
    allMembers: [],
    allRoles: [],           // All roles for filter dropdown
    assignableRoles: [],    // Roles that can be assigned by Institution Admin
    institutionId: null,
    institutionName: null,
    stats: {},
    CACHE_KEY: 'inst_members_cache',
    ROLES_CACHE_KEY: 'inst_roles_cache',
    CACHE_DURATION: 60 * 60 * 1000, // 1 hour cache

    async init() {
        this.loadInstitutionInfo();
        await this.loadRoles();
        this.loadMembers();
        this.setupEventListeners();
    },

    setupEventListeners() {
        document.getElementById('searchInput').addEventListener('input', () => this.filterMembers());
        document.getElementById('roleFilter').addEventListener('change', () => this.filterMembers());
        document.getElementById('addMemberForm').addEventListener('submit', (e) => this.handleAddMember(e));
    },

    getToken() {
        return localStorage.getItem('admin_token');
    },

    loadInstitutionInfo() {
        const userData = localStorage.getItem('admin_user');
        if (userData) {
            try {
                const user = JSON.parse(userData);
                this.institutionId = user.institution_id || user.institution?.id;
                this.institutionName = user.institution?.name || 'Institusi';
            } catch (e) {
                console.error('Error parsing user data:', e);
            }
        }
    },

    // Cache functions
    isValidCache(key = this.CACHE_KEY) {
        const cached = localStorage.getItem(key);
        if (!cached) return false;
        try {
            const { timestamp } = JSON.parse(cached);
            return (Date.now() - timestamp) < this.CACHE_DURATION;
        } catch (e) {
            return false;
        }
    },

    getCache(key = this.CACHE_KEY) {
        if (!this.isValidCache(key)) return null;
        try {
            return JSON.parse(localStorage.getItem(key)).data;
        } catch (e) {
            return null;
        }
    },

    setCache(data, key = this.CACHE_KEY) {
        try {
            localStorage.setItem(key, JSON.stringify({ data, timestamp: Date.now() }));
        } catch (e) {
            console.error('Failed to set cache:', e);
        }
    },

    clearCache() {
        localStorage.removeItem(this.CACHE_KEY);
    },

    clearRolesCache() {
        localStorage.removeItem(this.ROLES_CACHE_KEY);
    },

    // Load roles from API
    async loadRoles() {
        try {
            // Check cache first
            const cachedRoles = this.getCache(this.ROLES_CACHE_KEY);
            if (cachedRoles) {
                this.allRoles = cachedRoles.allRoles || [];
                this.assignableRoles = cachedRoles.assignableRoles || [];
                this.populateRoleDropdowns();
                return;
            }

            // Fetch all roles for filter and assignable roles for form
            const [allRolesRes, assignableRolesRes] = await Promise.all([
                fetch('/api/institution/all-roles', {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json'
                    }
                }),
                fetch('/api/institution/assignable-roles', {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json'
                    }
                })
            ]);

            if (allRolesRes.ok) {
                const allRolesData = await allRolesRes.json();
                if (allRolesData.success) {
                    this.allRoles = allRolesData.data || [];
                }
            }

            if (assignableRolesRes.ok) {
                const assignableData = await assignableRolesRes.json();
                if (assignableData.success) {
                    this.assignableRoles = assignableData.data || [];
                }
            }

            // Cache roles data
            this.setCache({
                allRoles: this.allRoles,
                assignableRoles: this.assignableRoles
            }, this.ROLES_CACHE_KEY);

            this.populateRoleDropdowns();
        } catch (error) {
            console.error('Failed to load roles:', error);
            // Use fallback roles if API fails
            this.allRoles = [
                { name: 'Super Admin', label: 'Super Admin' },
                { name: 'Admin', label: 'Admin' },
                { name: 'Manager', label: 'Manager' },
                { name: 'Institution Admin', label: 'Admin Institusi' },
                { name: 'User', label: 'Anggota' }
            ];
            this.assignableRoles = [
                { name: 'Institution Admin', label: 'Admin Institusi' },
                { name: 'User', label: 'Anggota' }
            ];
            this.populateRoleDropdowns();
        }
    },

    populateRoleDropdowns() {
        // Populate filter dropdown with all roles
        const filterSelect = document.getElementById('roleFilter');
        filterSelect.innerHTML = '<option value="">Semua Role</option>';
        this.allRoles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.name;
            option.textContent = role.label || role.name;
            filterSelect.appendChild(option);
        });

        // Populate form dropdown with assignable roles only
        const formSelect = document.getElementById('memberRole');
        formSelect.innerHTML = '<option value="">Pilih Role</option>';
        this.assignableRoles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.name;
            option.textContent = role.label || role.name;
            formSelect.appendChild(option);
        });
    },

    async loadMembers(forceRefresh = false) {
        try {
            document.getElementById('pageLoading').classList.remove('hidden');
            document.getElementById('mainContent').classList.add('hidden');

            if (!this.institutionId) {
                throw new Error('Institution ID not found');
            }

            // Check cache first
            if (!forceRefresh) {
                const cachedData = this.getCache();
                if (cachedData) {
                    this.allMembers = cachedData.members || [];
                    this.stats = cachedData.stats || {};
                    this.updateStats();
                    this.renderMembers();
                    document.getElementById('pageLoading').classList.add('hidden');
                    document.getElementById('mainContent').classList.remove('hidden');
                    return;
                }
            }

            const response = await fetch(`/api/institutions/${this.institutionId}/members`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch members');
            }

            const result = await response.json();

            if (result.success && result.data) {
                this.allMembers = result.data.members || [];
                this.stats = result.data.stats || {};
                
                // Cache the data
                this.setCache({ members: this.allMembers, stats: this.stats });
                
                this.updateStats();
                this.renderMembers();
            }

            document.getElementById('pageLoading').classList.add('hidden');
            document.getElementById('mainContent').classList.remove('hidden');
        } catch (error) {
            console.error('Failed to load members:', error);
            document.getElementById('pageLoading').classList.add('hidden');
            document.getElementById('mainContent').classList.remove('hidden');
            showAlert('error', 'Error', 'Gagal memuat data anggota: ' + error.message);
        }
    },

    updateStats() {
        // Use stats from API if available
        if (this.stats.total !== undefined) {
            document.getElementById('totalMembers').textContent = this.stats.total || 0;
            document.getElementById('adminCount').textContent = this.stats.admins || 0;
            document.getElementById('activeMembers').textContent = this.stats.active || 0;
            document.getElementById('pendingInvites').textContent = this.stats.inactive || 0;
        } else {
            // Calculate from members data - handle roles as array
            const admins = this.allMembers.filter(m => {
                if (m.roles && Array.isArray(m.roles)) {
                    return m.roles.includes('Institution Admin');
                }
                return m.role === 'Institution Admin';
            }).length;
            const active = this.allMembers.filter(m => m.status === 'active').length;
            const inactive = this.allMembers.filter(m => m.status !== 'active').length;
            
            document.getElementById('totalMembers').textContent = this.allMembers.length;
            document.getElementById('adminCount').textContent = admins;
            document.getElementById('activeMembers').textContent = active;
            document.getElementById('pendingInvites').textContent = inactive;
        }
    },

    filterMembers() {
        this.renderMembers();
    },

    // Get display roles string from member (handles array format like user-management)
    getMemberRolesString(member) {
        if (member.roles) {
            if (Array.isArray(member.roles)) {
                return member.roles.join(', ');
            } else if (typeof member.roles === 'string') {
                return member.roles;
            }
        }
        return member.role || 'User';
    },

    // Get primary role for badge color
    getPrimaryRole(member) {
        if (member.roles && Array.isArray(member.roles) && member.roles.length > 0) {
            return member.roles[0];
        }
        return member.role || 'User';
    },

    renderMembers() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        
        let filtered = this.allMembers.filter(member => {
            const matchSearch = !searchTerm || 
                member.name?.toLowerCase().includes(searchTerm) ||
                member.email?.toLowerCase().includes(searchTerm);
            
            let matchRole = true;
            if (roleFilter) {
                // Check if member has the selected role (in roles array)
                if (member.roles && Array.isArray(member.roles)) {
                    matchRole = member.roles.includes(roleFilter);
                } else {
                    matchRole = member.role === roleFilter;
                }
            }
            
            return matchSearch && matchRole;
        });
        
        const tbody = document.getElementById('membersTableBody');
        const emptyState = document.getElementById('emptyState');
        
        if (filtered.length === 0) {
            tbody.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        
        emptyState.classList.add('hidden');
        
        tbody.innerHTML = filtered.map(member => {
            const rolesDisplay = this.getMemberRolesString(member);
            const primaryRole = this.getPrimaryRole(member);
            
            return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white font-medium">
                            ${(member.name || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div class="ml-3">
                            <div class="font-medium text-gray-900">${this.escapeHtml(member.name)}</div>
                            ${member.phone_number ? `<div class="text-xs text-gray-500">${this.escapeHtml(member.phone_number)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    ${this.escapeHtml(member.email)}
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full ${this.getRoleBadgeClass(primaryRole)}">
                        ${this.getRoleLabel(rolesDisplay)}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full ${this.getStatusBadgeClass(member.status)}">
                        ${this.getStatusLabel(member.status)}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    ${member.created_at ? this.formatDate(member.created_at) : '-'}
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick="MembersManager.viewMember('${member.id}')" class="text-gray-600 hover:text-gray-800 mx-1" title="Lihat">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${member.last_login ? `
                        <span class="text-xs text-gray-400 ml-2" title="Login terakhir: ${this.formatDate(member.last_login)}">
                            <i class="fas fa-clock"></i>
                        </span>
                    ` : ''}
                </td>
            </tr>
        `}).join('');
    },

    getRoleBadgeClass(role) {
        if (!role) return 'bg-gray-100 text-gray-800';
        
        // Match role names from database exactly
        const badgeClasses = {
            'Super Admin': 'bg-red-100 text-red-800',
            'Admin': 'bg-orange-100 text-orange-800',
            'Manager': 'bg-blue-100 text-blue-800',
            'Institution Admin': 'bg-purple-100 text-purple-800',
            'User': 'bg-green-100 text-green-800'
        };
        
        return badgeClasses[role] || 'bg-gray-100 text-gray-800';
    },

    getRoleLabel(rolesString) {
        if (!rolesString) return 'Anggota';
        
        // Fallback labels map
        const labels = {
            'Super Admin': 'Super Admin',
            'Admin': 'Admin',
            'Manager': 'Manager',
            'Institution Admin': 'Admin Institusi',
            'User': 'Anggota'
        };
        
        // If it's a comma-separated string (multiple roles), translate each
        if (rolesString.includes(',')) {
            return rolesString.split(',').map(r => {
                const trimmed = r.trim();
                // Check from API loaded roles first
                const foundRole = this.allRoles.find(role => role.name === trimmed);
                if (foundRole) return foundRole.label || foundRole.name;
                return labels[trimmed] || trimmed;
            }).join(', ');
        }
        
        // Single role - check from API loaded roles first
        const foundRole = this.allRoles.find(r => r.name === rolesString);
        if (foundRole) {
            return foundRole.label || foundRole.name;
        }
        
        return labels[rolesString] || rolesString;
    },

    getStatusBadgeClass(status) {
        const classes = {
            'active': 'bg-green-100 text-green-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'inactive': 'bg-gray-100 text-gray-800',
            'suspended': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    },

    getStatusLabel(status) {
        const labels = {
            'active': 'Aktif',
            'pending': 'Pending',
            'inactive': 'Non-aktif',
            'suspended': 'Ditangguhkan'
        };
        return labels[status] || status || 'Aktif';
    },

    inviteMember() {
        // Redirect to addMember for backwards compatibility
        this.addMember();
    },

    addMember() {
        document.getElementById('addMemberForm').reset();
        this.hideFormAlert();
        document.getElementById('addMemberModal').classList.remove('hidden');
    },

    closeAddMemberModal() {
        document.getElementById('addMemberModal').classList.add('hidden');
        this.hideFormAlert();
    },

    showFormAlert(type, message) {
        const alertDiv = document.getElementById('addMemberAlert');
        const bgClass = type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' : 
                       type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
                       'bg-blue-100 text-blue-800 border border-blue-200';
        
        alertDiv.className = `${bgClass} p-4 rounded-lg mb-4`;
        alertDiv.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>${message}`;
        alertDiv.classList.remove('hidden');
    },

    hideFormAlert() {
        document.getElementById('addMemberAlert').classList.add('hidden');
    },

    async handleAddMember(e) {
        e.preventDefault();
        this.hideFormAlert();
        
        const submitBtn = document.getElementById('addMemberSubmitBtn');
        const originalText = submitBtn.innerHTML;
        
        const password = document.getElementById('memberPassword').value;
        const passwordConfirm = document.getElementById('memberPasswordConfirm').value;
        
        // Password validation
        if (password !== passwordConfirm) {
            this.showFormAlert('error', 'Password dan konfirmasi password tidak cocok');
            return;
        }
        
        // Password strength validation
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(password)) {
            this.showFormAlert('error', 'Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol');
            return;
        }
        
        const data = {
            name: document.getElementById('memberName').value,
            email: document.getElementById('memberEmail').value,
            password: password,
            password_confirmation: passwordConfirm,
            phone_number: document.getElementById('memberPhone').value || null,
            role: document.getElementById('memberRole').value
        };
        
        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            
            const response = await fetch('/api/institution/members', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                this.showFormAlert('success', result.message || 'Anggota berhasil ditambahkan');
                
                // Close modal and refresh after short delay
                setTimeout(() => {
                    this.closeAddMemberModal();
                    this.clearCache();
                    this.loadMembers(true);
                    showAlert('success', 'Berhasil', result.message || 'Anggota berhasil ditambahkan ke institusi');
                }, 1000);
            } else {
                // Handle validation errors
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat().join('<br>');
                    this.showFormAlert('error', errorMessages);
                } else {
                    this.showFormAlert('error', result.message || 'Gagal menambahkan anggota');
                }
            }
        } catch (error) {
            console.error('Failed to add member:', error);
            this.showFormAlert('error', 'Terjadi kesalahan saat menambahkan anggota');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    closeInviteModal() {
        // Redirect to closeAddMemberModal for backwards compatibility
        this.closeAddMemberModal();
    },

    async handleInvite(e) {
        // Redirect to handleAddMember for backwards compatibility
        this.handleAddMember(e);
    },

    viewMember(id) {
        const member = this.allMembers.find(m => m.id === id);
        if (!member) return;
        
        // Get roles display string
        const rolesDisplay = this.getMemberRolesString(member);
        
        // Show member details in alert for now
        const details = `
Nama: ${member.name}
Email: ${member.email}
Role: ${this.getRoleLabel(rolesDisplay)}
Status: ${this.getStatusLabel(member.status)}
Bergabung: ${member.created_at ? this.formatDate(member.created_at) : '-'}
Login Terakhir: ${member.last_login ? this.formatDate(member.last_login) : 'Belum pernah'}
        `.trim();
        
        showAlert('info', 'Detail Anggota', details);
    },

    refresh() {
        this.clearCache();
        this.clearRolesCache();
        this.loadRoles();
        this.loadMembers(true);
    },

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    }
};

// Global functions for onclick handlers
function addMember() { MembersManager.addMember(); }
function closeAddMemberModal() { MembersManager.closeAddMemberModal(); }
function inviteMember() { MembersManager.addMember(); } // backwards compatibility
function closeInviteModal() { MembersManager.closeAddMemberModal(); } // backwards compatibility
function refreshData() { MembersManager.refresh(); }

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + 'Icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('admin_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    MembersManager.init();
});
</script>
@endpush
