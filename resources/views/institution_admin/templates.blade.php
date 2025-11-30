@extends('institution_admin.layout')

@section('title', 'Template Logbook')
@section('page-title', 'Template Logbook')
@section('page-description', 'Kelola template logbook untuk institusi Anda')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Template Logbook</span>
    </div>
</li>
@endsection

@section('content')
<div id="pageLoading" class="flex flex-col items-center justify-center py-20">
    <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
    <p class="mt-4 text-gray-600">Memuat template...</p>
</div>

<div id="mainContent" class="hidden">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex gap-2">
            <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh
            </button>
        </div>
        
        <!-- Search -->
        <div class="relative w-full md:w-64">
            <input type="text" id="searchInput" placeholder="Cari template..." 
                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- Templates Grid -->
    <div id="templatesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Templates will be rendered here -->
    </div>
    
    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-12 bg-white rounded-xl shadow-sm">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Template</h3>
        <p class="text-gray-500">Template akan ditampilkan di sini setelah admin membuat template untuk institusi Anda</p>
    </div>
</div>

<!-- Template Detail Modal -->
<div id="templateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Detail Template</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalBody" class="p-6">
                <!-- Content will be rendered dynamically -->
            </div>
            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3 rounded-b-xl">
                <button onclick="closeModal()" 
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    Tutup
                </button>
                <button onclick="useTemplate()" 
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Gunakan Template
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let allTemplates = [];
let selectedTemplate = null;

function getToken() {
    return localStorage.getItem('admin_token');
}

document.addEventListener('DOMContentLoaded', async function() {
    const token = getToken();
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    await loadTemplates();
    document.getElementById('searchInput').addEventListener('input', filterTemplates);
});

async function loadTemplates() {
    try {
        document.getElementById('pageLoading').classList.remove('hidden');
        document.getElementById('mainContent').classList.add('hidden');
        
        const response = await fetch('/api/available-templates?is_active=1', {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            allTemplates = data.data || [];
            renderTemplates();
        }
        
        document.getElementById('pageLoading').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');
    } catch (error) {
        console.error('Failed to load templates:', error);
        showAlert('error', 'Error', 'Gagal memuat template');
    }
}

function filterTemplates() {
    renderTemplates();
}

function renderTemplates() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allTemplates.filter(t => 
        t.name.toLowerCase().includes(searchTerm) ||
        (t.description && t.description.toLowerCase().includes(searchTerm))
    );
    
    const grid = document.getElementById('templatesGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (filtered.length === 0) {
        grid.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    grid.innerHTML = filtered.map(template => `
        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600 text-xl"></i>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                        ${template.columns ? template.columns.length : 0} Kolom
                    </span>
                </div>
                
                <h3 class="font-semibold text-lg text-gray-800 mb-2">${escapeHtml(template.name)}</h3>
                <p class="text-gray-500 text-sm mb-4 line-clamp-2">${escapeHtml(template.description || 'Tidak ada deskripsi')}</p>
                
                <div class="flex items-center text-sm text-gray-400 mb-4">
                    <i class="fas fa-building mr-2"></i>
                    <span>${template.institution?.name || 'Global'}</span>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="viewTemplate('${template.id}')" 
                        class="flex-1 px-3 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors text-sm font-medium">
                        <i class="fas fa-eye mr-1"></i> Detail
                    </button>
                    <button onclick="useTemplateById('${template.id}')" 
                        class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i> Gunakan
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function viewTemplate(id) {
    selectedTemplate = allTemplates.find(t => t.id === id);
    if (!selectedTemplate) return;
    
    const columns = selectedTemplate.columns || [];
    
    document.getElementById('modalTitle').textContent = selectedTemplate.name;
    document.getElementById('modalBody').innerHTML = `
        <div class="space-y-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-1">Deskripsi</h4>
                <p class="text-gray-800">${escapeHtml(selectedTemplate.description || 'Tidak ada deskripsi')}</p>
            </div>
            
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-3">Kolom Template (${columns.length})</h4>
                <div class="space-y-2">
                    ${columns.length > 0 ? columns.map((col, index) => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-xs font-medium mr-3">${index + 1}</span>
                                <div>
                                    <p class="font-medium text-gray-800">${escapeHtml(col.name)}</p>
                                    <p class="text-xs text-gray-500">${col.description || '-'}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">${col.data_type}</span>
                        </div>
                    `).join('') : '<p class="text-gray-500 text-center py-4">Belum ada kolom</p>'}
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Institusi:</span>
                    <span class="font-medium ml-2">${selectedTemplate.institution?.name || 'Global'}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="ml-2 px-2 py-1 rounded-full text-xs ${selectedTemplate.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                        ${selectedTemplate.is_active ? 'Aktif' : 'Non-aktif'}
                    </span>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('templateModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('templateModal').classList.add('hidden');
    selectedTemplate = null;
}

function useTemplate() {
    if (selectedTemplate) {
        useTemplateById(selectedTemplate.id);
    }
}

function useTemplateById(id) {
    // Redirect to create logbook page with template pre-selected
    window.location.href = `/institution-admin/logbooks?create=true&template=${id}`;
}

async function refreshData() {
    await loadTemplates();
    showAlert('success', 'Berhasil', 'Data berhasil diperbarui');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
