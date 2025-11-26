@extends('admin.layout')

@section('title', 'Institution Management')
@section('page-title', 'Manajemen Institution')
@section('page-description', 'Kelola data institution dan lihat template yang terafiliasi')

@section('content')
<!-- Loading Indicator -->
<div id="pageLoading" class="text-center py-12">
    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
    <p class="text-gray-600 mt-4">Loading institution data...</p>
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
                           placeholder="Search institutions by name..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Refresh Button -->
            <button onclick="refreshInstitutionData()" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors"
                    title="Refresh data from server">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
            
            <!-- Add Institution Button -->
            <button onclick="openCreateModal()" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Add Institution</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Institutions</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalInstitutions">0</p>
                </div>
                <i class="fas fa-building text-indigo-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Templates</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalTemplates">0</p>
                </div>
                <i class="fas fa-file-alt text-green-500 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-800" id="totalUsers">0</p>
                </div>
                <i class="fas fa-users text-blue-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Institutions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Templates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="institutionsTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Table rows will be inserted here by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalCount">0</span> institutions
                </div>
                <div class="flex gap-2" id="paginationButtons">
                    <!-- Pagination buttons will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Institution Modal -->
<div id="institutionModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Add New Institution</h3>
                <button onclick="closeInstitutionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        
        <form id="institutionForm" class="p-6">
            <input type="hidden" id="institutionId" name="institution_id">
            <input type="hidden" id="formMode" value="create">
            
            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Institution Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="institutionName" 
                           name="name"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Enter institution name">
                </div>
                
                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        id="institutionDescription" 
                        name="description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter institution description (optional)"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters</p>
                </div>
            </div>
            
            <!-- Error Messages -->
            <div id="formError" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600"></p>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" 
                        onclick="closeInstitutionModal()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        id="submitBtn"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span id="submitBtnText">Create Institution</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Institution Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Konfirmasi Hapus Institution</h3>
            <p class="text-gray-600 text-center mb-6">
                Apakah Anda yakin ingin menghapus institution <strong id="deleteInstitutionName"></strong>?
                <br><span class="text-sm text-red-600 mt-2 block">Institution yang masih memiliki user atau template tidak dapat dihapus!</span>
            </p>
            
            <input type="hidden" id="deleteInstitutionId">
            
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
                        onclick="executeDeleteInstitution()"
                        id="deleteBtn"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                    <span id="deleteBtnText">Hapus</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Templates Modal -->
<div id="templatesModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4 hidden">
    <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800" id="templatesModalTitle">Templates</h3>
                    <p class="text-sm text-gray-600 mt-1" id="templatesModalSubtitle"></p>
                </div>
                <button onclick="closeTemplatesModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Loading indicator -->
            <div id="templatesLoading" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
                <p class="text-gray-600 mt-2">Loading templates...</p>
            </div>
            
            <!-- No templates message -->
            <div id="noTemplates" class="hidden text-center py-8">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No templates found for this institution</p>
            </div>
            
            <!-- Templates Grid -->
            <div id="templatesGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Template cards will be inserted here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allInstitutions = [];
let filteredInstitutions = [];
let currentPage = 1;
let perPage = 10;
const INSTITUTIONS_CACHE_KEY = 'institutions_management_cache';
const CACHE_DURATION = 10 * 60 * 1000; // 10 minutes

// CACHE FUNCTIONS
function isValidCache(cacheKey) {
    const cached = localStorage.getItem(cacheKey);
    if (!cached) return false;
    
    try {
        const { timestamp } = JSON.parse(cached);
        const age = Date.now() - timestamp;
        return age < CACHE_DURATION;
    } catch (e) {
        return false;
    }
}

function getCache(cacheKey) {
    if (!isValidCache(cacheKey)) return null;
    
    try {
        const cached = localStorage.getItem(cacheKey);
        const { data } = JSON.parse(cached);
        console.log(`📦 Using data from CACHE ${cacheKey}`);
        return data;
    } catch (e) {
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
        console.log(`💾 Data saved to CACHE ${cacheKey}`);
    } catch (e) {
        console.error(`❌ Error saving cache ${cacheKey}:`, e);
    }
}

function clearCacheData(cacheKey) {
    if (cacheKey) {
        localStorage.removeItem(cacheKey);
        console.log(`🗑️ Cache ${cacheKey} cleared`);
    } else {
        localStorage.removeItem(INSTITUTIONS_CACHE_KEY);
        console.log('🗑️ All cache cleared');
    }
}

