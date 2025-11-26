@extends('admin.layout')

@section('title', 'Detail Logbook')
@section('page-title', 'Detail Logbook')
@section('page-description', 'Lihat detail dan semua data yang ada dalam logbook')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <button onclick="history.back()" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </button>
    </div>

    <!-- Logbook Header -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-8" id="logbook-header">
        <!-- Loading state -->
        <div id="header-loading" class="animate-pulse">
            <div class="h-8 bg-gray-200 rounded w-1/3 mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="h-20 bg-gray-200 rounded"></div>
                <div class="h-20 bg-gray-200 rounded"></div>
                <div class="h-20 bg-gray-200 rounded"></div>
            </div>
        </div>

        <!-- Actual content (hidden initially) -->
        <div id="header-content" class="hidden">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 id="template-name" class="text-3xl font-bold text-gray-900 mb-2"></h1>
                    <p id="template-description" class="text-gray-600 mb-4"></p>
                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-2"></i>
                            <span>Dibuat oleh: <span id="template-creator" class="font-medium"></span></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar mr-2"></i>
                            <span>Tanggal: <span id="template-date" class="font-medium"></span></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="refreshData()" class="px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm mb-1">Total Entri</p>
                            <p id="total-entries" class="text-3xl font-bold">0</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-400 bg-opacity-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-list text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm mb-1">Total Penulis</p>
                            <p id="total-writers" class="text-3xl font-bold">0</p>
                        </div>
                        <div class="w-12 h-12 bg-green-400 bg-opacity-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm mb-1">Entri Terverifikasi</p>
                            <p id="verified-entries" class="text-3xl font-bold">0</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-400 bg-opacity-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm mb-1">Entri Terbaru</p>
                            <p id="latest-entry" class="text-sm font-medium">-</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-400 bg-opacity-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Writer Filter -->
                <div class="min-w-0 flex-1 sm:min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Penulis</label>
                    <select id="writer-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Penulis</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div class="flex gap-2">
                    <div class="min-w-0 flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                        <input type="date" id="start-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="min-w-0 flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                        <input type="date" id="end-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Verification Filter -->
                <div class="min-w-0 flex-1 sm:min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Verifikasi</label>
                    <select id="verification-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="verified">Terverifikasi</option>
                        <option value="unverified">Belum Verifikasi</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button onclick="applyFilters()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-200">
                    <i class="fas fa-filter mr-2"></i>
                    Terapkan Filter
                </button>
                <button onclick="clearFilters()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Bersihkan
                </button>
            </div>
        </div>
    </div>

    <!-- Data Entries -->
    <div class="bg-white rounded-xl shadow-sm border">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Data Logbook</h2>
                <p class="text-sm text-gray-600">Semua data yang telah dimasukkan ke dalam logbook</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Sort Options -->
                <select id="sort-option" onchange="applySorting()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="created_at-desc">Terbaru</option>
                    <option value="created_at-asc">Terlama</option>
                    <option value="updated_at-desc">Update Terbaru</option>
                </select>
                
                <!-- View Mode Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button onclick="toggleViewMode('card')" id="card-view-btn" class="px-3 py-2 text-sm rounded-md transition duration-200 bg-white text-gray-700 shadow-sm">
                        <i class="fas fa-th-large mr-1"></i>
                        Kartu
                    </button>
                    <button onclick="toggleViewMode('table')" id="table-view-btn" class="px-3 py-2 text-sm rounded-md transition duration-200 text-gray-500">
                        <i class="fas fa-table mr-1"></i>
                        Tabel
                    </button>
                </div>
            </div>
        </div>

        <!-- Data Container -->
        <div id="data-container" class="p-6">
            <!-- Loading state -->
            <div id="data-loading" class="space-y-4">
                <div class="animate-pulse">
                    <div class="grid gap-6" id="loading-grid">
                        <!-- Loading cards will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- No data state -->
            <div id="no-data" class="hidden text-center py-12">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Belum Ada Data</h3>
                <p class="text-gray-600">Logbook ini belum memiliki data yang dimasukkan.</p>
            </div>

            <!-- Card View -->
            <div id="card-view" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Cards will be inserted here -->
            </div>

            <!-- Table View -->
            <div id="table-view" class="hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead id="table-header" class="bg-gray-50">
                        <!-- Headers will be inserted here -->
                    </thead>
                    <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination-container" class="px-6 py-4 border-t bg-gray-50 hidden">
            <div class="flex items-center justify-between">
                <div id="pagination-info" class="text-sm text-gray-700">
                    <!-- Pagination info will be inserted here -->
                </div>
                <div id="pagination-buttons" class="flex space-x-2">
                    <!-- Pagination buttons will be inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Entry Detail Modal -->
    <div id="entry-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Detail Entri Logbook</h3>
                    <button onclick="closeEntryModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div id="modal-content" class="p-6">
                <!-- Entry details will be inserted here -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentTemplateId = null;
