@extends('admin.layout')

@section('title', 'Manajemen Logbook')
@section('page-title', 'Manajemen Logbook')
@section('page-description', 'Kelola semua template logbook yang dibuat oleh pengguna')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Template</p>
                    <p class="text-3xl font-bold text-gray-800" id="total-templates">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Entri</p>
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
                    <p class="text-sm text-gray-600 mb-1">Aktif Hari Ini</p>
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
                    <p class="text-sm text-gray-600 mb-1">Total Pembuat</p>
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
                    <i class="fas fa-search mr-1"></i> Cari Template
                </label>
                <input type="text" id="search-input" placeholder="Cari berdasarkan nama template atau pembuat..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <button onclick="refreshTemplates()" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Refresh
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
                            Template
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Pembuat
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Tanggal Dibuat
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Jumlah Entri
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="templates-tbody" class="bg-white divide-y divide-gray-200">
                    <!-- Templates will be loaded here -->
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">Memuat data template...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Konfirmasi Hapus</h3>
                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin menghapus template <strong id="delete-template-name"></strong>?
                    <br><span class="text-sm text-red-600 mt-2 block">Tindakan ini tidak dapat dibatalkan!</span>
                </p>
                
                <div class="flex gap-3">
                    <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition duration-200">
                        Batal
                    </button>
                    <button onclick="confirmDelete()" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let allTemplates = [];
let templateToDelete = null;
const CACHE_KEY = 'logbook_templates_cache';
const CACHE_DURATION = 10 * 60 * 1000; // 10 MENIT

// Check authentication when page loads
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('admin_token');
    const user = localStorage.getItem('admin_user');
    
    if (!token || !user) {
        console.error('No authentication found');
        window.location.href = '/admin/login';
        return;
    }

    // Load templates dengan cache
    loadTemplates();

    // Setup search
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            filterTemplates(e.target.value);
        });
    }
});

// CACHE FUNCTIONS - SIMPLE & WORKING
function isValidCache() {
    const cached = localStorage.getItem(CACHE_KEY);
    if (!cached) {
        console.log('❌ Cache tidak ada');
        return false;
    }
    
    try {
        const { timestamp } = JSON.parse(cached);
        const age = Date.now() - timestamp;
        const isValid = age < CACHE_DURATION;
        
        if (isValid) {
            console.log(`✅ CACHE VALID - Umur: ${Math.round(age/1000)}s dari ${CACHE_DURATION/1000}s`);
        } else {
            console.log(`❌ CACHE EXPIRED - Umur: ${Math.round(age/1000)}s (max: ${CACHE_DURATION/1000}s)`);
        }
        
        return isValid;
    } catch (e) {
        console.error('❌ Cache corrupt:', e);
        return false;
    }
}

function getCache() {
    if (!isValidCache()) return null;
    
    try {
        const cached = localStorage.getItem(CACHE_KEY);
        const { data } = JSON.parse(cached);
        console.log(`📦 Menggunakan data dari CACHE (${data.length} items)`);
        return data;
    } catch (e) {
        console.error('❌ Error reading cache:', e);
        return null;
    }
}

function setCache(data) {
    try {
        const cacheObject = {
            data: data,
            timestamp: Date.now()
        };
        localStorage.setItem(CACHE_KEY, JSON.stringify(cacheObject));
        console.log(`💾 Data disimpan ke CACHE (${data.length} items, expired dalam ${CACHE_DURATION/1000/60} menit)`);
    } catch (e) {
        console.error('❌ Error saving cache:', e);
    }
}

function clearCacheData() {
    localStorage.removeItem(CACHE_KEY);
    console.log('🗑️ Cache dihapus');
}

async function loadTemplates(forceRefresh = false) {
    try {
        // CEK CACHE DULU - JANGAN PANGGIL API KALAU CACHE MASIH VALID
        if (!forceRefresh) {
            const cachedData = getCache();
            if (cachedData !== null) {
                // GUNAKAN CACHE - TIDAK ADA API CALL!
                allTemplates = cachedData;
                updateStats();
                renderTemplatesTable(allTemplates);
                return; // STOP DI SINI - TIDAK LANJUT KE API!
            }
        }
        
        // CACHE TIDAK ADA ATAU EXPIRED - PANGGIL API
        console.log('🌐 Memanggil API karena cache ' + (forceRefresh ? 'di-refresh paksa' : 'tidak ada/expired'));
        
        const token = localStorage.getItem('admin_token');
        const response = await fetch('/api/templates/admin/all', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success && result.data) {
            allTemplates = result.data;
            
            // SIMPAN KE CACHE - BERLAKU 10 MENIT
            setCache(allTemplates);
            
            updateStats();
            renderTemplatesTable(allTemplates);
            
            if (forceRefresh) {
                showSuccess('Data diperbarui dari server');
            }
        } else {
            throw new Error(result.message || 'Failed to load templates');
        }
    } catch (error) {
        console.error('Error loading templates:', error);
        showError('Gagal memuat data template');
        renderEmptyState();
    }
}

