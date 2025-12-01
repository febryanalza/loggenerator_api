<!-- Tab Content: Scheduled Reports -->
<div id="tab-scheduled" class="tab-content hidden">
    <!-- Info Banner -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Demo Mode:</strong> Fitur Scheduled Reports dalam mode demo. Data yang ditampilkan adalah dummy dan belum terhubung dengan sistem email sebenarnya.
                </p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-clock mr-2 text-indigo-600"></i>
            Scheduled Reports
        </h3>
        <button onclick="ScheduledReports.openCreateModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-2"></i>
            Create Schedule
        </button>
    </div>

    <!-- Scheduled Reports List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6" id="scheduledReportsList">
        <!-- Card 1 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                        <i class="fas fa-circle text-green-500 mr-1" style="font-size: 6px;"></i>
                        Active
                    </span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">Weekly</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Weekly Logbook Summary</h4>
                <p class="text-sm text-gray-600 mb-4">Laporan ringkasan logbook mingguan</p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-file-alt w-5 text-gray-400"></i>
                        <span>Logbook Entries</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-envelope w-5 text-gray-400"></i>
                        <span>admin@example.com</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-clock w-5 text-gray-400"></i>
                        <span>Next: Dec 8, 2025</span>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-between">
                <button onclick="ScheduledReports.toggle('1')" class="text-yellow-600 hover:text-yellow-800 text-sm">
                    <i class="fas fa-pause mr-1"></i>
                    Pause
                </button>
                <button onclick="ScheduledReports.delete('1')" class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash mr-1"></i>
                    Delete
                </button>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                        <i class="fas fa-circle text-green-500 mr-1" style="font-size: 6px;"></i>
                        Active
                    </span>
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Monthly</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Monthly User Report</h4>
                <p class="text-sm text-gray-600 mb-4">Laporan user bulanan dengan statistik</p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-users w-5 text-gray-400"></i>
                        <span>Users</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-envelope w-5 text-gray-400"></i>
                        <span>hr@example.com, admin@example.com</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-clock w-5 text-gray-400"></i>
                        <span>Next: Jan 1, 2026</span>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-between">
                <button onclick="ScheduledReports.toggle('2')" class="text-yellow-600 hover:text-yellow-800 text-sm">
                    <i class="fas fa-pause mr-1"></i>
                    Pause
                </button>
                <button onclick="ScheduledReports.delete('2')" class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash mr-1"></i>
                    Delete
                </button>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden opacity-75">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                        <i class="fas fa-circle text-gray-400 mr-1" style="font-size: 6px;"></i>
                        Paused
                    </span>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">Daily</span>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Daily Activity Digest</h4>
                <p class="text-sm text-gray-600 mb-4">Ringkasan aktivitas harian untuk monitoring</p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-history w-5 text-gray-400"></i>
                        <span>Audit Logs</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-envelope w-5 text-gray-400"></i>
                        <span>security@example.com</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-clock w-5 text-gray-400"></i>
                        <span>Paused</span>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-between">
                <button onclick="ScheduledReports.toggle('3')" class="text-green-600 hover:text-green-800 text-sm">
                    <i class="fas fa-play mr-1"></i>
                    Resume
                </button>
                <button onclick="ScheduledReports.delete('3')" class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash mr-1"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Schedule History -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history mr-2 text-indigo-600"></i>
                Riwayat Pengiriman
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Report Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipients</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">Dec 1, 2025 08:00</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Weekly Logbook Summary</td>
                        <td class="px-6 py-4 text-sm text-gray-600">admin@example.com</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i>Sent
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">Nov 24, 2025 08:00</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Weekly Logbook Summary</td>
                        <td class="px-6 py-4 text-sm text-gray-600">admin@example.com</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i>Sent
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">Nov 1, 2025 00:00</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Monthly User Report</td>
                        <td class="px-6 py-4 text-sm text-gray-600">hr@example.com, admin@example.com</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i>Sent
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">Oct 28, 2025 18:00</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Daily Activity Digest</td>
                        <td class="px-6 py-4 text-sm text-gray-600">security@example.com</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                <i class="fas fa-times-circle mr-1"></i>Failed
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Schedule Modal -->
<div id="createScheduleModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Create Scheduled Report</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Name</label>
                <input type="text" id="scheduleName" placeholder="e.g., Weekly Summary" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select id="scheduleType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="logbook_entries">Logbook Entries</option>
                    <option value="users">Users</option>
                    <option value="audit_logs">Audit Logs</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                <select id="scheduleFrequency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients (comma separated)</label>
                <input type="text" id="scheduleRecipients" placeholder="email1@example.com, email2@example.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <button onclick="ScheduledReports.closeCreateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button onclick="ScheduledReports.create()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Create Schedule
            </button>
        </div>
    </div>
</div>
