@extends('institution_admin.layout')

@section('title', 'Manajemen Logbook')
@section('page-title', 'Manajemen Logbook')
@section('page-description', 'Kelola logbook dan template untuk institusi Anda')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Manajemen Logbook</span>
    </div>
</li>
@endsection

@section('content')
<!-- Tab Navigation -->
<div class="bg-white rounded-xl shadow-sm border mb-6">
    <div class="flex border-b">
        <button onclick="LogbookManagement.switchTab('logbooks')" 
            class="tab-button flex-1 px-6 py-4 text-center font-medium transition-all duration-200 border-b-2 border-transparent hover:bg-gray-50"
            data-tab="logbooks"
            id="tab-btn-logbooks">
            <i class="fas fa-book mr-2"></i>
            Daftar Logbook
        </button>
        <button onclick="LogbookManagement.switchTab('templates')" 
            class="tab-button flex-1 px-6 py-4 text-center font-medium transition-all duration-200 border-b-2 border-transparent hover:bg-gray-50"
            data-tab="templates"
            id="tab-btn-templates">
            <i class="fas fa-layer-group mr-2"></i>
            Template Available
        </button>
        <button onclick="LogbookManagement.switchTab('required-data')" 
            class="tab-button flex-1 px-6 py-4 text-center font-medium transition-all duration-200 border-b-2 border-transparent hover:bg-gray-50"
            data-tab="required-data"
            id="tab-btn-required-data">
            <i class="fas fa-list mr-2"></i>
            Required Data Participant
        </button>
    </div>
</div>

<!-- Tab Contents -->
<div id="tab-contents">
    <!-- Tab: Logbooks -->
    <div id="tab-logbooks" class="tab-content hidden">
        @include('institution_admin.logbook_management.logbooks')
    </div>
    
    <!-- Tab: Templates -->
    <div id="tab-templates" class="tab-content hidden">
        @include('institution_admin.logbook_management.templates')
    </div>
    
    <!-- Tab: Required Data -->
    <div id="tab-required-data" class="tab-content hidden">
        @include('institution_admin.logbook_management.required_data')
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global Logbook Management Module
const LogbookManagement = {
    currentTab: 'logbooks',
    initialized: {
        logbooks: false,
        templates: false,
        'required-data': false
    },
    userData: null,
    institutionId: null,

    init() {
        // Check authentication
        const token = localStorage.getItem('admin_token');
        const user = localStorage.getItem('admin_user');
        
        if (!token || !user) {
            console.error('No authentication found');
            window.location.href = '/login';
            return;
        }

        try {
            this.userData = JSON.parse(user);
            this.institutionId = this.userData.institution_id || this.userData.institution?.id;
            
            if (!this.institutionId) {
                console.error('No institution ID found');
                showAlert('error', 'Error', 'Tidak dapat menemukan ID institusi. Silakan login ulang.');
                return;
            }
            
            console.log('Institution ID:', this.institutionId);
        } catch (e) {
            console.error('Failed to parse user data:', e);
            window.location.href = '/login';
            return;
        }

        // Initialize first tab
        this.switchTab('logbooks');
    },

    switchTab(tabName) {
        this.currentTab = tabName;
        
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Show selected tab content
        const selectedContent = document.getElementById(`tab-${tabName}`);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }
        
        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-green-500', 'text-green-600', 'bg-green-50');
            btn.classList.add('border-transparent', 'text-gray-600');
        });
        
        const selectedBtn = document.getElementById(`tab-btn-${tabName}`);
        if (selectedBtn) {
            selectedBtn.classList.add('border-green-500', 'text-green-600', 'bg-green-50');
            selectedBtn.classList.remove('border-transparent', 'text-gray-600');
        }
        
        // Initialize tab content if not already initialized
        if (!this.initialized[tabName]) {
            this.initializeTab(tabName);
        }
    },

    initializeTab(tabName) {
        switch(tabName) {
            case 'logbooks':
                if (typeof LogbooksManager !== 'undefined') {
                    LogbooksManager.init(this.institutionId);
                    this.initialized.logbooks = true;
                }
                break;
            case 'templates':
                if (typeof TemplatesManager !== 'undefined') {
                    TemplatesManager.init(this.institutionId);
                    this.initialized.templates = true;
                }
                break;
            case 'required-data':
                if (typeof RequiredDataManager !== 'undefined') {
                    RequiredDataManager.init(this.institutionId);
                    this.initialized['required-data'] = true;
                }
                break;
        }
    },

    getToken() {
        return localStorage.getItem('admin_token');
    },

    getInstitutionId() {
        return this.institutionId;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    LogbookManagement.init();
});
</script>
@endpush