let currentEntries = [];
let currentWriters = [];
let currentPage = 1;
let viewMode = 'card'; // 'card' or 'table'

// Get template ID from URL
function getTemplateIdFromUrl() {
    const pathParts = window.location.pathname.split('/');
    return pathParts[pathParts.length - 1];
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    currentTemplateId = getTemplateIdFromUrl();
    if (currentTemplateId) {
        loadTemplateData();
        loadTemplateEntries();
    } else {
        showError('Template ID tidak ditemukan');
    }
});

// Load template header data
async function loadTemplateData() {
    try {
        // Get template basic info
        const templateResponse = await fetchWithAuth(`/api/templates/${currentTemplateId}`);
        if (!templateResponse.ok) {
            const errorData = await templateResponse.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to fetch template data');
        }
        const templateData = await templateResponse.json();

        // Get template summary with statistics
        const summaryResponse = await fetchWithAuth(`/api/logbook-entries/template/${currentTemplateId}/summary`);
        if (!summaryResponse.ok) {
            const errorData = await summaryResponse.json().catch(() => ({}));
            console.error('Summary API Error:', errorData);
            throw new Error(errorData.message || 'Failed to fetch template summary');
        }
        const summaryData = await summaryResponse.json();

        displayTemplateHeader(templateData.data, summaryData);
        
        // Hide loading and show content
        document.getElementById('header-loading').classList.add('hidden');
        document.getElementById('header-content').classList.remove('hidden');

    } catch (error) {
        console.error('Error loading template data:', error);
        showError('Gagal memuat data template: ' + error.message);
        
        // Hide loading
        document.getElementById('header-loading').classList.add('hidden');
    }
}

// Display template header information
function displayTemplateHeader(template, summary) {
    document.getElementById('template-name').textContent = template.name || 'Untitled Template';
    document.getElementById('template-description').textContent = template.description || 'No description available';
    
    // Format dates
    const createdDate = template.created_at ? new Date(template.created_at).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long', 
        day: 'numeric'
    }) : 'Unknown';
    
    document.getElementById('template-creator').textContent = 'Administrator'; // Default since we don't have creator info
    document.getElementById('template-date').textContent = createdDate;

    // Update statistics
    const stats = summary.statistics || {};
    document.getElementById('total-entries').textContent = stats.total_entries || 0;
    document.getElementById('total-writers').textContent = stats.total_writers || 0;
    
    // We don't have verification stats in the current API, so we'll set them to 0 for now
    document.getElementById('verified-entries').textContent = '0';
    
    // Format latest entry date
    const latestEntry = stats.latest_entry ? new Date(stats.latest_entry).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    }) : 'Belum ada';
    
    document.getElementById('latest-entry').textContent = latestEntry;
}

// Load template entries
async function loadTemplateEntries(page = 1) {
    try {
        showDataLoading();

        // Build query parameters
        const params = new URLSearchParams({
            per_page: 15,
            page: page
        });

        // Add filters
        const writerFilter = document.getElementById('writer-filter')?.value;
        const startDate = document.getElementById('start-date')?.value;
        const endDate = document.getElementById('end-date')?.value;
        const sortOption = document.getElementById('sort-option')?.value || 'created_at-desc';

        if (writerFilter) params.append('writer_id', writerFilter);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        // Add sorting
        const [sortBy, sortOrder] = sortOption.split('-');
        params.append('sort_by', sortBy);
        params.append('sort_order', sortOrder);

        const response = await fetchWithAuth(`/api/logbook-entries/template/${currentTemplateId}?${params.toString()}`);
        if (!response.ok) {
            throw new Error('Failed to fetch entries');
        }

        const data = await response.json();
        currentEntries = data.entries || [];
        
        // Update writers list for filter
        updateWritersFilter(currentEntries);

        // Display entries
        displayEntries(currentEntries);
        
        // Update pagination
        updatePagination(data.pagination);

        hideDataLoading();

    } catch (error) {
        console.error('Error loading entries:', error);
        showError('Gagal memuat data entri: ' + error.message);
        hideDataLoading();
    }
}