// Initialize page
async function initInstitutionManagement() {
    const token = localStorage.getItem('admin_token');
    
    if (!token) {
        console.error('No authentication token found');
        window.location.href = '/admin/login';
        return;
    }
    
    try {
        const pageLoading = document.getElementById('pageLoading');
        const mainContent = document.getElementById('mainContent');
        
        if (pageLoading && mainContent) {
            await loadInstitutions();
            
            pageLoading.classList.add('hidden');
            mainContent.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Initialization failed:', error);
        alert('Failed to load institution management data. Please try again.');
    }
}

// Load institutions from API
async function loadInstitutions(forceRefresh = false) {
    const token = localStorage.getItem('admin_token');
    
    try {
        // Check cache first
        if (!forceRefresh) {
            const cachedData = getCache(INSTITUTIONS_CACHE_KEY);
            if (cachedData !== null) {
                allInstitutions = cachedData;
                filteredInstitutions = [...allInstitutions];
                updateStats();
                renderInstitutionsTable();
                return;
            }
        }
        
        // Call API
        console.log('🌐 Calling API institutions/details...');
        const response = await fetch('/api/institutions/details', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to load institutions');
        
        const data = await response.json();
        allInstitutions = data.data || [];
        filteredInstitutions = [...allInstitutions];
        
        // Save to cache
        setCache(INSTITUTIONS_CACHE_KEY, allInstitutions);
        
        updateStats();
        renderInstitutionsTable();
        
    } catch (error) {
        console.error('Load institutions error:', error);
        throw error;
    }
}

// Update stats cards
function updateStats() {
    const totalInst = allInstitutions.length;
    let totalTemplates = 0;
    let totalUsers = 0;
    
    // Calculate total templates and users from all institutions
    allInstitutions.forEach(inst => {
        totalTemplates += inst.templates_count || 0;
        totalUsers += inst.users_count || 0;
    });
    
    document.getElementById('totalInstitutions').textContent = totalInst;
    document.getElementById('totalTemplates').textContent = totalTemplates;
    document.getElementById('totalUsers').textContent = totalUsers;
}

// Render institutions table
function renderInstitutionsTable() {
    const tbody = document.getElementById('institutionsTableBody');
    const startIndex = (currentPage - 1) * perPage;
    const endIndex = startIndex + perPage;
    const pageInstitutions = filteredInstitutions.slice(startIndex, endIndex);
    
    if (pageInstitutions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-building text-4xl mb-2"></i>
                    <p>No institutions found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = pageInstitutions.map(inst => {
        const createdDate = new Date(inst.created_at).toLocaleDateString('id-ID');
        const description = inst.description ? 
            (inst.description.length > 100 ? inst.description.substring(0, 100) + '...' : inst.description) : 
            '-';
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-building text-indigo-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">${inst.name}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-sm text-gray-600">${description}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        ${inst.templates_count || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${inst.users_count || 0}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${createdDate}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="viewTemplates('${inst.id}', '${inst.name}')" 
                            class="text-green-600 hover:text-green-900 mr-3" 
                            title="View Templates">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="openEditModal('${inst.id}')" 
                            class="text-indigo-600 hover:text-indigo-900 mr-3" 
                            title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="confirmDeleteInstitution('${inst.id}', '${inst.name}')" 
                            class="text-red-600 hover:text-red-900" 
                            title="Delete">
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
    const totalPages = Math.ceil(filteredInstitutions.length / perPage);
    const startIndex = (currentPage - 1) * perPage + 1;
    const endIndex = Math.min(currentPage * perPage, filteredInstitutions.length);
    
    document.getElementById('showingFrom').textContent = filteredInstitutions.length > 0 ? startIndex : 0;
    document.getElementById('showingTo').textContent = endIndex;
    document.getElementById('totalCount').textContent = filteredInstitutions.length;
    
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
    const totalPages = Math.ceil(filteredInstitutions.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderInstitutionsTable();
}

// Search institutions
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    filteredInstitutions = allInstitutions.filter(inst => {
        return inst.name.toLowerCase().includes(searchTerm) || 
               (inst.description && inst.description.toLowerCase().includes(searchTerm));
    });
    
    currentPage = 1;
    renderInstitutionsTable();
});

// Open create modal
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add New Institution';
    document.getElementById('formMode').value = 'create';
    document.getElementById('institutionId').value = '';
    document.getElementById('institutionForm').reset();
    document.getElementById('submitBtnText').textContent = 'Create Institution';
    document.getElementById('formError').classList.add('hidden');
    
    const modal = document.getElementById('institutionModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Open edit modal
function openEditModal(institutionId) {
    const institution = allInstitutions.find(inst => inst.id === institutionId);
    if (!institution) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Institution';
    document.getElementById('formMode').value = 'edit';
    document.getElementById('institutionId').value = institution.id;
    document.getElementById('institutionName').value = institution.name;
    document.getElementById('institutionDescription').value = institution.description || '';
    document.getElementById('submitBtnText').textContent = 'Update Institution';
    document.getElementById('formError').classList.add('hidden');
    
    const modal = document.getElementById('institutionModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close institution modal
function closeInstitutionModal() {
    const modal = document.getElementById('institutionModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('institutionForm').reset();
}

// Submit institution form
document.getElementById('institutionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const mode = document.getElementById('formMode').value;
    const institutionId = document.getElementById('institutionId').value;
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = document.getElementById('submitBtnText').textContent;
    
    // Disable button
    submitBtn.disabled = true;
    document.getElementById('submitBtnText').textContent = 'Processing...';
    
    const formData = {
        name: document.getElementById('institutionName').value,
        description: document.getElementById('institutionDescription').value
    };
    
    try {
        const token = localStorage.getItem('admin_token');
        const url = mode === 'create' ? '/api/institutions' : `/api/institutions/${institutionId}`;
        const method = mode === 'create' ? 'POST' : 'PUT';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || `Failed to ${mode} institution`);
        }
        
        // Show success message
        showToast(`Institution ${mode === 'create' ? 'created' : 'updated'} successfully!`, 'success');
        closeInstitutionModal();
        
        // Clear cache and reload
        clearCacheData(INSTITUTIONS_CACHE_KEY);
        await loadInstitutions(true);
        
    } catch (error) {
        console.error(`${mode} institution error:`, error);
        const errorDiv = document.getElementById('formError');
        errorDiv.querySelector('p').textContent = error.message;
        errorDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        document.getElementById('submitBtnText').textContent = originalBtnText;
    }
});

// Confirm delete institution
function confirmDeleteInstitution(institutionId, institutionName) {
    document.getElementById('deleteInstitutionId').value = institutionId;
    document.getElementById('deleteInstitutionName').textContent = institutionName;
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

// Execute delete institution
async function executeDeleteInstitution() {
    const institutionId = document.getElementById('deleteInstitutionId').value;
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteBtnText = document.getElementById('deleteBtnText');
    const originalText = deleteBtnText.textContent;
    
    try {
        // Disable button and show loading
        deleteBtn.disabled = true;
        deleteBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menghapus...';
        
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`/api/institutions/${institutionId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to delete institution');
        }
        
        showToast('Institution berhasil dihapus!', 'success');
        closeDeleteModal();
        
        // Clear cache and reload
        clearCacheData(INSTITUTIONS_CACHE_KEY);
        await loadInstitutions(true);
        
    } catch (error) {
        console.error('Delete institution error:', error);
        const errorDiv = document.getElementById('deleteFormError');
        errorDiv.querySelector('p').textContent = error.message;
        errorDiv.classList.remove('hidden');
    } finally {
        // Re-enable button
        deleteBtn.disabled = false;
        deleteBtnText.textContent = originalText;
    }
}

// View templates for institution
async function viewTemplates(institutionId, institutionName) {
    const modal = document.getElementById('templatesModal');
    const loading = document.getElementById('templatesLoading');
    const noTemplates = document.getElementById('noTemplates');
    const grid = document.getElementById('templatesGrid');
    
    // Set modal title
    document.getElementById('templatesModalTitle').textContent = `Templates - ${institutionName}`;
    document.getElementById('templatesModalSubtitle').textContent = 'Semua template yang terafiliasi dengan institution ini';
    
    // Show modal and loading
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    noTemplates.classList.add('hidden');
    grid.classList.add('hidden');
    
    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`/api/institutions/${institutionId}/templates`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to load templates');
        
        const data = await response.json();
        const templates = data.data.templates || [];
        
        loading.classList.add('hidden');
        
        if (templates.length === 0) {
            noTemplates.classList.remove('hidden');
        } else {
            grid.classList.remove('hidden');
            renderTemplatesGrid(templates);
        }
        
    } catch (error) {
        console.error('Load templates error:', error);
        loading.classList.add('hidden');
        showToast('Failed to load templates', 'error');
    }
}

// Render templates grid
function renderTemplatesGrid(templates) {
    const grid = document.getElementById('templatesGrid');
    
    grid.innerHTML = templates.map(template => {
        const ownerName = template.owner ? template.owner.name : 'Unknown';
        const createdDate = new Date(template.created_at).toLocaleDateString('id-ID');
        const description = template.description ? 
            (template.description.length > 100 ? template.description.substring(0, 100) + '...' : template.description) : 
            'No description';
        
        return `
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-semibold text-gray-900">${template.name}</h4>
                            <p class="text-xs text-gray-500">by ${ownerName}</p>
                        </div>
                    </div>
                </div>
                
                <p class="text-sm text-gray-600 mb-3">${description}</p>
                
                <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                    <div class="flex items-center gap-3">
                        <span title="Total Entries">
                            <i class="fas fa-database text-blue-500"></i> ${template.entries_count || 0}
                        </span>
                        <span title="Total Users">
                            <i class="fas fa-users text-green-500"></i> ${template.users_count || 0}
                        </span>
                    </div>
                </div>
                
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400">Created: ${createdDate}</p>
                </div>
            </div>
        `;
    }).join('');
}

// Close templates modal
function closeTemplatesModal() {
    const modal = document.getElementById('templatesModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-20 right-6 px-6 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Refresh institution data
async function refreshInstitutionData() {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    
    icon.classList.add('fa-spin');
    btn.disabled = true;
    
    try {
        clearCacheData();
        await loadInstitutions(true);
        showToast('Data berhasil diperbarui', 'success');
    } catch (error) {
        showToast('Gagal memperbarui data', 'error');
    } finally {
        icon.classList.remove('fa-spin');
        btn.disabled = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initInstitutionManagement);
</script>
@endpush
