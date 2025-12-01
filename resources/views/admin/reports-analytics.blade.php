@extends('admin.layout')

@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-description', 'Generate laporan dan analisis data sistem')

@section('content')
    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto" id="reportTabs" aria-label="Tabs">
                <button onclick="ReportsManager.switchTab('logbook')" id="tab-btn-logbook"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-indigo-500 text-indigo-600 whitespace-nowrap">
                    <i class="fas fa-book mr-2"></i>
                    Logbook Reports
                </button>
                <button onclick="ReportsManager.switchTab('user-activity')" id="tab-btn-user-activity"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-user-clock mr-2"></i>
                    User Activity
                </button>
                <button onclick="ReportsManager.switchTab('institution')" id="tab-btn-institution"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-building mr-2"></i>
                    Institution Performance
                </button>
                <button onclick="ReportsManager.switchTab('export')" id="tab-btn-export"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-download mr-2"></i>
                    Export Center
                </button>
                <button onclick="ReportsManager.switchTab('scheduled')" id="tab-btn-scheduled"
                    class="tab-button flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap">
                    <i class="fas fa-clock mr-2"></i>
                    Scheduled Reports
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Demo</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Contents -->
    <div id="tab-contents">
        @include('admin.reports_analytics.logbook_reports')
        @include('admin.reports_analytics.user_activity')
        @include('admin.reports_analytics.institution_performance')
        @include('admin.reports_analytics.export_center')
        @include('admin.reports_analytics.scheduled_reports')
    </div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center space-x-3">
            <i class="fas fa-spinner fa-spin text-indigo-600 text-2xl"></i>
            <span class="text-gray-900 font-medium">Loading...</span>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 bg-white rounded-lg shadow-lg p-4 z-50 max-w-sm">
    <div class="flex items-center space-x-3">
        <i id="toastIcon" class="text-2xl"></i>
        <div>
            <p id="toastMessage" class="font-medium text-gray-900"></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/reports-analytics.js') }}"></script>
@endpush

@push('styles')
<style>
    /* Custom scrollbar for tabs on mobile */
    #reportTabs::-webkit-scrollbar {
        height: 4px;
    }
    
    #reportTabs::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }
</style>
@endpush