// Update writers filter dropdown
function updateWritersFilter(entries) {
    const writerFilter = document.getElementById('writer-filter');
    if (!writerFilter) return;

    const currentValue = writerFilter.value;
    
    // Get unique writers
    const writers = [...new Map(entries.map(entry => [
        entry.writer?.id, 
        {id: entry.writer?.id, name: entry.writer?.name}
    ])).values()].filter(writer => writer.id);

    // Update options
    writerFilter.innerHTML = '<option value="">Semua Penulis</option>';
    writers.forEach(writer => {
        const option = document.createElement('option');
        option.value = writer.id;
        option.textContent = writer.name || 'Unknown User';
        if (writer.id === currentValue) {
            option.selected = true;
        }
        writerFilter.appendChild(option);
    });
}

// Display entries based on view mode
function displayEntries(entries) {
    if (entries.length === 0) {
        document.getElementById('card-view').classList.add('hidden');
        document.getElementById('table-view').classList.add('hidden');
        document.getElementById('no-data').classList.remove('hidden');
        return;
    }

    document.getElementById('no-data').classList.add('hidden');

    if (viewMode === 'card') {
        displayCardView(entries);
    } else {
        displayTableView(entries);
    }
}

// Display entries in card view
function displayCardView(entries) {
    const cardView = document.getElementById('card-view');
    const tableView = document.getElementById('table-view');
    
    tableView.classList.add('hidden');
    cardView.classList.remove('hidden');

    cardView.innerHTML = entries.map(entry => createEntryCard(entry)).join('');
}

// Display entries in table view
function displayTableView(entries) {
    const cardView = document.getElementById('card-view');
    const tableView = document.getElementById('table-view');
    const tableHeader = document.getElementById('table-header');
    const tableBody = document.getElementById('table-body');
    
    cardView.classList.add('hidden');
    tableView.classList.remove('hidden');

    // Create table headers (assuming all entries have same structure)
    if (entries.length > 0 && entries[0].data) {
        const headers = ['Penulis', 'Tanggal', ...Object.keys(entries[0].data), 'Status', 'Aksi'];
        tableHeader.innerHTML = `
            <tr>
                ${headers.map(header => `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${header}</th>`).join('')}
            </tr>
        `;

        // Create table rows
        tableBody.innerHTML = entries.map(entry => createEntryRow(entry)).join('');
    }
}

// Create entry card HTML
function createEntryCard(entry) {
    const createdDate = new Date(entry.created_at).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const writerName = entry.writer?.name || 'Unknown User';
    
    // Get first few data fields to show as preview
    const dataPreview = entry.data ? Object.entries(entry.data).slice(0, 3) : [];
    
    const isVerified = entry.is_verified || false;
    const statusBadge = isVerified 
        ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Terverifikasi</span>'
        : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-clock mr-1"></i>Pending</span>';

    return `
        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-200">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-medium text-gray-900">${writerName}</h3>
                    <p class="text-sm text-gray-500">${createdDate}</p>
                </div>
                ${statusBadge}
            </div>

            <div class="space-y-2 mb-4">
                ${dataPreview.map(([key, value]) => `
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">${key}:</span>
                        <span class="text-gray-900 max-w-[150px] truncate">${formatDataValue(value)}</span>
                    </div>
                `).join('')}
                ${Object.keys(entry.data || {}).length > 3 ? `
                    <div class="text-sm text-gray-500 italic">
                        +${Object.keys(entry.data || {}).length - 3} field lainnya
                    </div>
                ` : ''}
            </div>

            <button onclick="showEntryDetail('${entry.id}')" 
                class="w-full px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition duration-200 text-sm font-medium">
                <i class="fas fa-eye mr-2"></i>
                Lihat Detail
            </button>
        </div>
    `;
}

