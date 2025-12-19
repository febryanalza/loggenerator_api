{{-- Tab Content: Available Templates Management --}}
<div id="tab-availabletemplates" class="tab-content hidden">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.templates.stats.total') }}</p>
                    <p class="text-3xl font-bold text-gray-800" id="avt-total">0</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-layer-group text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.templates.stats.active') }}</p>
                    <p class="text-3xl font-bold text-green-600" id="avt-active">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.templates.stats.inactive') }}</p>
                    <p class="text-3xl font-bold text-red-600" id="avt-inactive">0</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('logbook.templates.stats.institutions') }}</p>
                    <p class="text-3xl font-bold text-gray-800" id="avt-institutions">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
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
                    <i class="fas fa-search mr-1"></i> {{ __('logbook.templates.search') }}
                </label>
                <input type="text" id="avt-search-input" placeholder="{{ __('logbook.templates.search_placeholder') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button onclick="AvailableTemplatesManager.showCreateModal()" 
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-plus"></i> {{ __('logbook.templates.create_button') }}
                </button>
                <button onclick="AvailableTemplatesManager.refresh()" 
                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> {{ __('logbook.templates.refresh') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.templates.table.name') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.templates.table.institution') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.templates.table.columns') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.templates.table.status') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            {{ __('logbook.templates.table.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody id="avt-tbody" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">{{ __('logbook.templates.loading') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="avt-form-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900" id="avt-modal-title">{{ __('logbook.templates.modal_create_title') }}</h3>
                    <button onclick="AvailableTemplatesManager.closeFormModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="avt-form" onsubmit="AvailableTemplatesManager.submitForm(event)">
                    <input type="hidden" id="avt-id">
                    
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('logbook.templates.form.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="avt-name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="{{ __('logbook.templates.form.name_placeholder') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('logbook.templates.form.institution') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="avt-institution" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">{{ __('logbook.templates.form.select_institution') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('logbook.templates.form.description') }}
                        </label>
                        <textarea id="avt-description" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="{{ __('logbook.templates.form.description_placeholder') }}"></textarea>
                    </div>

                    <!-- Is Active Checkbox -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="avt-is-active" checked
                                class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            <label for="avt-is-active" class="text-sm font-medium text-gray-700 cursor-pointer">
                                {{ __('logbook.templates.form.activate_template') }}
                            </label>
                        </div>
                    </div>

                    <!-- Required Columns Section -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                {{ __('logbook.templates.form.columns') }} <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="AvailableTemplatesManager.addColumn()"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2">
                                <i class="fas fa-plus"></i> {{ __('logbook.templates.form.add_column') }}
                            </button>
                        </div>
                        
                        <div id="avt-columns-container" class="space-y-4">
                            <!-- Columns will be added here dynamically -->
                        </div>
                        
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('logbook.templates.form.columns_info') }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3 pt-4 border-t">
                        <button type="button" onclick="AvailableTemplatesManager.closeFormModal()"
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                            {{ __('logbook.templates.cancel') }}
                        </button>
                        <button type="submit" id="avt-submit-btn"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                            {{ __('logbook.templates.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Template Modal -->
    <div id="avt-view-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ __('logbook.templates.view_detail') }}</h3>
                    <button onclick="AvailableTemplatesManager.closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="avt-view-content">
                    <!-- Content will be loaded here -->
                </div>
                
                <div class="flex gap-3 pt-4 border-t mt-6">
                    <button type="button" onclick="AvailableTemplatesManager.closeViewModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        {{ __('logbook.templates.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="avt-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">{{ __('logbook.templates.delete_confirm_title') }}</h3>
                <p class="text-gray-600 text-center mb-6">
                    {{ __('logbook.templates.delete_confirm_message') }} <strong id="avt-delete-name"></strong>?
                    <br><span class="text-sm text-red-600 mt-2 block">{{ __('logbook.templates.delete_warning') }}</span>
                </p>
                
                <div class="flex gap-3">
                    <button onclick="AvailableTemplatesManager.closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        {{ __('logbook.templates.cancel') }}
                    </button>
                    <button onclick="AvailableTemplatesManager.confirmDelete()" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        {{ __('logbook.templates.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Translations for JavaScript
window.templatesTranslations = {!! json_encode(['statusActive' => __('logbook.templates.status_active'), 'statusInactive' => __('logbook.templates.status_inactive'), 'actionsView' => __('logbook.templates.actions_view'), 'actionsEdit' => __('logbook.templates.actions_edit'), 'actionsDelete' => __('logbook.templates.actions_delete'), 'noData' => __('logbook.templates.no_data'), 'noDataDesc' => __('logbook.templates.no_data_description'), 'modalCreateTitle' => __('logbook.templates.modal_create_title'), 'modalEditTitle' => __('logbook.templates.modal_edit_title'), 'save' => __('logbook.templates.save'), 'update' => __('logbook.templates.update'), 'entries' => __('logbook.templates.entries'), 'columns' => __('logbook.templates.form.columns'), 'columnName' => __('logbook.templates.form.column_name'), 'columnNamePlaceholder' => __('logbook.templates.form.column_name_placeholder'), 'columnType' => __('logbook.templates.form.column_type'), 'selectColumnType' => __('logbook.templates.form.select_column_type'), 'columnRequired' => __('logbook.templates.form.column_required'), 'required' => __('logbook.templates.form.required_label'), 'optional' => __('logbook.templates.form.optional_label'), 'removeColumn' => __('logbook.templates.form.remove_column'), 'viewDetailTemplate' => __('logbook.templates.view_detail_template'), 'viewDetailInstitution' => __('logbook.templates.view_detail_institution'), 'viewDetailStatus' => __('logbook.templates.view_detail_status'), 'viewDetailDescription' => __('logbook.templates.form.description'), 'viewDetailColumns' => __('logbook.templates.view_detail_columns'), 'viewDetailColumnName' => __('logbook.templates.view_detail_column_name'), 'viewDetailColumnType' => __('logbook.templates.view_detail_column_type'), 'viewDetailColumnRequired' => __('logbook.templates.view_detail_column_required')]) !!};

// Available Templates Manager Module
const AvailableTemplatesManager = {
    allTemplates: [],
    institutions: [],
    dataTypes: [],
    templateToDelete: null,
    editingId: null,
    columnCounter: 0,
    CACHE_KEY: 'available_templates_cache',
    CACHE_DURATION: 10 * 60 * 1000, // 10 minutes

    init() {
        this.loadInstitutions();
        this.loadDataTypes();
        this.loadTemplates();
        
        const searchInput = document.getElementById('avt-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterTemplates(e.target.value);
            });
        }
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
            console.error('Error saving cache:', e);
        }
    },

    clearCache() {
        localStorage.removeItem(this.CACHE_KEY);
    },

    async loadInstitutions() {
        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch('/api/institutions', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success && result.data) {
                this.institutions = result.data;
                this.populateInstitutionDropdown();
            }
        } catch (error) {
            console.error('Error loading institutions:', error);
        }
    },

    async loadDataTypes() {
        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch('/api/available-data-types/active', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success && result.data) {
                this.dataTypes = result.data;
            }
        } catch (error) {
            console.error('Error loading data types:', error);
        }
    },

    populateInstitutionDropdown() {
        const select = document.getElementById('avt-institution');
        if (!select) return;
        
        select.innerHTML = '<option value="">Pilih Institusi</option>' + 
            this.institutions.map(inst => 
                `<option value="${inst.id}">${LogbookManagement.escapeHtml(inst.name)}</option>`
            ).join('');
    },

    async loadTemplates(forceRefresh = false) {
        try {
            if (!forceRefresh) {
                const cachedData = this.getCache();
                if (cachedData) {
                    this.allTemplates = cachedData;
                    this.updateStats();
                    this.renderTable(this.allTemplates);
                    return;
                }
            }

            const token = localStorage.getItem('admin_token');
            const response = await fetch('/api/available-templates', {
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
                    LogbookManagement.showSuccess('Data template diperbarui dari server');
                }
            } else {
                throw new Error(result.message || 'Failed to load templates');
            }
        } catch (error) {
            console.error('Error loading templates:', error);
            LogbookManagement.showError('Gagal memuat data template');
            this.renderEmptyState();
        }
    },

    updateStats() {
        document.getElementById('avt-total').textContent = this.allTemplates.length;
        document.getElementById('avt-active').textContent = this.allTemplates.filter(t => t.is_active).length;
        document.getElementById('avt-inactive').textContent = this.allTemplates.filter(t => !t.is_active).length;
        
        const uniqueInstitutions = new Set(this.allTemplates.map(t => t.institution_id));
        document.getElementById('avt-institutions').textContent = uniqueInstitutions.size;
    },

    renderTable(templates) {
        const tbody = document.getElementById('avt-tbody');
        
        if (!templates || templates.length === 0) {
            this.renderEmptyState();
            return;
        }

        tbody.innerHTML = templates.map(template => `
            <tr class="hover:bg-gray-50 transition duration-150">
                <td class="px-6 py-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-layer-group text-indigo-600"></i>
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
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">${LogbookManagement.escapeHtml(template.institution?.name || 'N/A')}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-columns mr-1"></i> ${(template.required_columns || []).length} ${window.templatesTranslations.columns}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <button onclick="AvailableTemplatesManager.toggleStatus('${template.id}')"
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium cursor-pointer transition duration-200 ${
                            template.is_active 
                                ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                                : 'bg-red-100 text-red-800 hover:bg-red-200'
                        }">
                        <i class="fas ${template.is_active ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                        ${template.is_active ? window.templatesTranslations.statusActive : window.templatesTranslations.statusInactive}
                    </button>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <button onclick="AvailableTemplatesManager.viewTemplate('${template.id}')" 
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition duration-200"
                            title="${window.templatesTranslations.actionsView}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="AvailableTemplatesManager.showEditModal('${template.id}')" 
                            class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition duration-200"
                            title="${window.templatesTranslations.actionsEdit}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="AvailableTemplatesManager.showDeleteModal('${template.id}', '${LogbookManagement.escapeHtml(template.name)}')" 
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                            title="${window.templatesTranslations.actionsDelete}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderEmptyState() {
        const tbody = document.getElementById('avt-tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <i class="fas fa-layer-group text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg mb-2">${window.templatesTranslations.noData}</p>
                    <p class="text-gray-400 text-sm">${window.templatesTranslations.noDataDesc}</p>
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
                   (template.description && template.description.toLowerCase().includes(search)) ||
                   (template.institution?.name && template.institution.name.toLowerCase().includes(search));
        });

        this.renderTable(filtered);
    },

    // Column management for dynamic fields
    addColumn(columnData = null) {
        this.columnCounter++;
        const container = document.getElementById('avt-columns-container');
        
        const columnHtml = `
            <div class="column-item bg-gray-50 rounded-lg p-4 border" id="column-${this.columnCounter}">
                <div class="flex items-start gap-4">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${window.templatesTranslations.columnName} <span class="text-red-500">*</span></label>
                            <input type="text" class="column-name w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="${window.templatesTranslations.columnNamePlaceholder}" value="${columnData?.name || ''}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${window.templatesTranslations.columnType} <span class="text-red-500">*</span></label>
                            <select class="column-type w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                <option value="">${window.templatesTranslations.selectColumnType}</option>
                                ${this.dataTypes.map(dt => 
                                    `<option value="${dt.name}" ${columnData?.data_type === dt.name ? 'selected' : ''}>${dt.name} - ${dt.description || ''}</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${window.templatesTranslations.viewDetailDescription}</label>
                            <input type="text" class="column-desc w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="${window.templatesTranslations.viewDetailDescription}" value="${columnData?.description || ''}">
                        </div>
                    </div>
                    <button type="button" onclick="AvailableTemplatesManager.removeColumn(${this.columnCounter})"
                        class="mt-6 p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                        title="${window.templatesTranslations.removeColumn}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', columnHtml);
    },

    removeColumn(columnId) {
        const column = document.getElementById(`column-${columnId}`);
        if (column) {
            column.remove();
        }
    },

    getColumnsData() {
        const columns = [];
        const columnItems = document.querySelectorAll('.column-item');
        
        columnItems.forEach(item => {
            const name = item.querySelector('.column-name').value.trim();
            const dataType = item.querySelector('.column-type').value;
            const description = item.querySelector('.column-desc').value.trim();
            
            if (name && dataType) {
                columns.push({
                    name: name,
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
        document.getElementById('avt-modal-title').textContent = window.templatesTranslations.modalCreateTitle;
        document.getElementById('avt-submit-btn').textContent = window.templatesTranslations.save;
        document.getElementById('avt-form').reset();
        document.getElementById('avt-id').value = '';
        document.getElementById('avt-is-active').checked = true;
        
        // Clear and add one empty column
        document.getElementById('avt-columns-container').innerHTML = '';
        this.addColumn();
        
        const modal = document.getElementById('avt-form-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    async showEditModal(id) {
        this.editingId = id;
        this.columnCounter = 0;
        
        const template = this.allTemplates.find(t => t.id === id);
        if (!template) {
            LogbookManagement.showError('Template tidak ditemukan');
            return;
        }

        document.getElementById('avt-modal-title').textContent = window.templatesTranslations.modalEditTitle;
        document.getElementById('avt-submit-btn').textContent = window.templatesTranslations.update;
        document.getElementById('avt-id').value = template.id;
        document.getElementById('avt-name').value = template.name;
        document.getElementById('avt-description').value = template.description || '';
        document.getElementById('avt-institution').value = template.institution_id;
        document.getElementById('avt-is-active').checked = template.is_active;
        
        // Load existing columns
        document.getElementById('avt-columns-container').innerHTML = '';
        if (template.required_columns && template.required_columns.length > 0) {
            template.required_columns.forEach(col => {
                this.addColumn(col);
            });
        } else {
            this.addColumn();
        }
        
        const modal = document.getElementById('avt-form-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeFormModal() {
        const modal = document.getElementById('avt-form-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        this.editingId = null;
    },

    async submitForm(event) {
        event.preventDefault();
        
        const columns = this.getColumnsData();
        if (columns.length === 0) {
            LogbookManagement.showError('Minimal 1 kolom diperlukan');
            return;
        }

        const formData = {
            name: document.getElementById('avt-name').value.trim(),
            description: document.getElementById('avt-description').value.trim() || null,
            institution_id: document.getElementById('avt-institution').value,
            is_active: document.getElementById('avt-is-active').checked,
            required_columns: columns
        };

        if (!formData.name || !formData.institution_id) {
            LogbookManagement.showError('Nama template dan institusi wajib diisi');
            return;
        }

        try {
            const token = localStorage.getItem('admin_token');
            const url = this.editingId 
                ? `/api/available-templates/${this.editingId}` 
                : '/api/available-templates';
            const method = this.editingId ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                LogbookManagement.showSuccess(this.editingId ? 'Template berhasil diperbarui' : 'Template berhasil dibuat');
                this.closeFormModal();
                this.clearCache();
                await this.loadTemplates(true);
            } else {
                throw new Error(result.message || result.errors ? JSON.stringify(result.errors) : 'Failed to save template');
            }
        } catch (error) {
            console.error('Error saving template:', error);
            LogbookManagement.showError('Gagal menyimpan template. ' + error.message);
        }
    },

    async viewTemplate(id) {
        const template = this.allTemplates.find(t => t.id === id);
        if (!template) {
            LogbookManagement.showError('Template tidak ditemukan');
            return;
        }

        const columns = template.required_columns || [];
        const columnsHtml = columns.length > 0 
            ? columns.map((col, index) => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">${index + 1}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${LogbookManagement.escapeHtml(col.name)}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            ${col.data_type}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">${col.description || '-'}</td>
                </tr>
            `).join('')
            : '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Tidak ada kolom yang didefinisikan</td></tr>';

        const content = `
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">${window.templatesTranslations.viewDetailTemplate}</p>
                        <p class="text-lg font-semibold text-gray-900">${LogbookManagement.escapeHtml(template.name)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">${window.templatesTranslations.viewDetailInstitution}</p>
                        <p class="text-lg font-semibold text-gray-900">${LogbookManagement.escapeHtml(template.institution?.name || 'N/A')}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">${window.templatesTranslations.viewDetailStatus}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                            template.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">
                            <i class="fas ${template.is_active ? 'fa-check-circle' : 'fa-times-circle'} mr-1"></i>
                            ${template.is_active ? window.templatesTranslations.statusActive : window.templatesTranslations.statusInactive}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">${window.templatesTranslations.viewDetailColumns}</p>
                        <p class="text-lg font-semibold text-gray-900">${columns.length} ${window.templatesTranslations.columns}</p>
                    </div>
                </div>
                
                ${template.description ? `
                    <div>
                        <p class="text-sm text-gray-500">${window.templatesTranslations.viewDetailDescription}</p>
                        <p class="text-gray-700">${LogbookManagement.escapeHtml(template.description)}</p>
                    </div>
                ` : ''}
                
                <div>
                    <p class="text-sm text-gray-500 mb-3">${window.templatesTranslations.viewDetailColumns}</p>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">${window.templatesTranslations.viewDetailColumnName}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">${window.templatesTranslations.viewDetailColumnType}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">${window.templatesTranslations.viewDetailDescription}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${columnsHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('avt-view-content').innerHTML = content;
        const modal = document.getElementById('avt-view-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeViewModal() {
        const modal = document.getElementById('avt-view-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async toggleStatus(id) {
        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch(`/api/available-templates/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                LogbookManagement.showSuccess('Status template berhasil diubah');
                this.clearCache();
                await this.loadTemplates(true);
            } else {
                throw new Error(result.message || 'Failed to toggle status');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            LogbookManagement.showError('Gagal mengubah status. ' + error.message);
        }
    },

    showDeleteModal(id, name) {
        this.templateToDelete = id;
        document.getElementById('avt-delete-name').textContent = name;
        const modal = document.getElementById('avt-delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeDeleteModal() {
        this.templateToDelete = null;
        const modal = document.getElementById('avt-delete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async confirmDelete() {
        if (!this.templateToDelete) return;

        try {
            const token = localStorage.getItem('admin_token');
            const response = await fetch(`/api/available-templates/${this.templateToDelete}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                LogbookManagement.showSuccess('Template berhasil dihapus');
                this.closeDeleteModal();
                this.clearCache();
                await this.loadTemplates(true);
            } else {
                throw new Error(result.message || 'Failed to delete template');
            }
        } catch (error) {
            console.error('Error deleting template:', error);
            LogbookManagement.showError('Gagal menghapus template. ' + error.message);
        }
    },

    refresh() {
        this.clearCache();
        this.loadTemplates(true);
    }
};
</script>
