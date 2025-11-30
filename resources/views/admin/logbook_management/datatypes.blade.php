{{-- Tab Content: Data Types Management --}}
<div id="tab-datatypes" class="tab-content hidden">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Tipe Data</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-datatypes">0</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-database text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Tipe Aktif</p>
                    <p class="text-3xl font-bold text-gray-800" id="active-datatypes">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Tipe Nonaktif</p>
                    <p class="text-3xl font-bold text-gray-800" id="inactive-datatypes">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Terakhir Diperbarui</p>
                    <p class="text-lg font-bold text-gray-800" id="last-updated-datatypes">-</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions and Search -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Cari Tipe Data
                </label>
                <input type="text" id="search-datatype-input" placeholder="Cari berdasarkan nama atau deskripsi..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button onclick="DataTypesManager.showCreateModal()" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Tipe Data
                </button>
                <button onclick="DataTypesManager.refresh()" 
                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Data Types Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nama Tipe Data
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Deskripsi
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Tanggal Dibuat
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="datatypes-tbody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">Memuat data tipe data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="datatype-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900" id="datatype-modal-title">Tambah Tipe Data</h3>
                    <button onclick="DataTypesManager.closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="datatype-form" onsubmit="DataTypesManager.submitForm(event)">
                    <input type="hidden" id="datatype-id" value="">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Tipe Data <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="datatype-name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: text, number, date, gambar">
                        <p class="text-xs text-gray-500 mt-1">Nama unik untuk tipe data (huruf kecil, tanpa spasi)</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea id="datatype-description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Deskripsi singkat tentang tipe data ini"></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="datatype-active" checked
                                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Aktifkan tipe data ini</span>
                        </label>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="DataTypesManager.closeModal()" 
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                            Batal
                        </button>
                        <button type="submit" id="datatype-submit-btn"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-datatype-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin menghapus tipe data <strong id="delete-datatype-name"></strong>?
                    <br><span class="text-sm text-red-600 mt-2 block">Tindakan ini tidak dapat dibatalkan!</span>
                </p>
                
                <div class="flex gap-3">
                    <button onclick="DataTypesManager.closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        Batal
                    </button>
                    <button onclick="DataTypesManager.confirmDelete()" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data Types Manager Module
