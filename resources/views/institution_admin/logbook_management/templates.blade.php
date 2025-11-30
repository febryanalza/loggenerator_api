{{-- Tab Content: Available Templates Management --}}
<div id="templates-content">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Template</p>
                    <p class="text-3xl font-bold text-gray-800" id="tpl-stat-total">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-layer-group text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Template Aktif</p>
                    <p class="text-3xl font-bold text-gray-800" id="tpl-stat-active">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Template Non-aktif</p>
                    <p class="text-3xl font-bold text-gray-800" id="tpl-stat-inactive">0</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pause-circle text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Fields</p>
                    <p class="text-3xl font-bold text-gray-800" id="tpl-stat-fields">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-columns text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Template</label>
                <div class="relative">
                    <input type="text" id="tpl-search-input" placeholder="Nama template..."
                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="tpl-status-filter" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Non-aktif</option>
                </select>
            </div>
            <button onclick="TemplatesManager.refresh()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <button onclick="TemplatesManager.showCreateModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i>Buat Template
            </button>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Template
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Deskripsi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kolom/Fields
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dibuat
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="tpl-table-body" class="bg-white divide-y divide-gray-200">
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

<!-- Create/Edit Template Modal -->
<div id="tpl-form-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-gray-800" id="tpl-modal-title">
                <i class="fas fa-layer-group text-green-600 mr-2"></i>
                Buat Template Baru
            </h3>
            <button onclick="TemplatesManager.closeFormModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="tpl-form" onsubmit="TemplatesManager.submitForm(event)" class="p-6">
            <input type="hidden" id="tpl-edit-id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Template *</label>
                    <input type="text" id="tpl-name" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Nama template...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="tpl-is-active" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea id="tpl-description" rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Deskripsi template..."></textarea>
            </div>
            
            <!-- Dynamic Fields/Columns -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-columns mr-2"></i>Kolom/Fields Template
                    </label>
                    <button type="button" onclick="TemplatesManager.addColumn()" 
                        class="px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm">
                        <i class="fas fa-plus mr-1"></i>Tambah Kolom
                    </button>
                </div>
                
                <div id="tpl-columns-container" class="space-y-3">
                    <!-- Columns will be added dynamically -->
                </div>
                
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Minimal 1 kolom diperlukan. Anda dapat menambahkan kolom sebanyak yang diperlukan.
                </p>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="TemplatesManager.closeFormModal()" 
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                    class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Template Modal -->
<div id="tpl-view-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-layer-group text-green-600 mr-2"></i>
                Detail Template
            </h3>
            <button onclick="TemplatesManager.closeViewModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6" id="tpl-view-content">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="tpl-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6 border-b bg-red-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-red-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Konfirmasi Hapus
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus template <strong id="tpl-delete-name"></strong>?</p>
            <p class="text-sm text-red-600 mb-6">
                <i class="fas fa-warning mr-1"></i>
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="TemplatesManager.closeDeleteModal()" 
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button onclick="TemplatesManager.confirmDelete()" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-trash mr-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Templates Manager Module
const TemplatesManager = {
    allTemplates: [],
    dataTypes: [],
    institutionId: null,
    templateToDelete: null,
    editingId: null,
    columnCounter: 0,
    CACHE_KEY: 'inst_templates_cache',
    CACHE_DURATION: 60 * 60 * 1000, // 1 hour cache

    init(institutionId) {
        this.institutionId = institutionId;
        this.loadDataTypes();
        this.loadTemplates();
        this.setupEventListeners();
    },

    setupEventListeners() {
        const searchInput = document.getElementById('tpl-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterTemplates());
        }
        
        const statusFilter = document.getElementById('tpl-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterTemplates());
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
                { name: 'boolean', display_name: 'Ya/Tidak' },
                { name: 'select', display_name: 'Pilihan' }
            ];
        }
    },

    async loadTemplates(forceRefresh = false) {
        try {
            // Check cache first
            if (!forceRefresh) {
                const cachedData = this.getCache();
                if (cachedData) {
                    this.allTemplates = cachedData;
                    this.updateStats();
                    this.renderTable(this.allTemplates);
                    return;
                }
            }

            // Fetch all templates by institution (active and inactive)
            const response = await fetch(`/api/available-templates/institution/${this.institutionId}/all`, {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch templates');
            }

            const result = await response.json();
            
            if (result.success && result.data) {
                this.allTemplates = result.data;
                this.setCache(this.allTemplates);
                this.updateStats();
                this.renderTable(this.allTemplates);
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            this.renderEmptyState('Gagal memuat data template');
        }
    },

    updateStats() {
        const total = this.allTemplates.length;
        const active = this.allTemplates.filter(t => t.is_active).length;
        const inactive = this.allTemplates.filter(t => !t.is_active).length;
        const fields = this.allTemplates.reduce((sum, t) => sum + (t.required_columns?.length || 0), 0);
        
        document.getElementById('tpl-stat-total').textContent = total;
        document.getElementById('tpl-stat-active').textContent = active;
        document.getElementById('tpl-stat-inactive').textContent = inactive;
        document.getElementById('tpl-stat-fields').textContent = fields;
    },

    filterTemplates() {
        const searchTerm = document.getElementById('tpl-search-input').value.toLowerCase();
        const statusFilter = document.getElementById('tpl-status-filter').value;

        let filtered = this.allTemplates.filter(template => {
            const matchSearch = !searchTerm || 
                template.name.toLowerCase().includes(searchTerm) ||
                (template.description && template.description.toLowerCase().includes(searchTerm));
            
            const matchStatus = !statusFilter ||
                (statusFilter === 'active' && template.is_active) ||
                (statusFilter === 'inactive' && !template.is_active);

            return matchSearch && matchStatus;
        });

        this.renderTable(filtered);
    },

    renderTable(templates) {
        const tbody = document.getElementById('tpl-table-body');
        
        if (!templates || templates.length === 0) {
            this.renderEmptyState();
            return;
        }

        tbody.innerHTML = templates.map(template => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-layer-group text-green-600"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${this.escapeHtml(template.name)}</div>
                            <div class="text-xs text-gray-500">ID: ${template.id.substring(0, 8)}...</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600 max-w-xs truncate">
                        ${template.description ? this.escapeHtml(template.description) : '<span class="text-gray-400 italic">Tidak ada deskripsi</span>'}
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                        ${template.required_columns?.length || 0} kolom
                    </span>
                </td>
                <td class="px-6 py-4">
                    <button onclick="TemplatesManager.toggleStatus('${template.id}')" 
                        class="px-3 py-1 ${template.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'} text-xs rounded-full hover:opacity-80 transition">
                        <i class="fas fa-${template.is_active ? 'check-circle' : 'pause-circle'} mr-1"></i>
                        ${template.is_active ? 'Aktif' : 'Non-aktif'}
                    </button>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <div>${this.formatDate(template.created_at)}</div>
                    <div class="text-xs text-gray-400">${template.creator?.name || 'Unknown'}</div>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="TemplatesManager.viewTemplate('${template.id}')" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="TemplatesManager.showEditModal('${template.id}')" 
                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="TemplatesManager.showDeleteModal('${template.id}', '${this.escapeHtml(template.name)}')" 
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderEmptyState(message = null) {
        const tbody = document.getElementById('tpl-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-layer-group text-gray-400 text-3xl"></i>
                        </div>
                        <p class="text-gray-500 text-lg mb-2">${message || 'Belum ada template'}</p>
                        <p class="text-gray-400 text-sm mb-4">Buat template pertama Anda untuk institusi</p>
                        <button onclick="TemplatesManager.showCreateModal()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-plus mr-2"></i>Buat Template
                        </button>
                    </div>
                </td>
            </tr>
        `;
    },

    // Column management for dynamic fields
    addColumn(columnData = null) {
        const container = document.getElementById('tpl-columns-container');
        const columnId = ++this.columnCounter;
        
        const dataTypeOptions = this.dataTypes.map(dt => 
            `<option value="${dt.name}" ${columnData?.data_type === dt.name ? 'selected' : ''}>${dt.display_name || dt.name}</option>`
        ).join('');
        
        const columnHtml = `
            <div id="tpl-column-${columnId}" class="flex gap-3 items-start p-4 bg-gray-50 rounded-lg border">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Kolom</label>
                    <input type="text" name="column_name_${columnId}" 
                        value="${columnData?.name || ''}"
                        placeholder="Nama kolom..."
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Data</label>
                    <select name="column_type_${columnId}" 
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        required>
                        ${dataTypeOptions}
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi (Opsional)</label>
                    <input type="text" name="column_desc_${columnId}" 
                        value="${columnData?.description || ''}"
                        placeholder="Deskripsi..."
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <button type="button" onclick="TemplatesManager.removeColumn(${columnId})" 
                    class="mt-6 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Kolom">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', columnHtml);
    },

    removeColumn(columnId) {
        const column = document.getElementById(`tpl-column-${columnId}`);
        if (column) {
            column.remove();
        }
    },

    getColumnsData() {
        const container = document.getElementById('tpl-columns-container');
        const columns = [];
        
        container.querySelectorAll('[id^="tpl-column-"]').forEach(col => {
            const id = col.id.replace('tpl-column-', '');
            const name = col.querySelector(`[name="column_name_${id}"]`)?.value?.trim();
            const dataType = col.querySelector(`[name="column_type_${id}"]`)?.value;
            const description = col.querySelector(`[name="column_desc_${id}"]`)?.value?.trim();
            
            if (name && dataType) {
                columns.push({
                    name,
                    data_type: dataType,
                    description: description || null
                });
            }
        });
        
        return columns;
    },

    showCreateModal() {
        this.editingId = null;
        this.columnCounter = 0;
        
        document.getElementById('tpl-modal-title').innerHTML = '<i class="fas fa-layer-group text-green-600 mr-2"></i>Buat Template Baru';
        document.getElementById('tpl-form').reset();
        document.getElementById('tpl-edit-id').value = '';
        document.getElementById('tpl-columns-container').innerHTML = '';
        
        // Add one default column
        this.addColumn();
        
        const modal = document.getElementById('tpl-form-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    async showEditModal(id) {
        try {
            const template = this.allTemplates.find(t => t.id === id);
            if (!template) {
                // Fetch from API
                const response = await fetch(`/api/available-templates/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) throw new Error('Template not found');
                
                const result = await response.json();
                if (!result.success) throw new Error('Failed to fetch template');
                
                this.editTemplate(result.data);
            } else {
                this.editTemplate(template);
            }
        } catch (error) {
            console.error('Error loading template:', error);
            showAlert('error', 'Error', 'Gagal memuat data template');
        }
    },

    editTemplate(template) {
        this.editingId = template.id;
        this.columnCounter = 0;
        
        document.getElementById('tpl-modal-title').innerHTML = '<i class="fas fa-layer-group text-green-600 mr-2"></i>Edit Template';
        document.getElementById('tpl-edit-id').value = template.id;
        document.getElementById('tpl-name').value = template.name;
        document.getElementById('tpl-description').value = template.description || '';
        document.getElementById('tpl-is-active').value = template.is_active ? '1' : '0';
        
        // Load columns
        document.getElementById('tpl-columns-container').innerHTML = '';
        if (template.required_columns && template.required_columns.length > 0) {
            template.required_columns.forEach(col => this.addColumn(col));
        } else {
            this.addColumn();
        }
        
        const modal = document.getElementById('tpl-form-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeFormModal() {
        const modal = document.getElementById('tpl-form-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        this.editingId = null;
    },

    async submitForm(event) {
        event.preventDefault();
        
        const columns = this.getColumnsData();
        
        if (columns.length === 0) {
            showAlert('error', 'Error', 'Minimal 1 kolom diperlukan');
            return;
        }
        
        const formData = {
            name: document.getElementById('tpl-name').value.trim(),
            description: document.getElementById('tpl-description').value.trim() || null,
            institution_id: this.institutionId,
            required_columns: columns,
            is_active: document.getElementById('tpl-is-active').value === '1'
        };
        
        try {
            const url = this.editingId 
                ? `/api/available-templates/${this.editingId}` 
                : '/api/available-templates';
            const method = this.editingId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method,
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', this.editingId ? 'Template berhasil diperbarui' : 'Template berhasil dibuat');
                this.closeFormModal();
                this.clearCache();
                this.loadTemplates(true);
            } else {
                throw new Error(result.message || 'Gagal menyimpan template');
            }
        } catch (error) {
            console.error('Error saving template:', error);
            showAlert('error', 'Error', error.message || 'Gagal menyimpan template');
        }
    },

    async viewTemplate(id) {
        try {
            const template = this.allTemplates.find(t => t.id === id);
            if (!template) return;

            const content = document.getElementById('tpl-view-content');
            content.innerHTML = `
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Template</label>
                            <p class="text-lg font-semibold text-gray-800">${this.escapeHtml(template.name)}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <p>
                                <span class="px-2 py-1 ${template.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'} text-sm rounded-full">
                                    ${template.is_active ? 'Aktif' : 'Non-aktif'}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Deskripsi</label>
                        <p class="text-gray-800">${template.description ? this.escapeHtml(template.description) : '<span class="text-gray-400 italic">Tidak ada deskripsi</span>'}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500 block mb-2">Kolom/Fields (${template.required_columns?.length || 0})</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            ${template.required_columns && template.required_columns.length > 0 ? `
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="text-left text-xs text-gray-500 uppercase">
                                            <th class="pb-2">Nama Kolom</th>
                                            <th class="pb-2">Tipe Data</th>
                                            <th class="pb-2">Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        ${template.required_columns.map(col => `
                                            <tr>
                                                <td class="py-2 font-medium">${this.escapeHtml(col.name)}</td>
                                                <td class="py-2">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                                        ${this.escapeHtml(col.data_type)}
                                                    </span>
                                                </td>
                                                <td class="py-2 text-sm text-gray-500">
                                                    ${col.description ? this.escapeHtml(col.description) : '-'}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            ` : '<p class="text-gray-400 text-center">Tidak ada kolom</p>'}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-gray-500">Dibuat pada</label>
                            <p class="text-gray-800">${this.formatDate(template.created_at)}</p>
                        </div>
                        <div>
                            <label class="text-gray-500">Terakhir diupdate</label>
                            <p class="text-gray-800">${this.formatDate(template.updated_at)}</p>
                        </div>
                    </div>
                </div>
            `;

            const modal = document.getElementById('tpl-view-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } catch (error) {
            console.error('Error viewing template:', error);
            showAlert('error', 'Error', 'Gagal memuat detail template');
        }
    },

    closeViewModal() {
        const modal = document.getElementById('tpl-view-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async toggleStatus(id) {
        try {
            const response = await fetch(`/api/available-templates/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', 'Status template berhasil diubah');
                this.clearCache();
                this.loadTemplates(true);
            } else {
                throw new Error(result.message || 'Gagal mengubah status');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            showAlert('error', 'Error', 'Gagal mengubah status template');
        }
    },

    showDeleteModal(id, name) {
        this.templateToDelete = id;
        document.getElementById('tpl-delete-name').textContent = name;
        const modal = document.getElementById('tpl-delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeDeleteModal() {
        this.templateToDelete = null;
        const modal = document.getElementById('tpl-delete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async confirmDelete() {
        if (!this.templateToDelete) return;
        
        try {
            const response = await fetch(`/api/available-templates/${this.templateToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', 'Template berhasil dihapus');
                this.closeDeleteModal();
                this.clearCache();
                this.loadTemplates(true);
            } else {
                throw new Error(result.message || 'Gagal menghapus template');
            }
        } catch (error) {
            console.error('Error deleting template:', error);
            showAlert('error', 'Error', 'Gagal menghapus template');
        }
    },

    refresh() {
        this.clearCache();
        this.loadTemplates(true);
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
