@extends('admin.layout')

@section('title', 'Manajemen Logbook')
@section('page-title', 'Manajemen Logbook')
@section('page-description', 'Kelola template logbook, tipe data, dan konfigurasi lainnya')

@section('content')
    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button onclick="LogbookManagement.switchTab('templates')" id="tab-btn-templates"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-blue-500 text-blue-600">
                    <i class="fas fa-book mr-2"></i>
                    Daftar Logbook
                </button>
                <button onclick="LogbookManagement.switchTab('datatypes')" id="tab-btn-datatypes"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-database mr-2"></i>
                    Tipe Data
                </button>
                <button onclick="LogbookManagement.switchTab('availabletemplates')" id="tab-btn-availabletemplates"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-layer-group mr-2"></i>
                    Available Template
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Contents -->
    <div id="tab-contents">
        @include('admin.logbook_management.templates')
        @include('admin.logbook_management.datatypes')
        @include('admin.logbook_management.availabletemplates')
    </div>
@endsection

@push('scripts')
<script>
// Global Logbook Management Module
const LogbookManagement = {
    currentTab: 'templates',
    initialized: {
        templates: false,
        datatypes: false,
        availabletemplates: false
    },

    init() {
        // Check authentication
        const token = localStorage.getItem('admin_token');
        const user = localStorage.getItem('admin_user');
        
        if (!token || !user) {
            console.error('No authentication found');
            window.location.href = '/login';
            return;
        }

        // Initialize current tab
        this.switchTab('templates');
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
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });
        
        const selectedBtn = document.getElementById(`tab-btn-${tabName}`);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            selectedBtn.classList.add('border-blue-500', 'text-blue-600');
        }
        
        // Initialize the tab's manager if not already done
        if (tabName === 'templates' && typeof TemplatesManager !== 'undefined' && !this.initialized.templates) {
            TemplatesManager.init();
            this.initialized.templates = true;
        } else if (tabName === 'datatypes' && typeof DataTypesManager !== 'undefined' && !this.initialized.datatypes) {
            DataTypesManager.init();
            this.initialized.datatypes = true;
        } else if (tabName === 'availabletemplates' && typeof AvailableTemplatesManager !== 'undefined' && !this.initialized.availabletemplates) {
            AvailableTemplatesManager.init();
            this.initialized.availabletemplates = true;
        }
    },

    // Helper functions shared across tabs
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    },

    getInitials(name) {
        if (!name) return '?';
        return name.split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    },

    formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    },

    formatTime(dateString) {
        const options = { hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleTimeString('id-ID', options);
    },

    showSuccess(message) {
        this.showToast(message, 'success');
    },

    showError(message) {
        this.showToast(message, 'error');
    },

    showToast(message, type = 'info') {
        // Create toast container if not exists
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }

        // Create toast element
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        
        toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-x-full`;
        toast.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${this.escapeHtml(message)}</span>
            <button onclick="this.parentElement.remove()" class="ml-2 hover:opacity-75">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    LogbookManagement.init();
});
</script>
@endpush