function updateStats() {
    // Total templates
    document.getElementById('total-templates').textContent = allTemplates.length;
    
    // Total entries
    const totalEntries = allTemplates.reduce((sum, t) => sum + parseInt(t.entries_count || 0), 0);
    document.getElementById('total-entries').textContent = totalEntries;
    
    // Active today (templates created today)
    const today = new Date().toDateString();
    const activeToday = allTemplates.filter(t => {
        const createdDate = new Date(t.created_at).toDateString();
        return createdDate === today;
    }).length;
    document.getElementById('active-today').textContent = activeToday;
    
    // Unique creators
    const uniqueCreators = new Set(allTemplates.map(t => t.creator_email));
    document.getElementById('total-creators').textContent = uniqueCreators.size;
}

function renderTemplatesTable(templates) {
    const tbody = document.getElementById('templates-tbody');
    
    if (!templates || templates.length === 0) {
        renderEmptyState();
        return;
    }

    tbody.innerHTML = templates.map(template => `
        <tr class="hover:bg-gray-50 transition duration-150">
            <td class="px-6 py-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900">${escapeHtml(template.name)}</div>
                        ${template.description ? `<div class="text-sm text-gray-500 mt-1">${escapeHtml(template.description)}</div>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                        ${getInitials(template.creator_name)}
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">${escapeHtml(template.creator_name)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(template.creator_email)}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">${formatDate(template.created_at)}</div>
                <div class="text-xs text-gray-500">${formatTime(template.created_at)}</div>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                    template.entries_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                }">
                    <i class="fas fa-list mr-1"></i> ${template.entries_count || 0} entri
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <button onclick="viewTemplate('${template.id}')" 
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition duration-200"
                        title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="showDeleteModal('${template.id}', '${escapeHtml(template.name)}')" 
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition duration-200"
                        title="Hapus Template">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderEmptyState() {
    const tbody = document.getElementById('templates-tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="5" class="px-6 py-12 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg mb-2">Tidak ada template ditemukan</p>
                <p class="text-gray-400 text-sm">Template yang dibuat akan muncul di sini</p>
            </td>
        </tr>
    `;
}

function filterTemplates(searchTerm) {
    if (!searchTerm.trim()) {
        renderTemplatesTable(allTemplates);
        return;
    }

    const filtered = allTemplates.filter(template => {
        const search = searchTerm.toLowerCase();
        return template.name.toLowerCase().includes(search) ||
               template.creator_name.toLowerCase().includes(search) ||
               template.creator_email.toLowerCase().includes(search) ||
               (template.description && template.description.toLowerCase().includes(search));
    });

    renderTemplatesTable(filtered);
}

function showDeleteModal(templateId, templateName) {
    templateToDelete = templateId;
    const modal = document.getElementById('delete-modal');
    document.getElementById('delete-template-name').textContent = templateName;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteModal() {
    templateToDelete = null;
    const modal = document.getElementById('delete-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function confirmDelete() {
    if (!templateToDelete) return;

    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`/api/templates/${templateToDelete}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success) {
            showSuccess('Template berhasil dihapus');
            closeDeleteModal();
            clearCacheData();
            await loadTemplates(true);
        } else {
            throw new Error(result.message || 'Failed to delete template');
        }
    } catch (error) {
        console.error('Error deleting template:', error);
        showError('Gagal menghapus template. ' + error.message);
    }
}

function viewTemplate(templateId) {
    // Navigate to logbook detail page
    window.location.href = `/admin/logbook/${templateId}`;
}

async function refreshTemplates() {
    clearCacheData();
    await loadTemplates(true);
}

// Helper functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

function getInitials(name) {
    if (!name) return '?';
    return name.split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

function formatTime(dateString) {
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleTimeString('id-ID', options);
}

function showSuccess(message) {
    // Simple alert for now, can be replaced with toast notification
    alert('✓ ' + message);
}

function showError(message) {
    alert('✗ ' + message);
}

function showInfo(message) {
    console.log('ℹ ' + message);
}
</script>
@endpush