const DataTypesManager = {
    allDataTypes: [],
    dataTypeToDelete: null,
    editingId: null,
    CACHE_KEY: 'available_datatypes_cache',
    CACHE_DURATION: 10 * 60 * 1000, // 10 MENIT

    init() {
        this.loadDataTypes();
        
        const searchInput = document.getElementById('search-datatype-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterDataTypes(e.target.value);
            });
        }
    },

    isValidCache() {
        const cached = localStorage.getItem(this.CACHE_KEY);
        if (!cached) return false;
        
        try {
            const { timestamp } = JSON.parse(cached);
            const age = Date.now() - timestamp;
            return age < this.CACHE_DURATION;
        } catch (e) {
            return false;
        }
    },

    getCache() {
        if (!this.isValidCache()) return null;
        
        try {
            const cached = localStorage.getItem(this.CACHE_KEY);
            const { data } = JSON.parse(cached);
            return data;
        } catch (e) {
            return null;
        }
    },

    setCache(data) {
        try {
            const cacheObject = { data: data, timestamp: Date.now() };
            localStorage.setItem(this.CACHE_KEY, JSON.stringify(cacheObject));
        } catch (e) {
            console.error('Error saving cache:', e);
        }
    },

    clearCache() {
        localStorage.removeItem(this.CACHE_KEY);
    },

    async loadDataTypes(forceRefresh = false) {
        try {
            if (!forceRefresh) {
                const cachedData = this.getCache();
                if (cachedData !== null) {
                    this.allDataTypes = cachedData;
                    this.updateStats();
                    this.renderTable(this.allDataTypes);
                    return;
                }
            }
            
            const token = localStorage.getItem('admin_token');
            const response = await fetch('/api/available-data-types', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success && result.data) {
                this.allDataTypes = result.data;
                this.setCache(this.allDataTypes);
                this.updateStats();
                this.renderTable(this.allDataTypes);
                
                if (forceRefresh) {
                    LogbookManagement.showSuccess('Data tipe data diperbarui dari server');
                }
            } else {
                throw new Error(result.message || 'Failed to load data types');
            }
        } catch (error) {
            console.error('Error loading data types:', error);
            LogbookManagement.showError('Gagal memuat data tipe data');
            this.renderEmptyState();
        }
    },

    updateStats() {
        const total = this.allDataTypes.length;
        const active = this.allDataTypes.filter(dt => dt.is_active).length;
        const inactive = total - active;
        
        document.getElementById('total-datatypes').textContent = total;
        document.getElementById('active-datatypes').textContent = active;
        document.getElementById('inactive-datatypes').textContent = inactive;
        
        // Last updated
        if (this.allDataTypes.length > 0) {
            const lastUpdated = this.allDataTypes.reduce((latest, dt) => {
                const dtDate = new Date(dt.updated_at || dt.created_at);
                return dtDate > latest ? dtDate : latest;
            }, new Date(0));
            document.getElementById('last-updated-datatypes').textContent = LogbookManagement.formatDate(lastUpdated);
        } else {
            document.getElementById('last-updated-datatypes').textContent = '-';
        }
    },

    renderTable(dataTypes) {
        const tbody = document.getElementById('datatypes-tbody');
        
        if (!dataTypes || dataTypes.length === 0) {
            this.renderEmptyState();
            return;
        }

        tbody.innerHTML = dataTypes.map(dt => `
            <tr class="hover:bg-gray-50 transition duration-150">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-code text-indigo-600"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-semibold text-gray-900">${LogbookManagement.escapeHtml(dt.name)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">${dt.description ? LogbookManagement.escapeHtml(dt.description) : '<span class="text-gray-400 italic">Tidak ada deskripsi</span>'}</div>
                </td>
                <td class="px-6 py-4">
                    <button onclick="DataTypesManager.toggleStatus('${dt.id}')" 
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium cursor-pointer transition duration-200 ${
                            dt.is_active 
                                ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                : 'bg-red-100 text-red-800 hover:bg-red-200'
                        }">
                        <i class="fas ${dt.is_active ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                        ${dt.is_active ? 'Aktif' : 'Nonaktif'}
                    </button>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">${LogbookManagement.formatDate(dt.created_at)}</div>
                    <div class="text-xs text-gray-500">${LogbookManagement.formatTime(dt.created_at)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="DataTypesManager.showEditModal('${dt.id}')" 
                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition duration-200"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="DataTypesManager.showDeleteModal('${dt.id}', '${LogbookManagement.escapeHtml(dt.name)}')" 
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                            title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderEmptyState() {
        const tbody = document.getElementById('datatypes-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <i class="fas fa-database text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg mb-2">Tidak ada tipe data ditemukan</p>
                    <p class="text-gray-400 text-sm mb-4">Klik tombol "Tambah Tipe Data" untuk membuat yang baru</p>
                    <button onclick="DataTypesManager.showCreateModal()" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Tambah Tipe Data
                    </button>
                </td>
            </tr>
        `;
    },

    filterDataTypes(searchTerm) {
        if (!searchTerm.trim()) {
            this.renderTable(this.allDataTypes);
            return;
        }

        const filtered = this.allDataTypes.filter(dt => {
            const search = searchTerm.toLowerCase();
            return dt.name.toLowerCase().includes(search) ||
                   (dt.description && dt.description.toLowerCase().includes(search));
        });

        this.renderTable(filtered);
    },

    showCreateModal() {
        this.editingId = null;
        document.getElementById('datatype-modal-title').textContent = 'Tambah Tipe Data';
        document.getElementById('datatype-submit-btn').textContent = 'Simpan';
        document.getElementById('datatype-id').value = '';
        document.getElementById('datatype-name').value = '';
        document.getElementById('datatype-description').value = '';
        document.getElementById('datatype-active').checked = true;
        
        const modal = document.getElementById('datatype-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    showEditModal(id) {
        const dataType = this.allDataTypes.find(dt => dt.id === id);
        if (!dataType) return;
        
        this.editingId = id;
        document.getElementById('datatype-modal-title').textContent = 'Edit Tipe Data';
        document.getElementById('datatype-submit-btn').textContent = 'Perbarui';
        document.getElementById('datatype-id').value = id;
        document.getElementById('datatype-name').value = dataType.name;
        document.getElementById('datatype-description').value = dataType.description || '';
        document.getElementById('datatype-active').checked = dataType.is_active;
        
        const modal = document.getElementById('datatype-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeModal() {
        this.editingId = null;
        const modal = document.getElementById('datatype-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async submitForm(event) {
        event.preventDefault();
        
        const name = document.getElementById('datatype-name').value.trim();
        const description = document.getElementById('datatype-description').value.trim();
        const isActive = document.getElementById('datatype-active').checked;
        
        if (!name) {
            LogbookManagement.showError('Nama tipe data harus diisi');
            return;
        }
        
        const token = localStorage.getItem('admin_token');
        const isEdit = !!this.editingId;
        const url = isEdit 
            ? `/api/available-data-types/${this.editingId}`
            : '/api/available-data-types';
        const method = isEdit ? 'PUT' : 'POST';
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    description: description || null,
                    is_active: isActive
                })
            });

            const result = await response.json();
            
            if (result.success) {
                LogbookManagement.showSuccess(isEdit ? 'Tipe data berhasil diperbarui' : 'Tipe data berhasil ditambahkan');
                this.closeModal();
                this.clearCache();
                await this.loadDataTypes(true);
            } else {
                throw new Error(result.message || result.errors?.name?.[0] || 'Failed to save data type');
            }
        } catch (error) {
            console.error('Error saving data type:', error);
            LogbookManagement.showError('Gagal menyimpan tipe data. ' + error.message);
        }
    },

    async toggleStatus(id) {
        const token = localStorage.getItem('admin_token');
        
        try {
            const response = await fetch(`/api/available-data-types/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                LogbookManagement.showSuccess('Status tipe data berhasil diubah');
                this.clearCache();
                await this.loadDataTypes(true);
            } else {
                throw new Error(result.message || 'Failed to toggle status');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            LogbookManagement.showError('Gagal mengubah status. ' + error.message);
        }
    },

    showDeleteModal(id, name) {
        this.dataTypeToDelete = id;
        const modal = document.getElementById('delete-datatype-modal');
        document.getElementById('delete-datatype-name').textContent = name;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeDeleteModal() {
        this.dataTypeToDelete = null;
        const modal = document.getElementById('delete-datatype-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async confirmDelete() {
        if (!this.dataTypeToDelete) return;

        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch(`/api/available-data-types/${this.dataTypeToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                LogbookManagement.showSuccess('Tipe data berhasil dihapus');
                this.closeDeleteModal();
                this.clearCache();
                await this.loadDataTypes(true);
            } else {
                throw new Error(result.message || 'Failed to delete data type');
            }
        } catch (error) {
            console.error('Error deleting data type:', error);
            LogbookManagement.showError('Gagal menghapus tipe data. ' + error.message);
        }
    },

    refresh() {
        this.clearCache();
        this.loadDataTypes(true);
    }
};
</script>
