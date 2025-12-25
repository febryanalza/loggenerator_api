@extends('institution_admin.layout')

@section('title', 'Detail Logbook')
@section('page-title', 'Detail Logbook')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <a href="{{ route('institution-admin.logbooks') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-green-600 md:ml-2">Manajemen Logbook</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2" id="logbook-title-breadcrumb">Detail</span>
    </div>
</li>
@endsection

@section('content')
<!-- Loading State -->
<div id="loading-state" class="text-center py-12">
    <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
    <p class="text-gray-600">Memuat detail logbook...</p>
</div>

<!-- Main Content -->
<div id="main-content" class="hidden">
    <!-- Header Actions -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2" id="logbook-title">-</h2>
                <p class="text-gray-600" id="logbook-description">-</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('institution-admin.logbooks') }}" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <button onclick="LogbookDetail.showEditModal()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit Logbook
                </button>
            </div>
        </div>
    </div>

    <!-- Logbook Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Basic Info -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Informasi Dasar
            </h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <p id="info-status" class="mt-1">-</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Institusi</label>
                    <p id="info-institution" class="text-gray-800 mt-1">-</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Dibuat Oleh</label>
                    <p id="info-creator" class="text-gray-800 mt-1">-</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Dibuat Pada</label>
                    <p id="info-created-at" class="text-gray-800 mt-1">-</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-bar text-purple-600 mr-2"></i>Statistik
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Entries</span>
                    <span id="stat-entries" class="font-semibold text-gray-800">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Fields</span>
                    <span id="stat-fields" class="font-semibold text-gray-800">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">User Access</span>
                    <span id="stat-access" class="font-semibold text-gray-800">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Participants</span>
                    <span id="stat-participants" class="font-semibold text-gray-800">0</span>
                </div>
            </div>
        </div>

        <!-- Owner Info -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-shield text-green-600 mr-2"></i>Owner
            </h3>
            <div id="owner-info" class="space-y-3">
                <!-- Will be populated by JS -->
            </div>
        </div>
    </div>

    <!-- Fields Section -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-list text-blue-600 mr-2"></i>Fields / Kolom
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Required</th>
                    </tr>
                </thead>
                <tbody id="fields-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Will be populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- User Access Section -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-users text-green-600 mr-2"></i>User Access
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Granted At</th>
                    </tr>
                </thead>
                <tbody id="access-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Will be populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Participants Section -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-user-friends text-purple-600 mr-2"></i>Participants
            </h3>
            <button onclick="LogbookDetail.showAddParticipantModal()" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200 text-sm">
                <i class="fas fa-plus mr-2"></i>Tambah Participant
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr id="participants-header">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <!-- Dynamic columns will be added here -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody id="participants-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Will be populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Participant Modal -->
<div id="participant-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-user-plus text-purple-600 mr-2"></i>Tambah Participant
            </h3>
            <button onclick="LogbookDetail.closeParticipantModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="participant-form" onsubmit="LogbookDetail.submitParticipant(event)" class="p-6">
            <div id="participant-fields-container" class="space-y-4 mb-6">
                <!-- Dynamic fields will be added here -->
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-star text-yellow-600 mr-1"></i>Nilai (Opsional)
                </label>
                <input type="number" id="participant-grade" min="1" max="100"
                    placeholder="Nilai 1-100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="LogbookDetail.closeParticipantModal()" 
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="submit" id="participant-submit-btn"
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const LogbookDetail = {
    logbookId: null,
    logbookData: null,
    requiredDataFields: [],

    init() {
        // Get logbook ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        this.logbookId = urlParams.get('id');
        
        if (!this.logbookId) {
            showAlert('error', 'Error', 'ID Logbook tidak ditemukan');
            setTimeout(() => window.location.href = '{{ route("institution-admin.logbooks") }}', 1500);
            return;
        }

        this.loadLogbookDetail();
        this.loadRequiredDataFields();
    },

    getToken() {
        return localStorage.getItem('admin_token');
    },

    async loadLogbookDetail() {
        try {
            const response = await fetch(`/api/templates/${this.logbookId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load logbook');

            const result = await response.json();
            this.logbookData = result.data;
            
            await this.loadUserAccess();
            await this.loadParticipants();
            
            this.renderLogbookDetail();
            
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('main-content').classList.remove('hidden');
        } catch (error) {
            console.error('Error loading logbook:', error);
            showAlert('error', 'Error', 'Gagal memuat detail logbook');
        }
    },

    async loadUserAccess() {
        try {
            const response = await fetch(`/api/user-access/template/${this.logbookId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.logbookData.user_access = result.data || [];
            }
        } catch (error) {
            console.error('Error loading user access:', error);
        }
    },

    async loadParticipants() {
        try {
            const response = await fetch(`/api/participants/list?template_id=${this.logbookId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.logbookData.participants = result.data?.participants || [];
            }
        } catch (error) {
            console.error('Error loading participants:', error);
            this.logbookData.participants = [];
        }
    },

    async loadRequiredDataFields() {
        try {
            const userData = JSON.parse(localStorage.getItem('admin_user'));
            const institutionId = userData.institution_id || userData.institution?.id;

            console.log('Loading required data fields for institution:', institutionId);

            if (!institutionId) {
                console.error('Institution ID not found in user data');
                this.requiredDataFields = [];
                return;
            }

            const response = await fetch(`/api/required-data-participants/institution/${institutionId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                console.log('Required data fields response:', result);
                // Filter only active required data fields
                const allFields = result.data?.required_data || [];
                this.requiredDataFields = allFields.filter(field => field.is_active);
                console.log('Loaded active required data fields:', this.requiredDataFields);
            } else {
                console.error('Failed to load required data fields:', response.status);
                this.requiredDataFields = [];
            }
        } catch (error) {
            console.error('Error loading required data fields:', error);
            this.requiredDataFields = [];
        }
    },

    renderLogbookDetail() {
        const lb = this.logbookData;

        // Title
        document.getElementById('logbook-title').textContent = lb.name;
        document.getElementById('logbook-title-breadcrumb').textContent = lb.name;
        document.getElementById('logbook-description').textContent = lb.description || '-';

        // Basic Info
        document.getElementById('info-status').innerHTML = lb.is_active 
            ? '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Aktif</span>'
            : '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-times-circle mr-1"></i>Tidak Aktif</span>';
        
        document.getElementById('info-institution').textContent = lb.institution?.name || '-';
        document.getElementById('info-creator').textContent = lb.creator?.name || '-';
        document.getElementById('info-created-at').textContent = this.formatDate(lb.created_at);

        // Statistics
        document.getElementById('stat-entries').textContent = lb.entries_count || lb.logbook_data_count || 0;
        document.getElementById('stat-fields').textContent = lb.required_columns?.length || 0;
        document.getElementById('stat-access').textContent = lb.user_access?.length || 0;
        document.getElementById('stat-participants').textContent = lb.participants?.length || 0;

        // Owner Info
        const ownerHtml = lb.owner ? `
            <div class="flex items-center space-x-3">
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-user text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">${this.escapeHtml(lb.owner.name)}</p>
                    <p class="text-sm text-gray-600">${this.escapeHtml(lb.owner.email)}</p>
                    ${lb.owner.roles?.length ? `<p class="text-xs text-gray-500 mt-1">${lb.owner.roles.map(r => r.name).join(', ')}</p>` : ''}
                </div>
            </div>
        ` : '<p class="text-gray-500 italic">Belum ada owner</p>';
        document.getElementById('owner-info').innerHTML = ownerHtml;

        // Fields Table
        this.renderFieldsTable();

        // User Access Table
        this.renderUserAccessTable();

        // Participants Table
        this.renderParticipantsTable();
    },

    renderFieldsTable() {
        const tbody = document.getElementById('fields-table-body');
        const fields = this.logbookData.required_columns || [];

        if (fields.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada fields</td></tr>';
            return;
        }

        tbody.innerHTML = fields.map((field, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">${index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${this.escapeHtml(field.field_name)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${this.escapeHtml(field.data_type)}</td>
                <td class="px-6 py-4 text-sm">
                    ${field.is_required 
                        ? '<span class="text-red-600"><i class="fas fa-asterisk text-xs"></i> Ya</span>'
                        : '<span class="text-gray-500">Tidak</span>'
                    }
                </td>
            </tr>
        `).join('');
    },

    renderUserAccessTable() {
        const tbody = document.getElementById('access-table-body');
        const access = this.logbookData.user_access || [];

        if (access.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada user access</td></tr>';
            return;
        }

        tbody.innerHTML = access.map((acc, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">${index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${this.escapeHtml(acc.user?.name || '-')}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${this.escapeHtml(acc.user?.email || '-')}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full ${this.getRoleBadgeClass(acc.logbook_role)}">
                        ${acc.logbook_role}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">${this.formatDate(acc.created_at)}</td>
            </tr>
        `).join('');
    },

    renderParticipantsTable() {
        const participants = this.logbookData.participants || [];
        
        // Render dynamic header columns based on required_data_participants
        const header = document.getElementById('participants-header');
        let headerHtml = '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>';
        
        // Use required data fields for consistent column order if available
        let columnKeys = [];
        if (this.requiredDataFields && this.requiredDataFields.length > 0) {
            // Use required data fields (active only) for header
            columnKeys = this.requiredDataFields.map(field => field.data_name);
            columnKeys.forEach(key => {
                headerHtml += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">${this.escapeHtml(key)}</th>`;
            });
        } else if (participants.length > 0 && participants[0].data) {
            // Fallback: use first participant's data keys
            columnKeys = Object.keys(participants[0].data);
            columnKeys.forEach(key => {
                headerHtml += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">${this.escapeHtml(key)}</th>`;
            });
        }
        
        headerHtml += `
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nilai</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
        `;
        header.innerHTML = headerHtml;

        // Render table body
        const tbody = document.getElementById('participants-table-body');

        if (participants.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="100" class="px-6 py-12 text-center">
                        <i class="fas fa-users text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500 text-lg">Belum ada participant</p>
                        <button onclick="LogbookDetail.showAddParticipantModal()" 
                            class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            <i class="fas fa-plus mr-2"></i>Tambah Participant Pertama
                        </button>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = participants.map((p, index) => {
            let rowHtml = `<tr class="hover:bg-gray-50"><td class="px-6 py-4 text-sm text-gray-900">${index + 1}</td>`;
            
            // Render data columns in same order as header (using columnKeys)
            if (p.data && columnKeys.length > 0) {
                // Render values based on column order from required_data_participants
                columnKeys.forEach(key => {
                    const value = p.data[key] || '-';
                    rowHtml += `<td class="px-6 py-4 text-sm text-gray-800">${this.escapeHtml(value)}</td>`;
                });
            } else if (p.data) {
                // Fallback: render all data values
                Object.values(p.data).forEach(value => {
                    rowHtml += `<td class="px-6 py-4 text-sm text-gray-800">${this.escapeHtml(value)}</td>`;
                });
            }
            
            // Grade column
            rowHtml += `<td class="px-6 py-4 text-sm">
                ${p.grade 
                    ? `<span class="font-semibold ${p.grade >= 60 ? 'text-green-600' : 'text-red-600'}">${p.grade}</span>`
                    : '<span class="text-gray-400">-</span>'
                }
            </td>`;
            
            // Status column
            rowHtml += `<td class="px-6 py-4 text-sm">
                ${p.passed 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Lulus</span>'
                    : p.grade 
                        ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i>Tidak Lulus</span>'
                        : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Belum Dinilai</span>'
                }
            </td>`;
            
            // Actions column
            rowHtml += `<td class="px-6 py-4 text-sm space-x-2">
                <button onclick="LogbookDetail.editParticipant('${p.id}')" 
                    class="text-green-600 hover:text-green-900" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="LogbookDetail.deleteParticipant('${p.id}', '${this.escapeHtml(p.name)}')" 
                    class="text-red-600 hover:text-red-900" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td></tr>`;
            
            return rowHtml;
        }).join('');
    },

    showAddParticipantModal() {
        const container = document.getElementById('participant-fields-container');
        container.innerHTML = '';

        console.log('Required data fields:', this.requiredDataFields);

        if (!this.requiredDataFields || this.requiredDataFields.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-3"></i>
                    <p class="text-gray-600 mb-4">Belum ada required data field yang dikonfigurasi untuk institution ini.</p>
                    <p class="text-sm text-gray-500">Silakan tambahkan required data participant di menu Required Data Participant terlebih dahulu.</p>
                </div>
            `;
            const modal = document.getElementById('participant-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            return;
        }

        this.requiredDataFields.forEach(field => {
            container.innerHTML += `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        ${this.escapeHtml(field.data_name)} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" data-field-name="${this.escapeHtml(field.data_name)}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            `;
        });

        const modal = document.getElementById('participant-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeParticipantModal() {
        const modal = document.getElementById('participant-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('participant-form').reset();
    },

    async submitParticipant(event) {
        event.preventDefault();

        const container = document.getElementById('participant-fields-container');
        const inputs = container.querySelectorAll('input[data-field-name]');
        const data = {};

        // Build data object with keys from required_data_participants.data_name
        // Format: { "Nama Lengkap": "John Doe", "NIM": "12345", "Email": "john@email.com" }
        inputs.forEach(input => {
            const value = input.value.trim();
            if (value) {
                // Key is from data-field-name attribute (required_data_participants.data_name)
                data[input.dataset.fieldName] = value;
            }
        });

        // Validate that at least one field is filled
        if (Object.keys(data).length === 0) {
            showAlert('error', 'Error', 'Harap isi minimal satu field participant');
            return;
        }

        const grade = document.getElementById('participant-grade').value;

        const submitBtn = document.getElementById('participant-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

        const payload = {
            template_id: this.logbookId,
            data: data, // JSON object with keys from required_data_participants.data_name
            grade: grade ? parseInt(grade) : null
        };

        console.log('Sending participant data:', payload);
        console.log('Data keys:', Object.keys(data));

        try {
            const response = await fetch('/api/participants', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            console.log('Response:', result);

            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', 'Participant berhasil ditambahkan');
                this.closeParticipantModal();
                await this.loadParticipants();
                this.renderParticipantsTable();
            } else {
                // Show validation errors if available
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat().join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(result.message || 'Gagal menambahkan participant');
            }
        } catch (error) {
            console.error('Error adding participant:', error);
            showAlert('error', 'Error', error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan';
        }
    },

    async deleteParticipant(id, name) {
        if (!confirm(`Apakah Anda yakin ingin menghapus participant "${name}"?`)) return;

        try {
            const response = await fetch(`/api/participants/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', 'Participant berhasil dihapus');
                await this.loadParticipants();
                this.renderParticipantsTable();
            } else {
                throw new Error(result.message || 'Gagal menghapus participant');
            }
        } catch (error) {
            console.error('Error deleting participant:', error);
            showAlert('error', 'Error', error.message);
        }
    },

    showEditModal() {
        window.location.href = '{{ route("institution-admin.logbooks") }}?edit=' + this.logbookId;
    },

    getRoleBadgeClass(roleName) {
        const classes = {
            'Owner': 'bg-purple-100 text-purple-800',
            'Supervisor': 'bg-blue-100 text-blue-800',
            'Editor': 'bg-green-100 text-green-800',
            'Viewer': 'bg-gray-100 text-gray-800'
        };
        return classes[roleName] || 'bg-gray-100 text-gray-800';
    },

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    formatDate(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    LogbookDetail.init();
});
</script>
@endpush