// Create entry table row HTML
function createEntryRow(entry) {
    const createdDate = new Date(entry.created_at).toLocaleDateString('id-ID');
    const writerName = entry.writer?.name || 'Unknown User';
    
    const dataFields = entry.data ? Object.values(entry.data) : [];
    const isVerified = entry.is_verified || false;
    
    const statusBadge = isVerified 
        ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>Terverifikasi</span>'
        : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pending</span>';

    return `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${writerName}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${createdDate}</td>
            ${dataFields.map(value => `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-[200px] truncate">${formatDataValue(value)}</td>`).join('')}
            <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="showEntryDetail('${entry.id}')" 
                    class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-eye mr-1"></i>Detail
                </button>
            </td>
        </tr>
    `;
}

// Format data value for display
function formatDataValue(value) {
    if (value === null || value === undefined) return '-';
    
    // Handle images
    if (typeof value === 'string' && (value.includes('http') && (value.includes('.jpg') || value.includes('.png') || value.includes('.jpeg')))) {
        return '<i class="fas fa-image text-gray-400"></i> Gambar';
    }
    
    // Handle long text
    if (typeof value === 'string' && value.length > 50) {
        return value.substring(0, 50) + '...';
    }
    
    return String(value);
}

// Show entry detail modal
async function showEntryDetail(entryId) {
    try {
        const response = await fetchWithAuth(`/api/logbook-entries/${entryId}`);
        if (!response.ok) {
            throw new Error('Failed to fetch entry details');
        }

        const data = await response.json();
        const entry = data.data;

        displayEntryModal(entry);
        document.getElementById('entry-modal').classList.remove('hidden');
        document.getElementById('entry-modal').classList.add('flex');

    } catch (error) {
        console.error('Error loading entry details:', error);
        showError('Gagal memuat detail entri: ' + error.message);
    }
}

// Display entry in modal
function displayEntryModal(entry) {
    const modalContent = document.getElementById('modal-content');
    
    const createdDate = new Date(entry.created_at).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const updatedDate = entry.updated_at !== entry.created_at 
        ? new Date(entry.updated_at).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
        : null;

    const writerName = entry.writer?.name || 'Unknown User';
    const writerEmail = entry.writer?.email || '';

    const isVerified = entry.is_verified || false;
    const verificationSection = isVerified ? `
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                <span class="font-medium text-green-800">Status: Terverifikasi</span>
            </div>
            <div class="text-sm text-green-700">
                <p>Diverifikasi pada: ${entry.verified_at ? new Date(entry.verified_at).toLocaleDateString('id-ID') : '-'}</p>
                ${entry.verification_notes ? `<p class="mt-1">Catatan: ${entry.verification_notes}</p>` : ''}
            </div>
        </div>
    ` : `
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-clock text-gray-600 mr-2"></i>
                <span class="font-medium text-gray-800">Status: Menunggu Verifikasi</span>
            </div>
        </div>
    `;

    modalContent.innerHTML = `
        <!-- Entry Info -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Informasi Penulis</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-user w-5 text-gray-400 mr-3"></i>
                            <span class="text-gray-700">${writerName}</span>
                        </div>
                        ${writerEmail ? `
                            <div class="flex items-center">
                                <i class="fas fa-envelope w-5 text-gray-400 mr-3"></i>
                                <span class="text-gray-700">${writerEmail}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Waktu</h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-plus w-5 text-gray-400 mr-3"></i>
                            <span class="text-gray-700">Dibuat: ${createdDate}</span>
                        </div>
                        ${updatedDate ? `
                            <div class="flex items-center">
                                <i class="fas fa-edit w-5 text-gray-400 mr-3"></i>
                                <span class="text-gray-700">Diupdate: ${updatedDate}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Status -->
        ${verificationSection}

        <!-- Entry Data -->
        <div class="mt-6">
            <h4 class="font-medium text-gray-900 mb-4">Data Entri</h4>
            <div class="space-y-4">
                ${Object.entries(entry.data || {}).map(([key, value]) => `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h5 class="font-medium text-gray-700 mb-2">${key}</h5>
                        <div class="text-gray-900">
                            ${formatDetailDataValue(value)}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

// Format data value for detail view
function formatDetailDataValue(value) {
    if (value === null || value === undefined) return '<span class="text-gray-400 italic">Tidak ada data</span>';
    
    // Handle images
    if (typeof value === 'string' && (value.includes('http') && (value.includes('.jpg') || value.includes('.png') || value.includes('.jpeg')))) {
        return `<img src="${value}" alt="Gambar" class="max-w-full max-h-64 rounded-lg shadow-sm object-contain">`;
    }
    
    // Handle URLs
    if (typeof value === 'string' && value.startsWith('http')) {
        return `<a href="${value}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">${value}</a>`;
    }
    
    // Handle long text with proper formatting
    if (typeof value === 'string' && value.length > 100) {
        return `<div class="whitespace-pre-wrap">${value}</div>`;
    }
    
    return String(value);
}

// Close entry modal
function closeEntryModal() {
    document.getElementById('entry-modal').classList.add('hidden');
    document.getElementById('entry-modal').classList.remove('flex');
}

// Toggle view mode
function toggleViewMode(mode) {
    viewMode = mode;
    
    const cardBtn = document.getElementById('card-view-btn');
    const tableBtn = document.getElementById('table-view-btn');
    
    if (mode === 'card') {
        cardBtn.className = 'px-3 py-2 text-sm rounded-md transition duration-200 bg-white text-gray-700 shadow-sm';
        tableBtn.className = 'px-3 py-2 text-sm rounded-md transition duration-200 text-gray-500';
    } else {
        tableBtn.className = 'px-3 py-2 text-sm rounded-md transition duration-200 bg-white text-gray-700 shadow-sm';
        cardBtn.className = 'px-3 py-2 text-sm rounded-md transition duration-200 text-gray-500';
    }
    
    displayEntries(currentEntries);
}

// Apply filters
function applyFilters() {
    currentPage = 1;
    loadTemplateEntries(currentPage);
}

// Clear filters
function clearFilters() {
    document.getElementById('writer-filter').value = '';
    document.getElementById('start-date').value = '';
    document.getElementById('end-date').value = '';
    document.getElementById('verification-filter').value = '';
    document.getElementById('sort-option').value = 'created_at-desc';
    
    currentPage = 1;
    loadTemplateEntries(currentPage);
}

// Apply sorting
function applySorting() {
    currentPage = 1;
    loadTemplateEntries(currentPage);
}

// Refresh all data
function refreshData() {
    loadTemplateData();
    loadTemplateEntries(currentPage);
    showSuccess('Data berhasil diperbarui');
}

// Update pagination
function updatePagination(pagination) {
    const container = document.getElementById('pagination-container');
    const info = document.getElementById('pagination-info');
    const buttons = document.getElementById('pagination-buttons');
    
    if (!pagination || pagination.total === 0) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    
    // Update pagination info
    info.textContent = `Menampilkan ${pagination.from || 1} - ${pagination.to || 0} dari ${pagination.total} entri`;
    
    // Update pagination buttons
    buttons.innerHTML = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        buttons.innerHTML += `
            <button onclick="loadTemplateEntries(${pagination.current_page - 1})" 
                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <i class="fas fa-chevron-left mr-1"></i>
                Sebelumnya
            </button>
        `;
    }
    
    // Page numbers
    const totalPages = pagination.last_page || 1;
    const currentPage = pagination.current_page || 1;
    
    for (let page = Math.max(1, currentPage - 2); page <= Math.min(totalPages, currentPage + 2); page++) {
        const isActive = page === currentPage;
        buttons.innerHTML += `
            <button onclick="loadTemplateEntries(${page})" 
                class="px-3 py-2 text-sm rounded-md ${isActive 
                    ? 'bg-indigo-600 text-white' 
                    : 'bg-white border border-gray-300 hover:bg-gray-50'
                }">
                ${page}
            </button>
        `;
    }
    
    // Next button
    if (pagination.current_page < totalPages) {
        buttons.innerHTML += `
            <button onclick="loadTemplateEntries(${pagination.current_page + 1})" 
                class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Selanjutnya
                <i class="fas fa-chevron-right ml-1"></i>
            </button>
        `;
    }
}

// Show data loading state
function showDataLoading() {
    const loadingGrid = document.getElementById('loading-grid');
    loadingGrid.innerHTML = Array(6).fill().map(() => `
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="animate-pulse">
                <div class="flex justify-between mb-4">
                    <div>
                        <div class="h-4 bg-gray-200 rounded w-24 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                    </div>
                    <div class="h-6 bg-gray-200 rounded w-20"></div>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="h-3 bg-gray-200 rounded w-full"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
                <div class="h-8 bg-gray-200 rounded w-full"></div>
            </div>
        </div>
    `).join('');
    
    loadingGrid.className = viewMode === 'card' ? 'grid gap-6 md:grid-cols-2 lg:grid-cols-3' : 'space-y-4';
    
    document.getElementById('data-loading').classList.remove('hidden');
    document.getElementById('card-view').classList.add('hidden');
    document.getElementById('table-view').classList.add('hidden');
    document.getElementById('no-data').classList.add('hidden');
}

// Hide data loading state
function hideDataLoading() {
    document.getElementById('data-loading').classList.add('hidden');
}

// Utility functions
async function fetchWithAuth(url, options = {}) {
    const token = localStorage.getItem('admin_token');
    
    return fetch(url, {
        ...options,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        }
    });
}

function showError(message) {
    // Simple error display - could be improved with a proper toast system
    alert('Error: ' + message);
}

function showSuccess(message) {
    // Simple success display - could be improved with a proper toast system
    console.log('Success: ' + message);
}

// Close modal when clicking outside
document.getElementById('entry-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEntryModal();
    }
});
</script>
@endpush