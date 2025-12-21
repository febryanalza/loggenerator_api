{{-- Tab Content: Required Data Participants Management --}}
<div id="required-data-content">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Field</p>
                    <p class="text-2xl font-bold text-gray-800" id="rd-stat-total">0</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Aktif</p>
                    <p class="text-2xl font-bold text-green-600" id="rd-stat-active">0</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Tidak Aktif</p>
                    <p class="text-2xl font-bold text-gray-600" id="rd-stat-inactive">0</p>
                </div>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <i class="fas fa-times-circle text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Institusi</p>
                    <p class="text-lg font-bold text-purple-600" id="rd-stat-institution">-</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-building text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Cari Field
                </label>
                <input type="text" id="rd-search-input" placeholder="Cari berdasarkan nama field..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="w-full md:w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i>Status
                </label>
                <select id="rd-status-filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>
            <button onclick="RequiredDataManager.refresh()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <button onclick="RequiredDataManager.showCreateModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i>Tambah Field
            </button>
        </div>
    </div>

    <!-- Required Data Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Field</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="rd-table-body">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Memuat data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Required Data Modal -->
<div id="rd-form-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl sticky top-0 z-10">
            <h3 class="text-xl font-bold text-gray-800" id="rd-modal-title">
                <i class="fas fa-list text-green-600 mr-2"></i>Tambah Field Data Participant
            </h3>
            <button onclick="RequiredDataManager.closeFormModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="rd-form" onsubmit="RequiredDataManager.submitForm(event)" class="p-6">
            <input type="hidden" id="rd-edit-id">
            <input type="hidden" id="rd-institution-id">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building text-purple-600 mr-1"></i>Institusi
                </label>
                <input type="text" id="rd-institution-name" readonly
                    class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-blue-600 mr-1"></i>Nama Field <span class="text-red-500">*</span>
                </label>
                <input type="text" id="rd-data-name" required
                    placeholder="Contoh: Nama Lengkap, NIM, Email, Nomor Telepon"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Nama field yang akan digunakan saat mengisi data participant
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-toggle-on text-green-600 mr-1"></i>Status
                </label>
                <select id="rd-is-active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Field tidak aktif tidak akan muncul saat pengisian data
                </p>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="RequiredDataManager.closeFormModal()" 
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="submit" id="rd-submit-btn"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Required Data Modal -->
<div id="rd-view-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-eye text-blue-600 mr-2"></i>Detail Field Data Participant
            </h3>
            <button onclick="RequiredDataManager.closeViewModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6" id="rd-view-content">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="rd-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6 border-b bg-red-50 rounded-t-xl">
            <h3 class="text-xl font-bold text-red-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus field <strong id="rd-delete-name"></strong>?</p>
            <p class="text-sm text-red-600 mb-6">
                <i class="fas fa-info-circle mr-1"></i>Field yang dihapus tidak dapat dikembalikan!
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="RequiredDataManager.closeDeleteModal()" 
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button onclick="RequiredDataManager.confirmDelete()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                    <i class="fas fa-trash-alt mr-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Required Data Manager Module
const RequiredDataManager = {
    allRequiredData: [],
    institutionId: null,
    institutionName: null,
    editingId: null,
    dataToDelete: null,
    CACHE_KEY: 'inst_required_data_cache',
    CACHE_DURATION: 60 * 60 * 1000, // 1 hour cache

    init(institutionId) {
        this.institutionId = institutionId;
        this.loadInstitutionInfo();
        this.loadRequiredData();
        this.setupEventListeners();
    },

    setupEventListeners() {
        const searchInput = document.getElementById('rd-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => this.filterData());
        }
        
        const statusFilter = document.getElementById('rd-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.filterData());
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
                this.institutionName = user.institution?.name || user.institution_name || 'Unknown Institution';
                document.getElementById('rd-stat-institution').textContent = this.institutionName;
            } catch (e) {
                console.error('Failed to parse user data:', e);
            }
        }
    },

    async loadRequiredData(forceRefresh = false) {
        try {
            // Check cache first
            if (!forceRefresh) {
                const cached = this.getCache();
                if (cached) {
                    console.log('Loading required data from cache');
                    this.allRequiredData = cached;
                    this.updateStats();
                    this.renderTable(this.allRequiredData);
                    return;
                }
            }

            const response = await fetch(`/api/required-data-participants/institution/${this.institutionId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.allRequiredData = result.data.required_data || [];
                    this.setCache(this.allRequiredData);
                    this.updateStats();
                    this.renderTable(this.allRequiredData);
                    console.log('Loaded required data:', this.allRequiredData.length, 'items');
                } else {
                    throw new Error(result.message || 'Failed to load required data');
                }
            } else if (response.status === 401) {
                showAlert('error', 'Sesi Berakhir', 'Silakan login ulang');
                setTimeout(() => window.location.href = '/login', 1500);
            } else {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to load required data');
            }
        } catch (error) {
            console.error('Error loading required data:', error);
            showAlert('error', 'Error', error.message);
            this.renderEmptyState('Gagal memuat data');
        }
    },

    updateStats() {
        const total = this.allRequiredData.length;
        const active = this.allRequiredData.filter(d => d.is_active).length;
        const inactive = total - active;
        
        document.getElementById('rd-stat-total').textContent = total;
        document.getElementById('rd-stat-active').textContent = active;
        document.getElementById('rd-stat-inactive').textContent = inactive;
    },

    filterData() {
        const searchTerm = document.getElementById('rd-search-input').value.toLowerCase();
        const statusFilter = document.getElementById('rd-status-filter').value;

        let filtered = this.allRequiredData.filter(data => {
            const matchSearch = !searchTerm || 
                data.data_name.toLowerCase().includes(searchTerm);

            const matchStatus = !statusFilter || 
                (statusFilter === 'active' && data.is_active) ||
                (statusFilter === 'inactive' && !data.is_active);

            return matchSearch && matchStatus;
        });

        this.renderTable(filtered);
    },

    renderTable(dataList) {
        const tbody = document.getElementById('rd-table-body');
        
        if (!dataList || dataList.length === 0) {
            this.renderEmptyState();
            return;
        }

        tbody.innerHTML = dataList.map((data, index) => `
            <tr class="hover:bg-gray-50 transition duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <i class="fas fa-tag text-blue-600 mr-2"></i>
                        <span class="text-sm font-medium text-gray-900">${this.escapeHtml(data.data_name)}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${data.is_active 
                        ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Aktif</span>'
                        : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-times-circle mr-1"></i>Tidak Aktif</span>'
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <i class="fas fa-calendar mr-1"></i>${this.formatDate(data.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button onclick="RequiredDataManager.viewData('${data.id}')" 
                        class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="RequiredDataManager.showEditModal('${data.id}')" 
                        class="text-green-600 hover:text-green-900" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="RequiredDataManager.toggleStatus('${data.id}')" 
                        class="text-yellow-600 hover:text-yellow-900" title="Toggle Status">
                        <i class="fas fa-toggle-on"></i>
                    </button>
                    <button onclick="RequiredDataManager.showDeleteModal('${data.id}', '${this.escapeHtml(data.data_name)}')" 
                        class="text-red-600 hover:text-red-900" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    },

    renderEmptyState(message = null) {
        const tbody = document.getElementById('rd-table-body');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <i class="fas fa-folder-open text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg">${message || 'Belum ada field data participant'}</p>
                    <button onclick="RequiredDataManager.showCreateModal()" 
                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Tambah Field Pertama
                    </button>
                </td>
            </tr>
        `;
    },

    showCreateModal() {
        this.editingId = null;
        
        document.getElementById('rd-modal-title').innerHTML = '<i class="fas fa-list text-green-600 mr-2"></i>Tambah Field Data Participant';
        document.getElementById('rd-form').reset();
        document.getElementById('rd-edit-id').value = '';
        document.getElementById('rd-institution-id').value = this.institutionId;
        document.getElementById('rd-institution-name').value = this.institutionName;
        document.getElementById('rd-is-active').value = '1';
        
        const modal = document.getElementById('rd-form-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    async showEditModal(id) {
        try {
            const data = this.allRequiredData.find(d => d.id === id);
            if (!data) {
                showAlert('error', 'Error', 'Data tidak ditemukan');
                return;
            }

            this.editingId = id;
            
            document.getElementById('rd-modal-title').innerHTML = '<i class="fas fa-edit text-green-600 mr-2"></i>Edit Field Data Participant';
            document.getElementById('rd-edit-id').value = data.id;
            document.getElementById('rd-institution-id').value = this.institutionId;
            document.getElementById('rd-institution-name').value = this.institutionName;
            document.getElementById('rd-data-name').value = data.data_name;
            document.getElementById('rd-is-active').value = data.is_active ? '1' : '0';
            
            const modal = document.getElementById('rd-form-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } catch (error) {
            console.error('Error loading data for edit:', error);
            showAlert('error', 'Error', 'Gagal memuat data untuk diedit');
        }
    },

    closeFormModal() {
        const modal = document.getElementById('rd-form-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        this.editingId = null;
    },

    async submitForm(event) {
        event.preventDefault();
        
        const dataName = document.getElementById('rd-data-name').value.trim();
        const isActive = document.getElementById('rd-is-active').value === '1';
        
        if (!dataName) {
            showAlert('error', 'Validasi Error', 'Nama field harus diisi');
            return;
        }
        
        const submitBtn = document.getElementById('rd-submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
        
        try {
            const url = this.editingId 
                ? `/api/required-data-participants/${this.editingId}`
                : '/api/required-data-participants';
            
            const method = this.editingId ? 'PUT' : 'POST';
            
            const requestData = {
                institution_id: this.institutionId,
                data_name: dataName,
                is_active: isActive
            };

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', result.message || (this.editingId ? 'Field berhasil diupdate' : 'Field berhasil ditambahkan'));
                this.closeFormModal();
                this.loadRequiredData(true);
            } else {
                throw new Error(result.message || 'Gagal menyimpan data');
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            showAlert('error', 'Error', error.message || 'Gagal menyimpan data');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan';
        }
    },

    async viewData(id) {
        try {
            const data = this.allRequiredData.find(d => d.id === id);
            if (!data) {
                showAlert('error', 'Error', 'Data tidak ditemukan');
                return;
            }

            const content = `
                <div class="space-y-4">
                    <div class="border-b pb-3">
                        <label class="text-sm font-medium text-gray-500">Nama Field</label>
                        <p class="text-lg font-semibold text-gray-800 mt-1">
                            <i class="fas fa-tag text-blue-600 mr-2"></i>${this.escapeHtml(data.data_name)}
                        </p>
                    </div>
                    
                    <div class="border-b pb-3">
                        <label class="text-sm font-medium text-gray-500">Institusi</label>
                        <p class="text-gray-800 mt-1">
                            <i class="fas fa-building text-purple-600 mr-2"></i>${this.escapeHtml(data.institution?.name || this.institutionName)}
                        </p>
                    </div>
                    
                    <div class="border-b pb-3">
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <p class="mt-1">
                            ${data.is_active 
                                ? '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Aktif</span>'
                                : '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800"><i class="fas fa-times-circle mr-1"></i>Tidak Aktif</span>'
                            }
                        </p>
                    </div>
                    
                    <div class="border-b pb-3">
                        <label class="text-sm font-medium text-gray-500">Dibuat Pada</label>
                        <p class="text-gray-800 mt-1">
                            <i class="fas fa-calendar text-gray-600 mr-2"></i>${this.formatDate(data.created_at)}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Terakhir Diupdate</label>
                        <p class="text-gray-800 mt-1">
                            <i class="fas fa-clock text-gray-600 mr-2"></i>${this.formatDate(data.updated_at)}
                        </p>
                    </div>
                </div>
            `;

            document.getElementById('rd-view-content').innerHTML = content;
            
            const modal = document.getElementById('rd-view-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } catch (error) {
            console.error('Error viewing data:', error);
            showAlert('error', 'Error', 'Gagal memuat detail data');
        }
    },

    closeViewModal() {
        const modal = document.getElementById('rd-view-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async toggleStatus(id) {
        try {
            const response = await fetch(`/api/required-data-participants/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', result.message || 'Status berhasil diubah');
                this.loadRequiredData(true);
            } else {
                throw new Error(result.message || 'Gagal mengubah status');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            showAlert('error', 'Error', error.message);
        }
    },

    showDeleteModal(id, name) {
        this.dataToDelete = id;
        document.getElementById('rd-delete-name').textContent = name;
        const modal = document.getElementById('rd-delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeDeleteModal() {
        this.dataToDelete = null;
        const modal = document.getElementById('rd-delete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async confirmDelete() {
        if (!this.dataToDelete) return;
        
        try {
            const response = await fetch(`/api/required-data-participants/${this.dataToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert('success', 'Berhasil', result.message || 'Field berhasil dihapus');
                this.closeDeleteModal();
                this.loadRequiredData(true);
            } else {
                throw new Error(result.message || 'Gagal menghapus field');
            }
        } catch (error) {
            console.error('Error deleting data:', error);
            showAlert('error', 'Error', error.message);
        }
    },

    refresh() {
        this.clearCache();
        this.loadRequiredData(true);
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
</script>
