{{-- Tab Content: Logbook Management --}}
<div id="tab-templates" class="tab-content">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.logbook.stats.total_logbook') }}</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-templates">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.logbook.stats.total_entries') }}</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-entries">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.logbook.stats.active_today') }}</p>
                    <p class="text-3xl font-bold text-gray-800" id="active-today">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.logbook.stats.total_creators') }}</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-creators">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> {{ __('logbook.logbook.search') }}
                </label>
                <input type="text" id="search-input" placeholder="{{ __('logbook.logbook.search_placeholder') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <button onclick="TemplatesManager.refresh()" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> {{ __('logbook.logbook.refresh') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Logbook Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.logbook.table.logbook') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.logbook.table.creator') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.logbook.table.institution') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.logbook.table.entries_count') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.logbook.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody id="templates-tbody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">{{ __('logbook.logbook.loading') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-template-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">{{ __('logbook.logbook.delete_confirm') }}</h3>
                <p class="text-gray-600 text-center mb-6">
                    {{ __('logbook.logbook.delete_message') }} <strong id="delete-template-name"></strong>?
                    <br><span class="text-sm text-red-600 mt-2 block">{{ __('logbook.logbook.delete_warning') }}</span>
                </p>
                
                <div class="flex gap-3">
                    <button onclick="TemplatesManager.closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        {{ __('logbook.logbook.cancel') }}
                    </button>
                    <button onclick="TemplatesManager.confirmDelete()" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        {{ __('logbook.logbook.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.logbookTranslations = {!! json_encode([
    'entries' => __('logbook.logbook.table.entries_count'),
    'viewDetail' => __('logbook.logbook.actions_view'),
    'deleteLogbook' => __('logbook.logbook.actions_delete'),
    'noData' => __('logbook.logbook.no_data'),
    'noDataDesc' => __('logbook.logbook.no_data_desc'),
]) !!};
</script>
<script>
// Templates Manager Module
const TemplatesManager = {
    allTemplates: [],
    templateToDelete: null,
    CACHE_KEY: 'logbook_templates_cache',
    CACHE_DURATION: 10 * 60 * 1000, // 10 MENIT

    init() {
        this.loadTemplates();
        
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterTemplates(e.target.value);
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

    async loadTemplates(forceRefresh = false) {
        try {
            if (!forceRefresh) {
                const cachedData = this.getCache();
                if (cachedData !== null) {
                    this.allTemplates = cachedData;
                    this.updateStats();
                    this.renderTable(this.allTemplates);
                    return;
                }
            }
            
            const token = localStorage.getItem('admin_token');
            const response = await fetch('/api/templates/admin/all', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success && result.data) {
                this.allTemplates = result.data;
                this.setCache(this.allTemplates);
                this.updateStats();
                this.renderTable(this.allTemplates);
                
                if (forceRefresh) {
                    LogbookManagement.showSuccess('Data logbook diperbarui dari server');
                }
            } else {
                throw new Error(result.message || 'Failed to load logbooks');
            }
        } catch (error) {
            console.error('Error loading logbooks:', error);
            LogbookManagement.showError('Gagal memuat data logbook');
            this.renderEmptyState();
        }
    },

    updateStats() {
        document.getElementById('total-templates').textContent = this.allTemplates.length;
        
        const totalEntries = this.allTemplates.reduce((sum, t) => sum + parseInt(t.entries_count || 0), 0);
        document.getElementById('total-entries').textContent = totalEntries;
        
        const today = new Date().toDateString();
        const activeToday = this.allTemplates.filter(t => {
            const createdDate = new Date(t.created_at).toDateString();
            return createdDate === today;
        }).length;
        document.getElementById('active-today').textContent = activeToday;
        
        const uniqueCreators = new Set(this.allTemplates.map(t => t.creator_email));
        document.getElementById('total-creators').textContent = uniqueCreators.size;
    },

    renderTable(templates) {
        const tbody = document.getElementById('templates-tbody');
        
        if (!templates || templates.length === 0) {
            this.renderEmptyState();
            return;
        }

        tbody.innerHTML = templates.map(template => `
            <tr class="hover:bg-gray-50 transition duration-150">
                <td class="px-6 py-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-semibold text-gray-900">${LogbookManagement.escapeHtml(template.name)}</div>
                            ${template.description ? `<div class="text-sm text-gray-500 mt-1">${LogbookManagement.escapeHtml(template.description)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            ${LogbookManagement.getInitials(template.creator_name)}
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">${LogbookManagement.escapeHtml(template.creator_name)}</div>
                            <div class="text-xs text-gray-500">${LogbookManagement.escapeHtml(template.creator_email)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    ${template.institution_name 
                        ? `<div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-green-600 text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900">${LogbookManagement.escapeHtml(template.institution_name)}</div>
                            </div>
                        </div>`
                        : `<span class="text-gray-400">-</span>`
                    }
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                        template.entries_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    }">
                        <i class="fas fa-list mr-1"></i> ${template.entries_count || 0} ${window.logbookTranslations.entries}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="TemplatesManager.viewTemplate('${template.id}')" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition duration-200"
                            title="${window.logbookTranslations.viewDetail}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="TemplatesManager.showDeleteModal('${template.id}', '${LogbookManagement.escapeHtml(template.name)}')" 
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                            title="${window.logbookTranslations.deleteLogbook}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderEmptyState() {
        const tbody = document.getElementById('templates-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg mb-2">${window.logbookTranslations.noData}</p>
                    <p class="text-gray-400 text-sm">${window.logbookTranslations.noDataDesc}</p>
                </td>
            </tr>
        `;
    },

    filterTemplates(searchTerm) {
        if (!searchTerm.trim()) {
            this.renderTable(this.allTemplates);
            return;
        }

        const filtered = this.allTemplates.filter(template => {
            const search = searchTerm.toLowerCase();
            return template.name.toLowerCase().includes(search) ||
                   template.creator_name.toLowerCase().includes(search) ||
                   template.creator_email.toLowerCase().includes(search) ||
                   (template.description && template.description.toLowerCase().includes(search));
        });

        this.renderTable(filtered);
    },

    showDeleteModal(templateId, templateName) {
        this.templateToDelete = templateId;
        const modal = document.getElementById('delete-template-modal');
        document.getElementById('delete-template-name').textContent = templateName;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeDeleteModal() {
        this.templateToDelete = null;
        const modal = document.getElementById('delete-template-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async confirmDelete() {
        if (!this.templateToDelete) return;

        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch(`/api/templates/${this.templateToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                LogbookManagement.showSuccess('Logbook berhasil dihapus');
                this.closeDeleteModal();
                this.clearCache();
                await this.loadTemplates(true);
            } else {
                throw new Error(result.message || 'Failed to delete logbook');
            }
        } catch (error) {
            console.error('Error deleting logbook:', error);
            LogbookManagement.showError('Gagal menghapus logbook. ' + error.message);
        }
    },

    viewTemplate(templateId) {
        window.location.href = `/admin/logbook/${templateId}`;
    },

    refresh() {
        this.clearCache();
        this.loadTemplates(true);
    }
};
</script>
