{{-- Tab Content: Logbooks List by Institution --}}
<div id="logbooks-content">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Logbook</p>
                    <p class="text-3xl font-bold text-gray-800" id="lb-stat-total">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Logbook Aktif</p>
                    <p class="text-3xl font-bold text-gray-800" id="lb-stat-active">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Entri</p>
                    <p class="text-3xl font-bold text-gray-800" id="lb-stat-entries">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Kontributor</p>
                    <p class="text-3xl font-bold text-gray-800" id="lb-stat-contributors">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Logbook</label>
                <div class="relative">
                    <input type="text" id="lb-search-input" placeholder="Nama logbook..."
                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="lb-status-filter" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Non-aktif</option>
                </select>
            </div>
            <button onclick="LogbooksManager.refresh()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <button onclick="LogbooksManager.showCreateModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i>Buat Logbook
            </button>
        </div>
    </div>

    <!-- Logbooks Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Logbook
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Deskripsi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fields
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Entri
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dibuat
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="lb-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                                <p class="text-gray-500">Memuat data...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Logbook Modal -->
<div id="lb-form-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full mx-4 max-h-[95vh] overflow-y-auto transform transition-all">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl sticky top-0 z-10">
            <h3 class="text-xl font-bold text-gray-800" id="lb-modal-title">
                <i class="fas fa-book text-green-600 mr-2"></i>
                Buat Logbook Baru
            </h3>
            <button onclick="LogbooksManager.closeFormModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="lb-form" onsubmit="LogbooksManager.submitForm(event)" class="p-6">
            <input type="hidden" id="lb-edit-id">
            
            <!-- Basic Information -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">
                    <i class="fas fa-info-circle text-green-500 mr-2"></i>Informasi Dasar
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Logbook *</label>
                        <input type="text" id="lb-name" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            placeholder="Nama logbook...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Institusi</label>
                        <input type="text" id="lb-institution-name" readonly
                            class="w-full px-4 py-2 border rounded-lg bg-gray-100 text-gray-600"
                            placeholder="Institusi otomatis">
                        <input type="hidden" id="lb-institution-id">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="lb-description" rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Deskripsi logbook..."></textarea>
                </div>
            </div>
            
            <!-- Owner Assignment -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">
                    <i class="fas fa-user-shield text-blue-500 mr-2"></i>Assign Owner
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari User (Email) *</label>
                        <div class="relative">
                            <input type="text" id="lb-owner-search" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Ketik email user..." autocomplete="off">
                            <div id="lb-owner-suggestions" class="absolute z-20 w-full bg-white border rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto hidden"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Cari berdasarkan email, user ini akan menjadi Owner logbook</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User Terpilih</label>
                        <div id="lb-selected-owner" class="px-4 py-2 border rounded-lg bg-gray-50 min-h-[42px] flex items-center">
                            <span class="text-gray-400 italic">Belum ada user dipilih</span>
                        </div>
                        <input type="hidden" id="lb-owner-id">
                        <input type="hidden" id="lb-owner-email">
                    </div>
                </div>
            </div>
            
            <!-- Field Configuration -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">
                    <i class="fas fa-columns text-purple-500 mr-2"></i>Konfigurasi Field
                </h4>
                
                <!-- Template Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gunakan Template</label>
                    <div class="flex gap-4 items-center">
                        <select id="lb-template-select" 
                            class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            onchange="LogbooksManager.onTemplateSelect()">
                            <option value="">-- Pilih Template (Opsional) --</option>
                        </select>
                        <button type="button" onclick="LogbooksManager.applyTemplate()" 
                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                            <i class="fas fa-magic mr-1"></i>Terapkan
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Pilih template untuk mengisi field otomatis, atau tambahkan field secara manual</p>
                </div>
                
                <!-- Custom Fields -->
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Field Logbook
                    </label>
                    <button type="button" onclick="LogbooksManager.addField()" 
                        class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm">
                        <i class="fas fa-plus mr-1"></i>Tambah Field
                    </button>
                </div>
                
                <div id="lb-fields-container" class="space-y-3">
                    <!-- Fields will be added dynamically -->
                </div>
                
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Field dapat ditambahkan setelah logbook dibuat melalui menu edit.
                </p>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="LogbooksManager.closeFormModal()" 
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit" id="lb-submit-btn"
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Logbook Detail Modal -->
<div id="lb-view-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-book text-green-600 mr-2"></i>
                Detail Logbook
            </h3>
            <button onclick="LogbooksManager.closeViewModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6" id="lb-view-content">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="lb-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6 border-b bg-red-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-red-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Konfirmasi Hapus
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus logbook <strong id="lb-delete-name"></strong>?</p>
            <p class="text-sm text-red-600 mb-6">
                <i class="fas fa-warning mr-1"></i>
                Semua data dan entri dalam logbook ini akan dihapus permanen.
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="LogbooksManager.closeDeleteModal()" 
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button onclick="LogbooksManager.confirmDelete()" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-trash mr-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Logbooks Manager Module
const LogbooksManager = {
    allLogbooks: [],
    availableTemplates: [],
    dataTypes: [],
    users: [],
    institutionId: null,
    institutionName: null,
    editingId: null,
    logbookToDelete: null,
    fieldCounter: 0,
    searchTimeout: null,
    CACHE_KEY: 'inst_logbooks_cache',
    CACHE_DURATION: 60 * 60 * 1000, // 1 hour cache

    init(institutionId) {
        this.institutionId = institutionId;
        this.loadInstitutionInfo();
        this.loadDataTypes();
        this.loadAvailableTemplates();
        this.loadLogbooks();
        this.setupEventListeners();
    },

    setupEventListeners() {
        const searchInput = document.getElementById('lb-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterLogbooks());
        }
        
        const statusFilter = document.getElementById('lb-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterLogbooks());
        }
        
        // Owner search with debounce
        const ownerSearch = document.getElementById('lb-owner-search');
        if (ownerSearch) {
            ownerSearch.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => this.searchUsers(e.target.value), 300);
            });
            
            ownerSearch.addEventListener('focus', () => {
                if (ownerSearch.value.length >= 2) {
                    document.getElementById('lb-owner-suggestions').classList.remove('hidden');
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#lb-owner-search') && !e.target.closest('#lb-owner-suggestions')) {
                    document.getElementById('lb-owner-suggestions').classList.add('hidden');
                }
            });
        }
    },

    getToken() {
        return localStorage.getItem('admin_token');
    },

    // Cache functions
    isValidCache() {
        const cached = localStorage.getItem(this.CACHE_KEY);
        if (!cached) return false;
        try {
            const { timestamp } = JSON.parse(cached);
            return (Date.now() - timestamp) < this.CACHE_DURATION;
        } catch (e) {
            return false;
        }
    },

    getCache() {
        if (!this.isValidCache()) return null;
        try {
            return JSON.parse(localStorage.getItem(this.CACHE_KEY)).data;
        } catch (e) {
            return null;
        }
    },

    setCache(data) {
        try {
            localStorage.setItem(this.CACHE_KEY, JSON.stringify({ data, timestamp: Date.now() }));
        } catch (e) {
            console.error('Failed to set cache:', e);
        }
    },

    clearCache() {
        localStorage.removeItem(this.CACHE_KEY);
    },

    loadInstitutionInfo() {
        const userData = localStorage.getItem('admin_user');
        if (userData) {
            try {
                const user = JSON.parse(userData);
                this.institutionName = user.institution?.name || 'Institusi';
                this.institutionId = user.institution_id || user.institution?.id;
            } catch (e) {
                console.error('Error parsing user data:', e);
            }
        }
    },

    async loadDataTypes() {
        try {
            const response = await fetch('/api/available-data-types/active', {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.dataTypes = result.data || [];
            }
        } catch (error) {
            console.error('Error loading data types:', error);
            // Fallback data types
            this.dataTypes = [
                { name: 'text', display_name: 'Teks' },
                { name: 'number', display_name: 'Angka' },
                { name: 'date', display_name: 'Tanggal' },
                { name: 'time', display_name: 'Waktu' },
                { name: 'datetime', display_name: 'Tanggal & Waktu' },
                { name: 'image', display_name: 'Gambar' },
                { name: 'boolean', display_name: 'Ya/Tidak' }
            ];
        }
    },

    async loadAvailableTemplates() {
        try {
            const response = await fetch(`/api/available-templates/institution/${this.institutionId}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.availableTemplates = (result.data || []).filter(t => t.is_active);
                this.populateTemplateDropdown();
            }
        } catch (error) {
            console.error('Error loading available templates:', error);
        }
    },

    populateTemplateDropdown() {
        const select = document.getElementById('lb-template-select');
        if (!select) return;
        
        select.innerHTML = '<option value="">-- Pilih Template (Opsional) --</option>';
        this.availableTemplates.forEach(template => {
            select.innerHTML += `<option value="${template.id}">${this.escapeHtml(template.name)} (${template.required_columns?.length || 0} fields)</option>`;
        });
    },

    async searchUsers(query) {
        const suggestionsDiv = document.getElementById('lb-owner-suggestions');
        
        if (!query || query.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`/api/users/search?search=${encodeURIComponent(query)}&per_page=10`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                const users = result.data?.data || result.data || [];
                
                if (users.length === 0) {
                    suggestionsDiv.innerHTML = '<div class="px-4 py-2 text-gray-500 text-sm">Tidak ada user ditemukan</div>';
                } else {
                    suggestionsDiv.innerHTML = users.map(user => `
                        <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer flex items-center gap-3" 
                            onclick="LogbooksManager.selectOwner('${user.id}', '${this.escapeHtml(user.email)}', '${this.escapeHtml(user.name)}')">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">${this.escapeHtml(user.name)}</div>
                                <div class="text-xs text-gray-500">${this.escapeHtml(user.email)}</div>
                            </div>
                        </div>
                    `).join('');
                }
                suggestionsDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error searching users:', error);
            suggestionsDiv.innerHTML = '<div class="px-4 py-2 text-red-500 text-sm">Error mencari user</div>';
            suggestionsDiv.classList.remove('hidden');
        }
    },

    selectOwner(userId, email, name) {
        document.getElementById('lb-owner-id').value = userId;
        document.getElementById('lb-owner-email').value = email;
        document.getElementById('lb-owner-search').value = email;
        document.getElementById('lb-selected-owner').innerHTML = `
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 text-sm"></i>
                </div>
                <div>
                    <div class="font-medium text-gray-800">${this.escapeHtml(name)}</div>
                    <div class="text-xs text-gray-500">${this.escapeHtml(email)}</div>
                </div>
                <button type="button" onclick="LogbooksManager.clearOwner()" class="ml-auto text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        document.getElementById('lb-owner-suggestions').classList.add('hidden');
    },

    clearOwner() {
        document.getElementById('lb-owner-id').value = '';
        document.getElementById('lb-owner-email').value = '';
        document.getElementById('lb-owner-search').value = '';
        document.getElementById('lb-selected-owner').innerHTML = '<span class="text-gray-400 italic">Belum ada user dipilih</span>';
    },

    onTemplateSelect() {
        // Just select, don't apply yet
    },

    applyTemplate() {
        const templateId = document.getElementById('lb-template-select').value;
        if (!templateId) {
            showAlert('warning', 'Peringatan', 'Pilih template terlebih dahulu');
            return;
        }

        const template = this.availableTemplates.find(t => t.id === templateId);
        if (!template || !template.required_columns) {
            showAlert('error', 'Error', 'Template tidak memiliki kolom');
            return;
        }

        // Clear existing fields
        document.getElementById('lb-fields-container').innerHTML = '';
        this.fieldCounter = 0;

        // Add fields from template
        template.required_columns.forEach(col => {
            this.addField({
                name: col.name,
                data_type: col.data_type,
                description: col.description
            });
        });

        showAlert('success', 'Berhasil', `${template.required_columns.length} field dari template "${template.name}" telah diterapkan`);
    },

    addField(fieldData = null) {
        const container = document.getElementById('lb-fields-container');
        const fieldId = ++this.fieldCounter;
        
        const dataTypeOptions = this.dataTypes.map(dt => 
            `<option value="${dt.name}" ${fieldData?.data_type === dt.name ? 'selected' : ''}>${dt.display_name || dt.name}</option>`
        ).join('');
        
        const fieldHtml = `
            <div id="lb-field-${fieldId}" class="flex gap-3 items-start p-4 bg-gray-50 rounded-lg border">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Field</label>
                    <input type="text" name="field_name_${fieldId}" 
                        value="${fieldData?.name || ''}"
                        placeholder="Nama field..."
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Data</label>
                    <select name="field_type_${fieldId}" 
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required>
                        ${dataTypeOptions}
                    </select>
                </div>
                <button type="button" onclick="LogbooksManager.removeField(${fieldId})" 
                    class="mt-6 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Field">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', fieldHtml);
    },

    removeField(fieldId) {
        const field = document.getElementById(`lb-field-${fieldId}`);
        if (field) {
            field.remove();
        }
    },

    getFieldsData() {
        const container = document.getElementById('lb-fields-container');
        const fields = [];
        
        container.querySelectorAll('[id^="lb-field-"]').forEach(fieldEl => {
            const id = fieldEl.id.replace('lb-field-', '');
            const name = fieldEl.querySelector(`[name="field_name_${id}"]`)?.value?.trim();
            const dataType = fieldEl.querySelector(`[name="field_type_${id}"]`)?.value;
            
            if (name && dataType) {
                fields.push({ name, data_type: dataType });
            }
        });
        
        return fields;
    },

    async loadLogbooks(forceRefresh = false) {
        try {
            // Check cache first
            if (!forceRefresh) {
                const cachedData = this.getCache();
                if (cachedData) {
                    this.allLogbooks = cachedData;
                    this.updateStats();
                    this.renderTable(this.allLogbooks);
                    return;
                }
            }

            // Fetch from API
            const response = await fetch('/api/templates/admin/all', {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch logbooks');
            }

            const result = await response.json();
            
            if (result.success && result.data) {
                // Filter by institution ID
                this.allLogbooks = result.data.filter(logbook => 
                    logbook.institution_id === this.institutionId
                );
                this.setCache(this.allLogbooks);
                this.updateStats();
                this.renderTable(this.allLogbooks);
            }
        } catch (error) {
            console.error('Error loading logbooks:', error);
            this.renderEmptyState('Gagal memuat data logbook');
        }
    },

    updateStats() {
        const total = this.allLogbooks.length;
        const active = this.allLogbooks.filter(l => l.is_active !== false).length;
        const entries = this.allLogbooks.reduce((sum, l) => sum + (l.entries_count || l.logbook_data_count || 0), 0);
        
        document.getElementById('lb-stat-total').textContent = total;
        document.getElementById('lb-stat-active').textContent = active;
        document.getElementById('lb-stat-entries').textContent = entries;
        document.getElementById('lb-stat-contributors').textContent = new Set(this.allLogbooks.map(l => l.created_by)).size;
    },

    filterLogbooks() {
        const searchTerm = document.getElementById('lb-search-input').value.toLowerCase();
        const statusFilter = document.getElementById('lb-status-filter').value;

        let filtered = this.allLogbooks.filter(logbook => {
            const matchSearch = !searchTerm || 
                logbook.name.toLowerCase().includes(searchTerm) ||
                (logbook.description && logbook.description.toLowerCase().includes(searchTerm));
            
            const matchStatus = !statusFilter ||
                (statusFilter === 'active' && logbook.is_active !== false) ||
                (statusFilter === 'inactive' && logbook.is_active === false);

            return matchSearch && matchStatus;
        });

        this.renderTable(filtered);
    },

    renderTable(logbooks) {
        const tbody = document.getElementById('lb-table-body');
        
        if (!logbooks || logbooks.length === 0) {
            this.renderEmptyState();
            return;
        }

        tbody.innerHTML = logbooks.map(logbook => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-book text-green-600"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${this.escapeHtml(logbook.name)}</div>
                            <div class="text-xs text-gray-500">ID: ${logbook.id.substring(0, 8)}...</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600 max-w-xs truncate">
                        ${logbook.description ? this.escapeHtml(logbook.description) : '<span class="text-gray-400 italic">Tidak ada deskripsi</span>'}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                        ${logbook.fields?.length || logbook.fields_count || 0} fields
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                        ${logbook.entries_count || logbook.logbook_data_count || 0} entri
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <div>${this.formatDate(logbook.created_at)}</div>
                    <div class="text-xs text-gray-400">${logbook.creator?.name || 'Unknown'}</div>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="LogbooksManager.viewLogbook('${logbook.id}')" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="LogbooksManager.showEditModal('${logbook.id}')" 
                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="LogbooksManager.showDeleteModal('${logbook.id}', '${this.escapeHtml(logbook.name)}')" 
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderEmptyState(message = null) {
        const tbody = document.getElementById('lb-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-book text-gray-400 text-3xl"></i>
                        </div>
                        <p class="text-gray-500 text-lg mb-2">${message || 'Belum ada logbook'}</p>
                        <p class="text-gray-400 text-sm mb-4">Buat logbook pertama untuk institusi Anda</p>
                        <button onclick="LogbooksManager.showCreateModal()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-plus mr-2"></i>Buat Logbook
                        </button>
                    </div>
                </td>
            </tr>
        `;
    },

    showCreateModal() {
        this.editingId = null;
        this.fieldCounter = 0;
        
        document.getElementById('lb-modal-title').innerHTML = '<i class="fas fa-book text-green-600 mr-2"></i>Buat Logbook Baru';
        document.getElementById('lb-form').reset();
        document.getElementById('lb-edit-id').value = '';
        document.getElementById('lb-institution-id').value = this.institutionId;
        document.getElementById('lb-institution-name').value = this.institutionName;
        document.getElementById('lb-fields-container').innerHTML = '';
        this.clearOwner();
        this.populateTemplateDropdown();
        
        const modal = document.getElementById('lb-form-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    async showEditModal(id) {
        try {
            const response = await fetch(`/api/templates/${id}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch logbook');
            
            const result = await response.json();
            const logbook = result.data;
            
            this.editingId = id;
            this.fieldCounter = 0;
            
            document.getElementById('lb-modal-title').innerHTML = '<i class="fas fa-book text-green-600 mr-2"></i>Edit Logbook';
            document.getElementById('lb-edit-id').value = id;
            document.getElementById('lb-name').value = logbook.name || '';
            document.getElementById('lb-description').value = logbook.description || '';
            document.getElementById('lb-institution-id').value = logbook.institution_id || this.institutionId;
            document.getElementById('lb-institution-name').value = this.institutionName;
            
            // Clear and hide owner section for edit (owner already assigned)
            this.clearOwner();
            
            // Load existing fields
            document.getElementById('lb-fields-container').innerHTML = '';
            if (logbook.fields && logbook.fields.length > 0) {
                logbook.fields.forEach(field => {
                    this.addField({
                        name: field.name,
                        data_type: field.data_type
                    });
                });
            }
            
            this.populateTemplateDropdown();
            
            const modal = document.getElementById('lb-form-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } catch (error) {
            console.error('Error loading logbook:', error);
            showAlert('error', 'Error', 'Gagal memuat data logbook');
        }
    },

    closeFormModal() {
        const modal = document.getElementById('lb-form-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        this.editingId = null;
    },

    async submitForm(event) {
        event.preventDefault();
        
        const name = document.getElementById('lb-name').value.trim();
        const description = document.getElementById('lb-description').value.trim();
        const institutionId = document.getElementById('lb-institution-id').value;
        const ownerEmail = document.getElementById('lb-owner-email').value;
        const fields = this.getFieldsData();
        
        if (!name) {
            showAlert('error', 'Error', 'Nama logbook wajib diisi');
            return;
        }
        
        // For create, owner is required
        if (!this.editingId && !ownerEmail) {
            showAlert('error', 'Error', 'Pilih user sebagai Owner logbook');
            return;
        }
        
        const submitBtn = document.getElementById('lb-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
        
        try {
            let logbookId = this.editingId;
            
            if (this.editingId) {
                // Update existing logbook
                const response = await fetch(`/api/templates/${this.editingId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name,
                        description: description || null,
                        institution_id: institutionId
                    })
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal memperbarui logbook');
                }
            } else {
                // Create new logbook
                const response = await fetch('/api/templates', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name,
                        description: description || null,
                        institution_id: institutionId
                    })
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal membuat logbook');
                }
                
                logbookId = result.data.id;
                
                // Assign owner access (logbook_role_id = 1 for Owner)
                if (ownerEmail) {
                    await fetch('/api/user-access', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${this.getToken()}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            logbook_template_id: logbookId,
                            user_email: ownerEmail,
                            logbook_role_id: 1 // Owner role
                        })
                    });
                }
            }
            
            // Add/Update fields if any
            if (fields.length > 0 && logbookId) {
                await fetch('/api/fields/batch', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        template_id: logbookId,
                        fields: fields
                    })
                });
            }
            
            showAlert('success', 'Berhasil', this.editingId ? 'Logbook berhasil diperbarui' : 'Logbook berhasil dibuat');
            this.closeFormModal();
            this.clearCache();
            this.loadLogbooks(true);
        } catch (error) {
            console.error('Error saving logbook:', error);
            showAlert('error', 'Error', error.message || 'Gagal menyimpan logbook');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan';
        }
    },

    async viewLogbook(id) {
        try {
            const response = await fetch(`/api/templates/${id}`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch logbook details');

            const result = await response.json();
            const logbook = result.data;

            // Fetch user access for this logbook
            let userAccess = [];
            try {
                const accessResponse = await fetch(`/api/user-access/template/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json'
                    }
                });
                if (accessResponse.ok) {
                    const accessResult = await accessResponse.json();
                    userAccess = accessResult.data || [];
                }
            } catch (e) {
                console.error('Error fetching user access:', e);
            }

            const content = document.getElementById('lb-view-content');
            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Logbook</label>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(logbook.name)}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Total Entri</label>
                            <p class="text-lg font-semibold text-gray-800">${logbook.logbook_data_count || 0}</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Deskripsi</label>
                        <p class="text-gray-800">${logbook.description ? this.escapeHtml(logbook.description) : '<span class="text-gray-400 italic">Tidak ada deskripsi</span>'}</p>
                    </div>
                    
                    <!-- Fields -->
                    <div>
                        <label class="text-sm font-medium text-gray-500 block mb-2">Fields (${logbook.fields?.length || 0})</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            ${logbook.fields && logbook.fields.length > 0 ? `
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    ${logbook.fields.map(field => `
                                        <div class="px-3 py-2 bg-white rounded border flex justify-between items-center">
                                            <span class="font-medium text-sm">${this.escapeHtml(field.name)}</span>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">${field.data_type}</span>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-400 text-center">Tidak ada field</p>'}
                        </div>
                    </div>
                    
                    <!-- User Access -->
                    <div>
                        <label class="text-sm font-medium text-gray-500 block mb-2">User Access (${userAccess.length})</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            ${userAccess.length > 0 ? `
                                <div class="space-y-2">
                                    ${userAccess.map(access => `
                                        <div class="px-3 py-2 bg-white rounded border flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-green-600 text-sm"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-sm">${this.escapeHtml(access.user?.name || 'Unknown')}</div>
                                                    <div class="text-xs text-gray-500">${this.escapeHtml(access.user?.email || '')}</div>
                                                </div>
                                            </div>
                                            <span class="px-2 py-1 ${this.getRoleBadgeClass(access.logbook_role?.name)} text-xs rounded-full">
                                                ${access.logbook_role?.name || 'Unknown'}
                                            </span>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-400 text-center">Tidak ada user access</p>'}
                        </div>
                    </div>
                    
                    <!-- Timestamps -->
                    <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                        <div>
                            <label class="text-gray-500">Dibuat pada</label>
                            <p class="text-gray-800">${this.formatDate(logbook.created_at)}</p>
                            <p class="text-xs text-gray-500">oleh ${logbook.creator?.name || 'Unknown'}</p>
                        </div>
                        <div>
                            <label class="text-gray-500">Terakhir diupdate</label>
                            <p class="text-gray-800">${this.formatDate(logbook.updated_at)}</p>
                        </div>
                    </div>
                </div>
            `;

            const modal = document.getElementById('lb-view-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } catch (error) {
            console.error('Error viewing logbook:', error);
            showAlert('error', 'Error', 'Gagal memuat detail logbook');
        }
    },

    getRoleBadgeClass(roleName) {
        switch (roleName) {
            case 'Owner': return 'bg-purple-100 text-purple-800';
            case 'Supervisor': return 'bg-blue-100 text-blue-800';
            case 'Editor': return 'bg-green-100 text-green-800';
            case 'Viewer': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    },

    closeViewModal() {
        const modal = document.getElementById('lb-view-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    showDeleteModal(id, name) {
        this.logbookToDelete = id;
        document.getElementById('lb-delete-name').textContent = name;
        const modal = document.getElementById('lb-delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeDeleteModal() {
        this.logbookToDelete = null;
        const modal = document.getElementById('lb-delete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async confirmDelete() {
        if (!this.logbookToDelete) return;
        
        try {
            const response = await fetch(`/api/templates/${this.logbookToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', 'Logbook berhasil dihapus');
                this.closeDeleteModal();
                this.clearCache();
                this.loadLogbooks(true);
            } else {
                throw new Error(result.message || 'Gagal menghapus logbook');
            }
        } catch (error) {
            console.error('Error deleting logbook:', error);
            showAlert('error', 'Error', error.message || 'Gagal menghapus logbook');
        }
    },

    refresh() {
        this.clearCache();
        this.loadLogbooks(true);
        this.loadAvailableTemplates();
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
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};
</script>
